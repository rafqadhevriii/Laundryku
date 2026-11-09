<?php
namespace App\Http\Controllers;
use App\Models\{Order,InventoryItem,CashShift,InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class OperationalReportController extends Controller {
 public function index(Request $request){
  $user=$request->user();
  $filters=$request->validate(['start'=>['nullable','date'],'end'=>['nullable','date','after_or_equal:start'],'branch_id'=>['nullable',Rule::exists('branches','id')->where('business_id',$user->business_id)]]);
  $start=$filters['start']??now()->startOfMonth()->toDateString();
  $end=$filters['end']??now()->toDateString();
  $branch=app(\App\Services\ActiveBranchContext::class)->reportBranch($request,$filters['branch_id']??null);
  $businessId=$user->business_id;
  $orders=Order::where('business_id',$businessId)->when($branch,fn($q)=>$q->where('branch_id',$branch))->whereDate('created_at','>=',$start)->whereDate('created_at','<=',$end);
  $statusCounts=(clone $orders)->selectRaw('status,COUNT(*) as total')->groupBy('status')->pluck('total','status');
  $overdue=(clone $orders)->with('customer','branch')->whereIn('status',['received','processing'])->whereNotNull('estimated_at')->where('estimated_at','<',now())->orderBy('estimated_at')->paginate(10,['*'],'overdue_page')->withQueryString();
  $stock=InventoryItem::where('business_id',$businessId)->when($branch,fn($q)=>$q->where('branch_id',$branch));
  $lowStock=(clone $stock)->with('branch')->whereColumn('quantity','<=','minimum_quantity')->orderBy('quantity')->paginate(10,['*'],'stock_page')->withQueryString();
  $shifts=CashShift::where('business_id',$businessId)->when($branch,fn($q)=>$q->where('branch_id',$branch))->whereDate('opened_at','>=',$start)->whereDate('opened_at','<=',$end);
  $shiftSummary=['total'=>(clone $shifts)->count(),'open'=>(clone $shifts)->where('status','open')->count(),'submitted'=>(clone $shifts)->where('status','submitted')->count(),'reviewed'=>(clone $shifts)->where('status','reviewed')->count(),'difference'=>(clone $shifts)->whereIn('status',['submitted','reviewed'])->sum('difference')];
  $shiftRows=(clone $shifts)->with('cashier','branch')->latest('opened_at')->paginate(10,['*'],'shift_page')->withQueryString();
  $movements=InventoryMovement::whereHas('item',fn($q)=>$q->where('business_id',$businessId)->when($branch,fn($q)=>$q->where('branch_id',$branch)))->whereDate('created_at','>=',$start)->whereDate('created_at','<=',$end);
  $movementCounts=(clone $movements)->selectRaw('type,status,COUNT(*) as total')->groupBy('type','status')->get();
  $branches=$user->business->branches()->orderBy('name')->get();
  return view('reports.operational',compact('start','end','branch','statusCounts','overdue','lowStock','shiftSummary','shiftRows','movementCounts','branches'));
 }
}
