<?php
namespace App\Http\Controllers;
use App\Models\CashShift;
use App\Models\Payment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class CashShiftController extends Controller {
 private function scoped(Request $request){
  return app(\App\Services\ActiveBranchContext::class)->scope(CashShift::query(),$request);
 }
 public function index(Request $request){
  $shifts=$this->scoped($request)->with(['branch','cashier'])->latest()->paginate(15);
  $open=$request->user()->isCashier()?CashShift::where('cashier_id',$request->user()->id)->where('status','open')->first():null;
  return view('shifts.index',compact('shifts','open'));
 }
 public function store(Request $request){
  abort_unless($request->user()->isCashier(),403);
  $data=$request->validate(['opening_cash'=>['required','numeric','min:0','max:999999999999.99','decimal:0,2']]);
  DB::transaction(function()use($request,$data){
   $user=$request->user();
   $user=\App\Models\User::whereKey($user->id)->lockForUpdate()->firstOrFail();
   abort_unless($user->branch?->is_active,403);
   if(CashShift::where('cashier_id',$user->id)->where('status','open')->exists()) throw ValidationException::withMessages(['opening_cash'=>'Kamu masih memiliki shift yang terbuka.']);
   $shift=CashShift::create(['business_id'=>$user->business_id,'branch_id'=>$user->branch_id,'cashier_id'=>$user->id,'opening_cash'=>$data['opening_cash'],'opened_at'=>now(),'status'=>'open']);
   AuditLog::create(['business_id'=>$user->business_id,'user_id'=>$user->id,'action'=>'shift.opened','subject_type'=>'cash_shift','subject_id'=>$shift->id,'details'=>['branch_id'=>$shift->branch_id,'opening_cash'=>$shift->opening_cash]]);
  });
  return back()->with('success','Shift berhasil dibuka.');
 }
 public function close(Request $request,int $shift){
  abort_unless($request->user()->isCashier(),403);
  $data=$request->validate(['closing_cash'=>['required','numeric','min:0','max:999999999999.99','decimal:0,2'],'notes'=>['nullable','string','max:2000']]);
  DB::transaction(function()use($request,$shift,$data){
   $s=$this->scoped($request)->lockForUpdate()->findOrFail($shift);
   abort_unless($s->cashier_id===$request->user()->id && $s->status==='open',403);
   // Jangan tutup dengan angka lebih kecil jika shift lama masih memuat
   // pembayaran sebelum pengaitan shift diperkenalkan. Tidak menebak pemiliknya.
   $unassigned=Payment::whereNull('cash_shift_id')->where('method','cash')->where('status','paid')
    ->whereNull('reconciliation_status')->whereBetween('paid_at',[$s->opened_at,now()])
    ->whereHas('order',fn($q)=>$q->where('business_id',$s->business_id)->where('branch_id',$s->branch_id))->exists();
   if($unassigned) throw ValidationException::withMessages(['closing_cash'=>'Ada pembayaran tunai lama tanpa shift pada periode ini. Minta Pemilik merekonsiliasi data sebelum menutup shift.']);
   $cash=$s->payments()->where('method','cash')->where('status','paid')->sum('amount');
   $expected=round((float)$s->opening_cash+(float)$cash,2);
   $s->update(['closing_cash'=>$data['closing_cash'],'expected_cash'=>$expected,'difference'=>round((float)$data['closing_cash']-$expected,2),'status'=>'submitted','closed_at'=>now(),'notes'=>$data['notes']??null]);
   AuditLog::create(['business_id'=>$s->business_id,'user_id'=>$request->user()->id,'action'=>'shift.closed','subject_type'=>'cash_shift','subject_id'=>$s->id,'details'=>['branch_id'=>$s->branch_id,'expected_cash'=>$s->expected_cash,'closing_cash'=>$s->closing_cash,'difference'=>$s->difference]]);
  });
  return back()->with('success','Penutupan shift berhasil dikirim untuk ditinjau.');
 }
 public function review(Request $request,int $shift){
  abort_unless($request->user()->isOwner(),403);
  DB::transaction(function()use($request,$shift){
   $s=$this->scoped($request)->lockForUpdate()->findOrFail($shift);
   abort_unless($s->status==='submitted',422);
   $s->update(['status'=>'reviewed','reviewed_by'=>$request->user()->id]);
   AuditLog::create(['business_id'=>$s->business_id,'user_id'=>$request->user()->id,'action'=>'shift.reviewed','subject_type'=>'cash_shift','subject_id'=>$s->id,'details'=>['branch_id'=>$s->branch_id,'difference'=>$s->difference]]);
  });
  return back()->with('success','Shift selesai ditinjau.');
 }
}
