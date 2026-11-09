<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Order;
use App\Models\AuditLog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class OrderController extends Controller {
 private function scoped(Request $request) {
  return app(\App\Services\ActiveBranchContext::class)->scope(Order::query(),$request);
 }
 public function index(Request $request) {
  $query=$this->scoped($request)->with(['customer','branch','payment']);
  if($request->filled('status')) $query->where('status',$request->validate(['status'=>Rule::in(['received','processing','ready','picked_up','cancelled'])])['status']);
  if($request->filled('search')) {
   $search=trim($request->input('search'));
   $query->where(function($q)use($search){$q->where('order_number','like','%'.$search.'%')->orWhereHas('customer',fn($c)=>$c->where('name','like','%'.$search.'%'));});
  }
  return view('orders.index',['orders'=>$query->latest()->paginate(15)->withQueryString()]);
 }
 public function create(Request $request) {
  $user=$request->user();
  return view('orders.create',[
   'customers'=>app(\App\Services\ActiveBranchContext::class)->id($request)===null?collect():app(\App\Services\ActiveBranchContext::class)->scope(Customer::query(),$request)->orderBy('name')->get(),
   'services'=>Service::where('business_id',$user->business_id)->where('is_active',true)->orderBy('name')->get()
  ]);
 }
 public function store(Request $request) {
  $user=$request->user();
  $context=app(\App\Services\ActiveBranchContext::class);
  $branchId=$context->requireBranch($request)->id;
  $rules=[
   'customer_id'=>['required','integer'],
   'items'=>['required','array','min:1','max:20'],
   'items.*.service_id'=>['required','integer','distinct'],
   'items.*.quantity'=>['required','numeric','gt:0','max:999999','decimal:0,2'],
   'notes'=>['nullable','string','max:2000'],
  ];
  $data=$request->validate($rules);
  abort_unless($user->isOwner() || $user->branch?->is_active,403);
  $customer=Customer::where('business_id',$user->business_id)->where('branch_id',$branchId)->find($data['customer_id']);
  if(!$customer) throw ValidationException::withMessages(['customer_id'=>'Pelanggan harus berasal dari cabang yang dipilih.']);
  $ids=collect($data['items'])->pluck('service_id');
  $services=Service::where('business_id',$user->business_id)->where('is_active',true)->whereIn('id',$ids)->get()->keyBy('id');
  if($services->count()!==$ids->count()) throw ValidationException::withMessages(['items'=>'Ada layanan yang tidak valid atau tidak aktif.']);
  $order=DB::transaction(function()use($user,$branchId,$customer,$data,$services,$request,$context){
   $branch=$context->requireBranch($request,true);
   $items=[];$subtotal=0;$maxHours=0;
   foreach($data['items'] as $entry){
    $service=$services->get((int)$entry['service_id']);
    $quantity=(float)$entry['quantity'];
    if($service->unit==='item' && floor($quantity)!==$quantity) throw ValidationException::withMessages(['items'=>'Layanan per barang harus menggunakan jumlah bulat.']);
    // Harga disimpan sebagai snapshot, bukan dihitung ulang dari tarif terbaru.
    $line=round($quantity*(float)$service->price,2);
    $subtotal=round($subtotal+$line,2);
    $maxHours=max($maxHours,$service->estimated_hours);
    $items[]=['service_id'=>$service->id,'service_name'=>$service->name,'unit'=>$service->unit,'quantity'=>$quantity,'unit_price'=>$service->price,'subtotal'=>$line];
   }
   $order=Order::create([
    'business_id'=>$user->business_id,'branch_id'=>$branchId,'customer_id'=>$customer->id,'created_by'=>$user->id,
    'order_number'=>'TMP-'.strtoupper(bin2hex(random_bytes(12))),
    'subtotal'=>$subtotal,'total'=>$subtotal,'estimated_at'=>now()->addHours($maxHours),'notes'=>$data['notes']??null,
   ]);
   $order->update(['order_number'=>sprintf('%s-%s-%s-%05d',$user->business->code,$branch->code,now()->format('Ymd'),$order->id)]);
   $order->items()->createMany($items);
   AuditLog::create(['business_id'=>$user->business_id,'user_id'=>$user->id,'action'=>'order.created','subject_type'=>'order','subject_id'=>$order->id,'details'=>['number'=>$order->order_number,'branch_id'=>$branchId,'total'=>$subtotal]]);
   return $order;
  });
  return redirect()->route('orders.show',$order->id)->with('success','Pesanan berhasil dibuat.');
 }
 public function show(Request $request,int $order) {
  $order=$this->scoped($request)->with(['customer','branch','items','creator','payment.attempts.recorder','payment.attempts.confirmer'])->findOrFail($order);
  $cashShifts=$request->user()->isOwner()
   ? \App\Models\CashShift::where('business_id',$order->business_id)->where('branch_id',$order->branch_id)->where('status','open')->with('cashier')->get()
   : collect();
  return view('orders.show',compact('order','cashShifts'));
 }
 public function updateStatus(Request $request,int $order) {
  $data=$request->validate(['status'=>['required',Rule::in(['processing','ready','picked_up'])]]);
  DB::transaction(function()use($request,$order,$data){
   $order=$this->scoped($request)->lockForUpdate()->findOrFail($order);
   $next=['received'=>'processing','processing'=>'ready','ready'=>'picked_up'];
   if(($next[$order->status]??null)!==$data['status']) throw ValidationException::withMessages(['status'=>'Perubahan status harus mengikuti urutan pengerjaan.']);
   if($data['status']==='picked_up' && $order->payment?->status!=='paid') throw ValidationException::withMessages(['status'=>'Pesanan harus lunas sebelum diserahkan kepada pelanggan.']);
   $previous=$order->status;
   $order->update(['status'=>$data['status'],'picked_up_at'=>$data['status']==='picked_up'?now():null]);
   AuditLog::create(['business_id'=>$order->business_id,'user_id'=>$request->user()->id,'action'=>'order.status_changed','subject_type'=>'order','subject_id'=>$order->id,'details'=>['from'=>$previous,'to'=>$data['status']]]);
  });
  return redirect()->route('orders.show',$order)->with('success','Status pesanan diperbarui.');
 }
}
