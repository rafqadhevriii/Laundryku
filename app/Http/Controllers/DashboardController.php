<?php
namespace App\Http\Controllers;
use App\Models\CashShift;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller {
 public function index(Request $request) {
  $user=$request->user()->loadMissing('business','branch');
  $branch=app(\App\Services\ActiveBranchContext::class)->id($request);
  $orders=Order::where('business_id',$user->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch));
  $payments=Payment::whereHas('order',fn($q)=>$q->where('business_id',$user->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch)));
  $expenses=Expense::where('business_id',$user->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch));
  $stock=InventoryItem::where('business_id',$user->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch));
  $today=now()->startOfDay();
  $stats=[
   ['label'=>'Pesanan hari ini','value'=>(clone $orders)->where('created_at','>=',$today)->count()],
   ['label'=>'Sedang diproses','value'=>(clone $orders)->where('status','processing')->count()],
   ['label'=>'Siap diambil','value'=>(clone $orders)->where('status','ready')->count()],
   ['label'=>'Belum lunas','value'=>(clone $orders)->where('status','!=','cancelled')->whereDoesntHave('payment',fn($q)=>$q->where('status','paid'))->count()],
   ['label'=>'Pesanan terlambat','value'=>(clone $orders)->whereIn('status',['received','processing'])->where('estimated_at','<',now())->count()],
   ['label'=>'Stok menipis','value'=>(clone $stock)->whereColumn('quantity','<=','minimum_quantity')->count()],
   ['label'=>'Pembayaran diterima hari ini','value'=>'Rp '.number_format((clone $payments)->where('status','paid')->where('paid_at','>=',$today)->sum('amount'),0,',','.')],
   ['label'=>'Pengeluaran hari ini','value'=>'Rp '.number_format((clone $expenses)->whereDate('expense_date',today())->sum('amount'),0,',','.')],
  ];
  if($user->isOwner())array_unshift($stats,['label'=>'Cabang aktif','value'=>$user->business->branches()->where('is_active',true)->when($branch,fn($q)=>$q->whereKey($branch))->count()]);
  else $stats[]=['label'=>'Shift berjalan','value'=>app(\App\Services\ActiveBranchContext::class)->scope(CashShift::query(),$request)->where('cashier_id',$user->id)->where('status','open')->exists()?'Aktif':'Belum dibuka'];
  $recent=(clone $orders)->with('customer')->latest()->limit(6)->get();
  $overdue=(clone $orders)->with('customer','branch')->whereIn('status',['received','processing'])->whereNotNull('estimated_at')->where('estimated_at','<',now())->orderBy('estimated_at')->limit(3)->get();
  $ready=(clone $orders)->with('customer','branch')->where('status','ready')->oldest('updated_at')->limit(3)->get();
  $lowStock=(clone $stock)->with('branch')->whereColumn('quantity','<=','minimum_quantity')->orderBy('quantity')->limit(3)->get();

  // Pembayaran yang sudah diterima, mengikuti usaha dan cabang aktif. Bukan omzet pesanan.
  $revenueHistory=[];
  $maxRevenue=0;
  if($user->isOwner()){
   $start=now()->startOfDay()->subDays(6);
   $byDate=(clone $payments)->where('status','paid')->whereBetween('paid_at',[$start,now()->endOfDay()])
    ->get(['paid_at','amount'])->groupBy(fn($payment)=>$payment->paid_at->toDateString());
   for($day=0;$day<7;$day++){
    $date=$start->copy()->addDays($day);
    $total=(float)($byDate->get($date->toDateString())?->sum('amount')??0);
    $revenueHistory[]=['day'=>$date->locale('id')->translatedFormat('D'),'date'=>$date->locale('id')->translatedFormat('d F Y'),'total'=>$total];
    $maxRevenue=max($maxRevenue,$total);
   }
  }
  return view('dashboard.index',compact('user','stats','recent','overdue','ready','lowStock','revenueHistory','maxRevenue'));
 }
}
