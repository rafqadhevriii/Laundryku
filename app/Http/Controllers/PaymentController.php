<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\CashShift;
use App\Models\Payment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class PaymentController extends Controller {
 private function scoped(Request $request) {
  $user=$request->user();
  return Order::query()->where('business_id',$user->business_id)
   ->when($user->isCashier(),fn($q)=>$q->where('branch_id',$user->branch_id));
 }
 public function store(Request $request,int $order) {
  $data=$request->validate([
   'cash_shift_id'=>['nullable','integer'],
   'method'=>['required',Rule::in(['cash','transfer','qris'])],
   'reference'=>['nullable','string','max:120'],
   'notes'=>['nullable','string','max:1000'],
  ]);
  DB::transaction(function()use($request,$order,$data){
   $order=$this->scoped($request)->lockForUpdate()->findOrFail($order);
   if($order->status==='cancelled') throw ValidationException::withMessages(['method'=>'Pesanan dibatalkan tidak dapat dibayar.']);
   $existing=Payment::where('order_id',$order->id)->lockForUpdate()->first();
   if($existing && ($existing->status!=='rejected' || !$request->user()->isOwner())) throw ValidationException::withMessages(['method'=>'Pembayaran sudah dicatat. Muat ulang halaman.']);
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
   if($existing) $existing->update($payload);
   else Payment::create($payload);
   AuditLog::create(['business_id'=>$order->business_id,'user_id'=>$request->user()->id,'action'=>'payment.recorded','subject_type'=>'payment','subject_id'=>$existing?->id??Payment::where('order_id',$order->id)->value('id'),'details'=>['order_id'=>$order->id,'method'=>$data['method'],'amount'=>$order->total,'status'=>$payload['status'],'cash_shift_id'=>$shift?->id]]);
  });
  return redirect()->route('orders.show',$order)->with('success','Pembayaran berhasil dicatat.');
 }
 public function confirm(Request $request,int $order) {
  DB::transaction(function()use($request,$order){
   $order=$this->scoped($request)->lockForUpdate()->findOrFail($order);
   $payment=Payment::where('order_id',$order->id)->lockForUpdate()->firstOrFail();
   if($payment->status!=='pending' || !in_array($payment->method,['transfer','qris'])) throw ValidationException::withMessages(['payment'=>'Pembayaran ini tidak menunggu konfirmasi.']);
   $payment->update(['status'=>'paid','confirmed_by'=>$request->user()->id,'paid_at'=>now()]);
   AuditLog::create(['business_id'=>$order->business_id,'user_id'=>$request->user()->id,'action'=>'payment.confirmed','subject_type'=>'payment','subject_id'=>$payment->id,'details'=>['order_id'=>$order->id,'amount'=>$payment->amount]]);
  });
  return redirect()->route('orders.show',$order)->with('success','Pembayaran telah dikonfirmasi lunas.');
 }
 public function reject(Request $request,int $order) {
  $data=$request->validate(['notes'=>['required','string','max:1000']]);
  DB::transaction(function()use($request,$order,$data){
   $order=$this->scoped($request)->lockForUpdate()->findOrFail($order);
   $payment=Payment::where('order_id',$order->id)->lockForUpdate()->firstOrFail();
   if($payment->status!=='pending') throw ValidationException::withMessages(['payment'=>'Hanya pembayaran menunggu konfirmasi yang dapat ditolak.']);
   $payment->update(['status'=>'rejected','notes'=>$data['notes'],'confirmed_by'=>$request->user()->id]);
   AuditLog::create(['business_id'=>$order->business_id,'user_id'=>$request->user()->id,'action'=>'payment.rejected','subject_type'=>'payment','subject_id'=>$payment->id,'details'=>['order_id'=>$order->id,'reason'=>$data['notes']]]);
  });
  return redirect()->route('orders.show',$order)->with('success','Pembayaran ditolak. Perlu tindak lanjut pemilik.');
 }
}
