<?php
namespace Tests\Feature;
use App\Models\{Business,Branch,User,Customer,Order,Payment,InventoryItem,CashShift};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class OperationalFlowTest extends TestCase {
 use RefreshDatabase;
 private Business $business; private Branch $branch; private User $owner; private User $cashier; private Order $order;
 protected function setUp():void {
  parent::setUp();
  $this->business=Business::create(['name'=>'Usaha Test','code'=>'FLOW']);
  $this->branch=Branch::create(['business_id'=>$this->business->id,'name'=>'Cabang Test','code'=>'CT']);
  $this->owner=User::create(['business_id'=>$this->business->id,'name'=>'Pemilik','username'=>'ownerflow','email'=>'ownerflow@example.test','password'=>'password','role'=>'owner','is_active'=>true]);
  $this->cashier=User::create(['business_id'=>$this->business->id,'branch_id'=>$this->branch->id,'name'=>'Kasir','username'=>'cashierflow','email'=>'cashierflow@example.test','password'=>'password','role'=>'cashier']);
  $customer=Customer::create(['business_id'=>$this->business->id,'branch_id'=>$this->branch->id,'name'=>'Pelanggan']);
  $this->order=Order::create(['business_id'=>$this->business->id,'branch_id'=>$this->branch->id,'customer_id'=>$customer->id,'created_by'=>$this->cashier->id,'order_number'=>'FLOW-001','status'=>'ready','subtotal'=>24000,'total'=>24000]);
 }
 public function test_cash_payment_is_paid_and_cannot_be_recorded_twice():void {
  $this->actingAs($this->cashier);
  $this->post(route('payments.store',$this->order->id),['method'=>'cash'])->assertRedirect();
  $this->assertDatabaseHas('payments',['order_id'=>$this->order->id,'status'=>'paid','amount'=>24000]);
  $this->post(route('payments.store',$this->order->id),['method'=>'cash'])->assertSessionHasErrors('method');
  $this->assertDatabaseCount('payments',1);
 }
 public function test_pickup_requires_full_payment():void {
  $this->actingAs($this->cashier);
  $this->patch(route('orders.status',$this->order->id),['status'=>'picked_up'])->assertSessionHasErrors('status');
  $this->assertDatabaseHas('orders',['id'=>$this->order->id,'status'=>'ready']);
  $this->post(route('payments.store',$this->order->id),['method'=>'cash'])->assertRedirect();
  $this->patch(route('orders.status',$this->order->id),['status'=>'picked_up'])->assertRedirect();
  $this->assertDatabaseHas('orders',['id'=>$this->order->id,'status'=>'picked_up']);
 }
 public function test_transfer_requires_owner_confirmation():void {
  $this->actingAs($this->cashier)->post(route('payments.store',$this->order->id),['method'=>'transfer','reference'=>'TRX123'])->assertRedirect();
  $this->assertDatabaseHas('payments',['order_id'=>$this->order->id,'status'=>'pending']);
  $this->actingAs($this->cashier)->patch(route('payments.confirm',$this->order->id))->assertForbidden();
  $this->actingAs($this->owner)->patch(route('payments.confirm',$this->order->id))->assertRedirect();
  $this->assertDatabaseHas('payments',['order_id'=>$this->order->id,'status'=>'paid','confirmed_by'=>$this->owner->id]);
  $this->assertDatabaseHas('audit_logs',['action'=>'payment.confirmed']);
 }
 public function test_cash_shift_reconciles_cash_payment():void {
  $this->actingAs($this->cashier)->post(route('shifts.store'),['opening_cash'=>100000])->assertRedirect();
  $shift=CashShift::firstOrFail();
  $this->actingAs($this->cashier)->post(route('payments.store',$this->order->id),['method'=>'cash'])->assertRedirect();
  $this->actingAs($this->cashier)->patch(route('shifts.close',$shift->id),['closing_cash'=>123000])->assertRedirect();
  $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'status'=>'submitted','expected_cash'=>124000,'closing_cash'=>123000,'difference'=>-1000]);
  $this->actingAs($this->owner)->patch(route('shifts.review',$shift->id))->assertRedirect();
  $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'status'=>'reviewed']);
 }
 public function test_cashier_stock_adjustment_waits_for_owner_approval():void {
  $item=InventoryItem::create(['business_id'=>$this->business->id,'branch_id'=>$this->branch->id,'name'=>'Sabun','category'=>'Deterjen','unit'=>'liter','quantity'=>10,'minimum_quantity'=>2]);
  $this->actingAs($this->cashier)->post(route('inventory.move',$item->id),['type'=>'adjustment','quantity'=>7,'notes'=>'Hitung ulang'])->assertRedirect();
  $this->assertDatabaseHas('inventory_items',['id'=>$item->id,'quantity'=>10]);
  $this->assertDatabaseHas('inventory_movements',['inventory_item_id'=>$item->id,'status'=>'pending','quantity'=>7]);
  $movement=$item->movements()->firstOrFail();
  $this->actingAs($this->owner)->patch(route('inventory.review',$movement->id),['decision'=>'approve'])->assertRedirect();
  $this->assertDatabaseHas('inventory_items',['id'=>$item->id,'quantity'=>7]);
  $this->assertDatabaseHas('inventory_movements',['id'=>$movement->id,'status'=>'approved']);
 }
}
