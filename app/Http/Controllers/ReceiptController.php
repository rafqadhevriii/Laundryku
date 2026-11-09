<?php
namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;
class ReceiptController extends Controller {
 public function show(Request $request,int $order){
  $user=$request->user();
  $order=app(\App\Services\ActiveBranchContext::class)->scope(Order::with(['items','customer','branch','payment']),$request)
   ->findOrFail($order);
  $business=$user->business;
  $width=$request->query('paper','80');
  abort_unless(in_array($width,['58','80'],true),422);
  return view('orders.receipt',compact('order','business','width'));
 }
}
