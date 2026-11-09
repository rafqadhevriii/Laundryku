<?php
namespace App\Http\Controllers;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class InventoryController extends Controller {
 private function scoped(Request $request){
  $u=$request->user();
  return InventoryItem::where('business_id',$u->business_id)->when($u->isCashier(),fn($q)=>$q->where('branch_id',$u->branch_id));
 }
 public function index(Request $request){
  $items=$this->scoped($request)->with('branch')->orderBy('name')->paginate(20);
  $pending=InventoryMovement::where('status','pending')->whereHas('item',function($q)use($request){
   $q->where('business_id',$request->user()->business_id);
   if($request->user()->isCashier())$q->where('branch_id',$request->user()->branch_id);
  })->with(['item','creator'])->latest()->get();
  $branches=$request->user()->isOwner()?$request->user()->business->branches()->where('is_active',true)->get():collect();
  return view('inventory.index',compact('items','pending','branches'));
 }
 public function store(Request $request){
  abort_unless($request->user()->isOwner(),403);
  $u=$request->user();
  $data=$request->validate([
   'branch_id'=>['required',Rule::exists('branches','id')->where('business_id',$u->business_id)->where('is_active',true)],
   'name'=>['required','string','max:150'],
   'category'=>['required',Rule::in(['Deterjen','Pewangi dan pelembut','Pemutih dan bahan khusus','Kemasan dan hanger','Operasional lain'])],
   'unit'=>['required','string','max:30'],
   'minimum_quantity'=>['required','numeric','min:0','max:9999999999.99','decimal:0,2']
  ]);
  DB::transaction(function()use($data,$u){
   $item=InventoryItem::create([...$data,'business_id'=>$u->business_id,'quantity'=>0]);
   AuditLog::create(['business_id'=>$u->business_id,'user_id'=>$u->id,'action'=>'inventory.item_created','subject_type'=>'inventory_item','subject_id'=>$item->id,'details'=>['branch_id'=>$item->branch_id,'name'=>$item->name]]);
  });
  return back()->with('success','Barang stok berhasil ditambahkan.');
 }
 public function move(Request $request,int $item){
  $data=$request->validate([
   'type'=>['required',Rule::in(['in','out','adjustment'])],
   'quantity'=>['required','numeric','min:0','max:9999999999.99','decimal:0,2'],
   'notes'=>['nullable','string','max:255'],
  ]);
  DB::transaction(function()use($request,$item,$data){
   $stock=$this->scoped($request)->lockForUpdate()->findOrFail($item);
   $u=$request->user();$before=(float)$stock->quantity;$amount=(float)$data['quantity'];
   if($data['type']!=='adjustment' && $amount<=0)throw ValidationException::withMessages(['quantity'=>'Jumlah masuk atau keluar harus lebih dari nol.']);
   $status=$data['type']==='adjustment'&&$u->isCashier()?'pending':'approved';
   $after=match($data['type']){'in'=>$before+$amount,'out'=>$before-$amount,'adjustment'=>$amount};
   if($after<0)throw ValidationException::withMessages(['quantity'=>'Stok tidak mencukupi.']);
   $movement=InventoryMovement::create([
    'inventory_item_id'=>$stock->id,'created_by'=>$u->id,'type'=>$data['type'],'status'=>$status,
    'quantity'=>$amount,'before_quantity'=>$before,'after_quantity'=>$status==='approved'?$after:null,'notes'=>$data['notes']??null
   ]);
   if($status==='approved')$stock->update(['quantity'=>$after]);
   AuditLog::create(['business_id'=>$stock->business_id,'user_id'=>$u->id,'action'=>$status==='pending'?'inventory.adjustment_requested':'inventory.movement_recorded','subject_type'=>'inventory_movement','subject_id'=>$movement->id,'details'=>['item_id'=>$stock->id,'branch_id'=>$stock->branch_id,'type'=>$data['type'],'status'=>$status,'before'=>$before,'quantity'=>$amount,'after'=>$status==='approved'?$after:null]]);
  });
  return back()->with('success',$data['type']==='adjustment'&&$request->user()->isCashier()?'Koreksi stok diajukan kepada Pemilik.':'Perubahan stok berhasil dicatat.');
 }
 public function review(Request $request,int $movement){
  abort_unless($request->user()->isOwner(),403);
  $data=$request->validate(['decision'=>['required',Rule::in(['approve','reject'])]]);
  DB::transaction(function()use($request,$movement,$data){
   $m=InventoryMovement::where('status','pending')->whereHas('item',fn($q)=>$q->where('business_id',$request->user()->business_id))->findOrFail($movement);
   $stock=InventoryItem::whereKey($m->inventory_item_id)->lockForUpdate()->firstOrFail();
   $m=InventoryMovement::whereKey($m->id)->lockForUpdate()->firstOrFail();
   abort_unless($m->status==='pending',409);
   if($data['decision']==='approve'){
    $before=(float)$stock->quantity;$after=(float)$m->quantity;
    $stock->update(['quantity'=>$after]);
    $m->update(['status'=>'approved','reviewed_by'=>$request->user()->id,'before_quantity'=>$before,'after_quantity'=>$after]);
   }else{$m->update(['status'=>'rejected','reviewed_by'=>$request->user()->id]);}
   AuditLog::create(['business_id'=>$stock->business_id,'user_id'=>$request->user()->id,'action'=>$data['decision']==='approve'?'inventory.adjustment_approved':'inventory.adjustment_rejected','subject_type'=>'inventory_movement','subject_id'=>$m->id,'details'=>['item_id'=>$stock->id,'branch_id'=>$stock->branch_id,'requested_quantity'=>$m->quantity,'after'=>$data['decision']==='approve'?$stock->quantity:null]]);
  });
  return back()->with('success','Pengajuan koreksi stok telah ditinjau.');
 }
}
