<?php
namespace Tests\Feature;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class BranchIsolationTest extends TestCase {
 use RefreshDatabase;
 private Business $business;
 private Branch $branchA;
 private Branch $branchB;
 private User $cashierA;
 private Customer $customerA;
 private Customer $customerB;
 private Service $service;
 protected function setUp():void {
  parent::setUp();
  $this->business=Business::create(['name'=>'Usaha Uji','code'=>'TEST']);
  $this->branchA=Branch::create(['business_id'=>$this->business->id,'name'=>'Cabang A','code'=>'A']);
  $this->branchB=Branch::create(['business_id'=>$this->business->id,'name'=>'Cabang B','code'=>'B']);
  $this->cashierA=User::create(['business_id'=>$this->business->id,'branch_id'=>$this->branchA->id,'name'=>'Kasir A','username'=>'kasira','email'=>'kasira@example.test','password'=>'testing-password','role'=>'cashier']);
  $this->customerA=Customer::create(['business_id'=>$this->business->id,'branch_id'=>$this->branchA->id,'name'=>'Pelanggan A']);
  $this->customerB=Customer::create(['business_id'=>$this->business->id,'branch_id'=>$this->branchB->id,'name'=>'Pelanggan B']);
  $this->service=Service::create(['business_id'=>$this->business->id,'name'=>'Cuci Kering','unit'=>'kg','price'=>8000,'estimated_hours'=>24]);
 }
 private function order(Branch $branch,Customer $customer):Order {
  return Order::create(['business_id'=>$this->business->id,'branch_id'=>$branch->id,'customer_id'=>$customer->id,'created_by'=>$this->cashierA->id,'order_number'=>'TEST-'.$branch->code.'-'.uniqid(),'subtotal'=>8000,'total'=>8000]);
 }
 public function test_cashier_cannot_read_another_branch_order_or_receipt():void {
  $other=$this->order($this->branchB,$this->customerB);
  $this->actingAs($this->cashierA);
  $this->get(route('orders.show',$other->id))->assertNotFound();
  $this->get(route('orders.receipt',$other->id))->assertNotFound();
  $this->patch(route('orders.status',$other->id),['status'=>'processing'])->assertNotFound();
 }
 public function test_cashier_cannot_create_order_with_other_branch_customer():void {
  $this->actingAs($this->cashierA);
  $this->post(route('orders.store'),['customer_id'=>$this->customerB->id,'items'=>[['service_id'=>$this->service->id,'quantity'=>2]]])->assertSessionHasErrors('customer_id');
  $this->assertDatabaseCount('orders',0);
 }
 public function test_cashier_can_create_order_for_own_branch():void {
  $this->actingAs($this->cashierA);
  $this->post(route('orders.store'),['customer_id'=>$this->customerA->id,'items'=>[['service_id'=>$this->service->id,'quantity'=>2]]])->assertRedirect();
  $this->assertDatabaseHas('orders',['business_id'=>$this->business->id,'branch_id'=>$this->branchA->id,'customer_id'=>$this->customerA->id,'total'=>16000]);
  $this->assertDatabaseHas('audit_logs',['action'=>'order.created']);
 }
 public function test_cashier_cannot_change_other_branch_stock():void {
  $item=InventoryItem::create(['business_id'=>$this->business->id,'branch_id'=>$this->branchB->id,'name'=>'Deterjen','category'=>'Deterjen','unit'=>'liter','quantity'=>10,'minimum_quantity'=>2]);
  $this->actingAs($this->cashierA);
  $this->post(route('inventory.move',$item->id),['type'=>'out','quantity'=>1])->assertNotFound();
  $this->assertDatabaseHas('inventory_items',['id'=>$item->id,'quantity'=>10]);
 }
 public function test_cashier_cannot_edit_another_branch_customer():void {
  $this->actingAs($this->cashierA);
  $this->get(route('customers.edit',$this->customerB->id))->assertNotFound();
  $this->put(route('customers.update',$this->customerB->id),['name'=>'Diganti'])->assertNotFound();
 }
}
