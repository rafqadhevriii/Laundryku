<?php
namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;
class ReceiptController extends Controller {
 public function show(Request $request,int $order){
  $user=$request->user();
  $order=Order::with(['items','customer','branch','payment'])
   ->where('business_id',$user->business_id)
   ->when($user->isCashier(),fn($q)=>$q->where('branch_id',$user->branch_id))
   ->findOrFail($order);
  $business=$user->business;
  $width=$request->query('paper','80');
  abort_unless(in_array($width,['58','80'],true),422);
  return view('orders.receipt',compact('order','business','width'));
 }
}
