<?php
namespace Tests\Support;
use App\Models\{Business,Branch,User,Customer,Order,Payment,CashShift};
trait PaymentFixtures {
 protected Business $business;
 protected Branch $branch;
 protected Branch $otherBranch;
 protected User $owner;
 protected User $cashier;
 protected User $otherCashier;
 protected User $foreignOwner;
 protected Branch $foreignBranch;
 protected function paymentFixtures():void {
  $this->freezeTime();
  $this->business=Business::create(['name'=>'Laundry Uji','code'=>'REC']);
  $this->branch=Branch::create(['business_id'=>$this->business->id,'name'=>'Cabang A','code'=>'A']);
  $this->otherBranch=Branch::create(['business_id'=>$this->business->id,'name'=>'Cabang B','code'=>'B']);
  $this->owner=$this->makeUser('owner','owner',$this->branch);
  $this->cashier=$this->makeUser('cashier','cashier',$this->branch);
  $this->otherCashier=$this->makeUser('cashierb','cashier',$this->otherBranch);
  $foreign=Business::create(['name'=>'Asing','code'=>'FOREIGN']);
  $this->foreignBranch=Branch::create(['business_id'=>$foreign->id,'name'=>'Cabang Asing','code'=>'F']);
  $this->foreignOwner=$this->makeUser('foreign','owner',$this->foreignBranch);
 }
 protected function makeUser(string $name,string $role,Branch $branch):User {
  return User::create(['business_id'=>$branch->business_id,'branch_id'=>$role==='owner'?null:$branch->id,'name'=>$name,'username'=>$name,'email'=>$name.'@example.test','password'=>'password','role'=>$role,'is_active'=>true]);
 }
 protected function makeOrder(?Branch $branch=null,string $number=''):Order {
  $branch??=$this->branch;
  $customer=Customer::create(['business_id'=>$branch->business_id,'branch_id'=>$branch->id,'name'=>'Pelanggan Uji']);
  return Order::create(['business_id'=>$branch->business_id,'branch_id'=>$branch->id,'customer_id'=>$customer->id,'created_by'=>$this->owner->id,'order_number'=>$number?:uniqid('REC-'),'subtotal'=>24000,'total'=>24000,'status'=>'ready']);
 }
 protected function legacy(?Branch $branch=null,array $extra=[]):Payment {
  $order=$this->makeOrder($branch);
  return Payment::create(['order_id'=>$order->id,'recorded_by'=>$this->cashier->id,'confirmed_by'=>$this->owner->id,'method'=>'cash','status'=>'paid','amount'=>24000,'paid_at'=>now(),...$extra]);
 }
 protected function makeShift(?User $user=null,array $extra=[]):CashShift {
  $user??=$this->cashier;
  return CashShift::create(['business_id'=>$user->business_id,'branch_id'=>$user->branch_id,'cashier_id'=>$user->id,'opening_cash'=>100000,'status'=>'open','opened_at'=>now()->subHour(),...$extra]);
 }
}
