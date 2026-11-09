<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\CashShift;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class PaymentController extends Controller {
 private function scoped(Request $request) {
  $user=$request->user()->fresh();
  abort_unless($user?->is_active && $user->business?->is_active && ($user->isOwner() || ($user->isCashier() && $user->branch?->is_active && $user->branch->business_id===$user->business_id)),403);
  return app(\App\Services\ActiveBranchContext::class)->scope(Order::query(),$request);
 }
 public function store(Request $request,int $order) {
  return $this->record($request,$order,false);
 }
 public function retry(Request $request,int $order) {
  return $this->record($request,$order,true);
 }
 private function currentAttempt(Payment $payment):PaymentAttempt {
  return $payment->attempts()->reorder()->latest('attempt_number')->first() ?? $this->snapshotAttempt($payment,1);
 }
 private function snapshotAttempt(Payment $payment,int $number):PaymentAttempt {
  return $payment->attempts()->create([
   'attempt_number'=>$number,'recorded_by'=>$payment->recorded_by,'confirmed_by'=>$payment->confirmed_by,
   'method'=>$payment->method,'status'=>$payment->status,'amount'=>$payment->amount,
   'reference'=>$payment->reference,'notes'=>$payment->notes,
   'rejection_reason'=>$payment->status==='rejected'?$payment->notes:null,
   'submitted_at'=>$payment->created_at,'decided_at'=>$payment->status==='pending'?null:$payment->updated_at,'paid_at'=>$payment->paid_at,
  ]);
 }
 private function record(Request $request,int $order,bool $retryOnly) {
  $this->scoped($request);
  $data=$request->validate([
   'cash_shift_id'=>['nullable','integer'],
   'method'=>['required',Rule::in(['cash','transfer','qris'])],
   'reference'=>['nullable','string','max:120'],
   'notes'=>['nullable','string','max:1000'],
  ]);
  DB::transaction(function()use($request,$order,$data,$retryOnly){
   $order=$this->scoped($request)->lockForUpdate()->findOrFail($order);
   if($order->status==='cancelled') throw ValidationException::withMessages(['method'=>'Pesanan dibatalkan tidak dapat dibayar.']);
   $existing=Payment::where('order_id',$order->id)->lockForUpdate()->first();
   if($retryOnly && !$existing)throw ValidationException::withMessages(['method'=>'Belum ada pembayaran ditolak untuk diajukan ulang.']);
   $previous=null;$previousAttempt=null;
   if($existing){
    if($existing->status!=='rejected')throw ValidationException::withMessages(['method'=>'Pembayaran sudah dicatat. Muat ulang halaman.']);
    if(!in_array($existing->method,['transfer','qris']) || !in_array($data['method'],['transfer','qris']))throw ValidationException::withMessages(['method'=>'Pengajuan ulang pembayaran ditolak hanya melalui transfer atau QRIS.']);
    $previous=$existing->only(['method','status','amount','reference','notes','confirmed_by']);
    $previousAttempt=$this->currentAttempt($existing);
   }
   $cash=$data['method']==='cash';
   $shift=null;
   if($cash){
    // Lock yang sama dengan penutupan shift: pembayaran selesai sebelum tutup,
    // atau ditolak jika penutupan sudah lebih dahulu selesai.
    $shifts=CashShift::where('business_id',$order->business_id)
     ->where('branch_id',$order->branch_id)->where('status','open');
    if($request->user()->isCashier()){
     $shifts->where('cashier_id',$request->user()->id);
    }else{
     if(empty($data['cash_shift_id'])) throw ValidationException::withMessages(['method'=>'Pilih shift kasir aktif untuk pembayaran tunai.']);
     $shifts->whereKey($data['cash_shift_id']);
    }
    $shift=$shifts->lockForUpdate()->first();
    if(!$shift) throw ValidationException::withMessages(['method'=>'Pembayaran tunai memerlukan shift aktif pada cabang pesanan. Buka shift terlebih dahulu.']);
   }
   $payload=[
    'cash_shift_id'=>$shift?->id,
    'order_id'=>$order->id,'recorded_by'=>$request->user()->id,
    'confirmed_by'=>$cash?$request->user()->id:null,
    'method'=>$data['method'],'status'=>$cash?'paid':'pending',
    'amount'=>$order->total,'reference'=>$data['reference']??null,
    'notes'=>$data['notes']??null,'paid_at'=>$cash?now():null,
   ];
   if($existing){$existing->update($payload);$payment=$existing;}
   else{$payment=Payment::create($payload);}
   $attempt=$this->snapshotAttempt($payment,($previousAttempt?->attempt_number??0)+1);
   if($previousAttempt)$attempt->update(['submitted_at'=>now()]);
   AuditLog::create(['business_id'=>$order->business_id,'user_id'=>$request->user()->id,'action'=>$existing?'payment.retried':'payment.recorded','subject_type'=>'payment','subject_id'=>$payment->id,'details'=>['order_id'=>$order->id,'method'=>$data['method'],'amount'=>$order->total,'status'=>$payload['status'],'cash_shift_id'=>$shift?->id,'attempt_id'=>$attempt->id,'before'=>$previous,'after'=>$payment->only(['method','status','amount','reference','notes','confirmed_by'])]]);
  },3);
  return redirect()->route('orders.show',$order)->with('success','Pembayaran berhasil dicatat.');
 }
 public function confirm(Request $request,int $order) {
  DB::transaction(function()use($request,$order){
   $order=$this->scoped($request)->lockForUpdate()->findOrFail($order);
   $payment=Payment::where('order_id',$order->id)->lockForUpdate()->firstOrFail();
   if($payment->status!=='pending' || !in_array($payment->method,['transfer','qris'])) throw ValidationException::withMessages(['payment'=>'Pembayaran ini tidak menunggu konfirmasi.']);
   $attempt=$this->currentAttempt($payment);
   $payment->update(['status'=>'paid','confirmed_by'=>$request->user()->id,'paid_at'=>now()]);
   $attempt->update(['status'=>'paid','confirmed_by'=>$request->user()->id,'paid_at'=>$payment->paid_at,'decided_at'=>now()]);
   AuditLog::create(['business_id'=>$order->business_id,'user_id'=>$request->user()->id,'action'=>'payment.confirmed','subject_type'=>'payment','subject_id'=>$payment->id,'details'=>['order_id'=>$order->id,'amount'=>$payment->amount,'attempt_id'=>$attempt->id]]);
  });
  return redirect()->route('orders.show',$order)->with('success','Pembayaran telah dikonfirmasi lunas.');
 }
 public function reject(Request $request,int $order) {
  $data=$request->validate(['notes'=>['required','string','max:1000']]);
  DB::transaction(function()use($request,$order,$data){
   $order=$this->scoped($request)->lockForUpdate()->findOrFail($order);
   $payment=Payment::where('order_id',$order->id)->lockForUpdate()->firstOrFail();
   if($payment->status!=='pending') throw ValidationException::withMessages(['payment'=>'Hanya pembayaran menunggu konfirmasi yang dapat ditolak.']);
   $attempt=$this->currentAttempt($payment);
   $payment->update(['status'=>'rejected','notes'=>$data['notes'],'confirmed_by'=>$request->user()->id]);
   $attempt->update(['status'=>'rejected','confirmed_by'=>$request->user()->id,'rejection_reason'=>$data['notes'],'decided_at'=>now()]);
   AuditLog::create(['business_id'=>$order->business_id,'user_id'=>$request->user()->id,'action'=>'payment.rejected','subject_type'=>'payment','subject_id'=>$payment->id,'details'=>['order_id'=>$order->id,'reason'=>$data['notes'],'attempt_id'=>$attempt->id]]);
  });
  return redirect()->route('orders.show',$order)->with('success','Pembayaran ditolak. Kasir atau Pemilik dapat mengajukan ulang transfer/QRIS.');
 }
}
