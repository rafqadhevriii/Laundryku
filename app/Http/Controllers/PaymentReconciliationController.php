<?php
namespace App\Http\Controllers;

use App\Models\{AuditLog,CashShift,Payment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentReconciliationController extends Controller {
 private function scoped(Request $request){
  $user=$request->user()->fresh();
  abort_unless($user?->isOwner() && $user->is_active && $user->business?->is_active,403);
  return Payment::where('method','cash')->where('status','paid')
   ->whereHas('order',fn($q)=>$q->where('business_id',$user->business_id));
 }
 public function index(Request $request){
  $query=$this->scoped($request);
  $filters=$request->validate([
   'branch_id'=>['nullable',Rule::exists('branches','id')->where('business_id',$request->user()->business_id)],
   'start'=>['nullable','date'],'end'=>['nullable','date','after_or_equal:start'],
   'order_number'=>['nullable','string','max:150'],
   'review'=>['nullable',Rule::in(['all','unreviewed','unallocated'])],
  ]);
  if(!empty($filters['branch_id']))$query->whereHas('order',fn($q)=>$q->where('branch_id',$filters['branch_id']));
  if(!empty($filters['order_number']))$query->whereHas('order',fn($q)=>$q->where('order_number','like','%'.$filters['order_number'].'%'));
  if(!empty($filters['start']))$query->whereDate('paid_at','>=',$filters['start']);
  if(!empty($filters['end']))$query->whereDate('paid_at','<=',$filters['end']);
  $history=AuditLog::where('business_id',$request->user()->business_id)->where('subject_type','payment')
   ->where('action','payment.reconciled')->whereIn('subject_id',(clone $query)->select('payments.id'))
   ->with('user')->latest('id')->paginate(15,['*'],'history_page')->withQueryString();
  $unassigned=(clone $query)->whereNull('cash_shift_id');
  $summary=[
   'unreviewed'=>(clone $unassigned)->whereNull('reconciliation_status')->sum('amount'),
   'unallocated'=>(clone $unassigned)->where('reconciliation_status','unallocated')->sum('amount'),
  ];
  if(($filters['review']??'all')==='unreviewed')$unassigned->whereNull('reconciliation_status');
  if(($filters['review']??'all')==='unallocated')$unassigned->where('reconciliation_status','unallocated');
  $payments=$unassigned->with(['order.branch','recorder','reconciler'])->latest('paid_at')->paginate(15)->withQueryString();
  $shifts=CashShift::where('business_id',$request->user()->business_id)->where('status','open')->with('cashier')->get();
  $branches=$request->user()->business->branches()->orderBy('name')->get();
  return view('payments.reconciliation',compact('payments','shifts','branches','history','summary'));
 }
 public function update(Request $request,int $payment){
  $this->scoped($request); // otorisasi sebelum validasi
  $data=$request->validate([
   'decision'=>['required',Rule::in(['assign','unallocated'])],
   'cash_shift_id'=>['required_if:decision,assign','nullable','integer'],
   'reason'=>['required','string','max:2000'],
  ]);
  DB::transaction(function()use($request,$payment,$data){
   // Urutan lock shift -> payment juga aman terhadap penutupan shift.
   $shift=null;
   if($data['decision']==='assign'){
    $shift=CashShift::where('business_id',$request->user()->business_id)->whereKey($data['cash_shift_id'])->lockForUpdate()->firstOrFail();
   }
   $p=$this->scoped($request)->with('order')->lockForUpdate()->findOrFail($payment);
   if($p->cash_shift_id!==null || $p->reconciliation_status==='assigned')throw ValidationException::withMessages(['decision'=>'Pembayaran sudah dihubungkan ke shift. Keputusan tidak dapat diulang.']);
   if($data['decision']==='unallocated' && $p->reconciliation_status==='unallocated')throw ValidationException::withMessages(['decision'=>'Pembayaran sudah ditandai belum teralokasi.']);
   if($shift){
    if($shift->branch_id!==$p->order->branch_id)throw ValidationException::withMessages(['cash_shift_id'=>'Shift harus berasal dari cabang pesanan.']);
    if($shift->status!=='open')throw ValidationException::withMessages(['cash_shift_id'=>'Shift sudah ditutup/ditinjau. Saldo final tidak boleh diubah; gunakan keputusan historis belum teralokasi jika atribusi tidak dapat diselesaikan.']);
    if(!$p->paid_at || $p->paid_at->lt($shift->opened_at) || $p->paid_at->gt(now()))throw ValidationException::withMessages(['cash_shift_id'=>'Waktu pembayaran harus berada dalam periode shift terbuka.']);
   }
   $before=$p->only(['reconciliation_status','cash_shift_id','status','amount','method']);
   $p->update(['cash_shift_id'=>$shift?->id,'reconciliation_status'=>$shift?'assigned':'unallocated',
    'reconciled_by'=>$request->user()->id,'reconciled_at'=>now(),'reconciliation_reason'=>$data['reason']]);
   AuditLog::create(['business_id'=>$p->order->business_id,'user_id'=>$request->user()->id,'action'=>'payment.reconciled',
    'subject_type'=>'payment','subject_id'=>$p->id,'details'=>[
     'order_number'=>$p->order->order_number,'branch_id'=>$p->order->branch_id,'reason'=>$data['reason'],
     'before'=>$before,'after'=>$p->only(['reconciliation_status','cash_shift_id','status','amount','method']),
     'target_shift_id'=>$shift?->id,'decided_at'=>$p->reconciled_at->toIso8601String(),
    ]]);
  },3);
  return back()->with('success','Keputusan rekonsiliasi tersimpan. Pendapatan tidak berubah.');
 }
}
