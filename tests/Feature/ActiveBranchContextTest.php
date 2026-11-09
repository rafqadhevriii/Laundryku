<?php
namespace Tests\Feature;

use App\Models\{Customer,InventoryItem,Order,Service,Payment};
use App\Services\ActiveBranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class ActiveBranchContextTest extends TestCase {
 use RefreshDatabase,PaymentFixtures;
 protected function setUp():void {parent::setUp();$this->paymentFixtures();}
 private function choose(mixed $value,string $return='dashboard') {
  return $this->post(route('active-branch.update'),['active_branch'=>$value,'return_route'=>$return]);
 }
 private function payload(Order $order):array {
  $service=Service::create(['business_id'=>$this->business->id,'name'=>'Cuci','unit'=>'kg','price'=>8000,'estimated_hours'=>24]);
  return ['customer_id'=>$order->customer_id,'items'=>[['service_id'=>$service->id,'quantity'=>3]]];
 }
 public function test_owner_context_persists_and_scopes_before_pagination_and_aggregation():void {
  $a=$this->makeOrder(null,'ONLY-A');$b=$this->makeOrder($this->otherBranch,'ONLY-B');$this->makeOrder($this->foreignBranch,'FOREIGN');
  foreach([$this->branch,$this->otherBranch] as $branch)InventoryItem::create(['business_id'=>$this->business->id,'branch_id'=>$branch->id,'name'=>'Stock-'.$branch->code,'category'=>'Deterjen','unit'=>'kg','quantity'=>3,'minimum_quantity'=>1]);
  $this->actingAs($this->owner);$this->choose($this->branch->id)->assertRedirect(route('dashboard'));
  $this->get(route('dashboard'))->assertOk()->assertSee('ONLY-A')->assertDontSee('ONLY-B')->assertViewHas('stats',fn($rows)=>collect($rows)->firstWhere('label','Pesanan hari ini')['value']===1);
  $this->get(route('orders.index'))->assertViewHas('orders',fn($rows)=>$rows->total()===1 && $rows->first()->id===$a->id);
  $this->get(route('customers.index'))->assertViewHas('customers',fn($rows)=>$rows->total()===1 && $rows->first()->id===$a->customer_id);
  $this->get(route('inventory.index'))->assertSee('Stock-A')->assertDontSee('Stock-B');
  $this->choose('all');
  $this->get(route('dashboard'))->assertSee('ONLY-A')->assertSee('ONLY-B')->assertDontSee('FOREIGN')->assertViewHas('stats',fn($rows)=>collect($rows)->firstWhere('label','Pesanan hari ini')['value']===2);
 }
 public function test_all_mode_blocks_new_records_and_selected_order_uses_server_context():void {
  $a=$this->makeOrder();$b=$this->makeOrder($this->otherBranch);$payload=$this->payload($b);
  $this->actingAs($this->owner)->get(route('orders.create'))->assertSee('Pilih cabang aktif terlebih dahulu')->assertDontSee('name="customer_id"',false);
  $this->post(route('orders.store'),$payload+['branch_id'=>$this->otherBranch->id])->assertSessionHasErrors('active_branch');
  $this->withSession(['_old_input'=>['customer_id'=>$a->customer_id]]);$this->choose($this->otherBranch->id,'orders.create')->assertRedirect(route('orders.create'))->assertSessionMissing('_old_input');
  $this->get(route('orders.create'))->assertOk()->assertDontSee('name="branch_id"',false)->assertViewHas('customers',fn($rows)=>$rows->count()===1 && $rows->first()->id===$b->customer_id);
  $this->post(route('orders.store'),$payload+['branch_id'=>$this->foreignBranch->id])->assertSessionHasNoErrors();
  $created=Order::latest('id')->firstOrFail();$this->assertSame($this->otherBranch->id,$created->branch_id);$this->assertEquals('24000.00',$created->total);
  $this->post(route('orders.store'),array_replace($payload,['customer_id'=>$a->customer_id]))->assertSessionHasErrors('customer_id');
  $this->assertDatabaseCount('orders',3);
 }
 public function test_cashier_ignores_session_query_and_payload_and_cannot_switch():void {
  $a=$this->makeOrder();$this->makeOrder($this->otherBranch,'OTHER');$payload=$this->payload($a);
  $this->actingAs($this->cashier)->withSession([app(ActiveBranchContext::class)->sessionKey($this->cashier)=>$this->otherBranch->id]);
  $this->get(route('orders.create',['branch_id'=>$this->otherBranch->id]))->assertOk()->assertSee('Cabang penugasan')->assertDontSee('name="active_branch"',false);
  $this->choose($this->otherBranch->id)->assertForbidden();
  $this->post(route('orders.store'),$payload+['branch_id'=>$this->otherBranch->id])->assertSessionHasNoErrors();
  $this->assertSame($this->branch->id,Order::latest('id')->firstOrFail()->branch_id);
  $this->assertSame($this->branch->id,$this->cashier->fresh()->branch_id);
  $this->cashier->update(['branch_id'=>null]);$this->get(route('dashboard'))->assertForbidden();
 }
 public function test_selector_rejects_foreign_inactive_and_malformed_values_and_redirects_safely():void {
  $this->actingAs($this->owner);$this->otherBranch->update(['is_active'=>false]);
  foreach([$this->foreignBranch->id,$this->otherBranch->id,0,'0','1e2',['all'],null] as $value)$this->choose($value)->assertSessionHasErrors('active_branch');
  $this->choose($this->branch->id,'https://example.test/orders')->assertRedirect(route('dashboard'));
  $this->get('/workspace/branch')->assertMethodNotAllowed();
 }
 public function test_stale_get_falls_back_but_stale_post_never_mutates():void {
  $a=$this->makeOrder();$payload=$this->payload($a);$key=app(ActiveBranchContext::class)->sessionKey($this->owner);
  $this->actingAs($this->owner);$this->choose($this->branch->id);$this->branch->update(['is_active'=>false]);
  $this->post(route('orders.store'),$payload)->assertSessionHasErrors('active_branch')->assertSessionMissing($key);$this->assertDatabaseCount('orders',1);
  foreach([$this->branch->id,$this->foreignBranch->id,999999,['bad'],null] as $saved){
   $this->withSession([$key=>$saved])->get(route('dashboard'))->assertOk()->assertSee('Cabang aktif sebelumnya tidak lagi valid.')->assertSessionMissing($key)->assertViewHas('activeBranchId',null);
  }
  $this->actingAs($this->cashier)->get(route('dashboard'))->assertForbidden();
 }
 public function test_selected_scope_protects_existing_records_and_all_preserves_historical_branch():void {
  $a=$this->makeOrder();$b=$this->makeOrder($this->otherBranch);$foreign=$this->makeOrder($this->foreignBranch);
  $this->actingAs($this->owner);$this->choose($this->branch->id);
  foreach(['orders.show','orders.receipt'] as $route)$this->get(route($route,$b->id))->assertNotFound();
  $this->put(route('customers.update',$b->customer_id),['name'=>'Unauthorized'])->assertNotFound();
  $this->post(route('payments.store',$b->id),['method'=>'transfer'])->assertNotFound();
  $this->choose('all');
  $this->get(route('orders.show',$b->id))->assertOk();
  $this->put(route('customers.update',$b->customer_id),['name'=>'Historical','branch_id'=>$this->branch->id])->assertSessionHasNoErrors();
  $this->assertSame($this->otherBranch->id,Customer::findOrFail($b->customer_id)->branch_id);
  $this->get(route('orders.show',$foreign->id))->assertNotFound();
 }
 public function test_customer_stock_and_expense_creation_use_active_branch():void {
  $this->actingAs($this->owner);$this->choose($this->otherBranch->id);
  $this->post(route('customers.store'),['name'=>'New','branch_id'=>$this->foreignBranch->id])->assertSessionHasNoErrors();
  $this->assertDatabaseHas('customers',['name'=>'New','branch_id'=>$this->otherBranch->id,'business_id'=>$this->business->id]);
  $stock=['name'=>'New stock','category'=>'Deterjen','unit'=>'kg','minimum_quantity'=>1,'branch_id'=>$this->foreignBranch->id];
  $this->post(route('inventory.store'),$stock)->assertSessionHasNoErrors();$item=InventoryItem::latest('id')->firstOrFail();$this->assertSame($this->otherBranch->id,$item->branch_id);
  $expense=['category'=>'Operasional lain','description'=>'New expense','amount'=>5000,'expense_date'=>today()->toDateString(),'branch_id'=>$this->foreignBranch->id];
  $this->post(route('expenses.store'),$expense)->assertSessionHasNoErrors();$this->assertDatabaseHas('expenses',['description'=>'New expense','branch_id'=>$this->otherBranch->id]);
  $this->choose($this->branch->id);$this->post(route('inventory.move',$item->id),['type'=>'in','quantity'=>5])->assertNotFound();$this->assertEquals(0,$item->fresh()->quantity);
  $this->choose('all');$this->post(route('inventory.store'),$stock)->assertSessionHasErrors('active_branch');$this->post(route('expenses.store'),$expense)->assertSessionHasErrors('active_branch');$this->post(route('customers.store'),['name'=>'Blocked'])->assertSessionHasErrors('active_branch');
 }
 public function test_reports_keep_all_mode_filters_but_cannot_override_selected_branch():void {
  $this->makeOrder();$this->makeOrder($this->otherBranch);$this->actingAs($this->owner);
  foreach(['owner.reports.index','owner.operational.index','owner.reconciliation.index'] as $name){
   $this->get(route($name,['branch_id'=>$this->otherBranch->id]))->assertOk();
  }
  $this->choose($this->branch->id);
  foreach(['owner.reports.index','owner.operational.index','owner.reconciliation.index','owner.reports.export','owner.operational.excel'] as $name){
   $this->get(route($name))->assertOk();
   $this->get(route($name,['branch_id'=>$this->otherBranch->id]))->assertSessionHasErrors('branch_id');
  }
 }
 public function test_revenue_and_shift_snapshots_remain_unchanged_when_switching():void {
  $a=$this->makeOrder();$b=$this->makeOrder($this->otherBranch);
  foreach([[$a,24000],[$b,48000]] as [$order,$amount])Payment::create(['order_id'=>$order->id,'recorded_by'=>$this->cashier->id,'method'=>'cash','status'=>'paid','amount'=>$amount,'paid_at'=>now()]);
  $shiftA=$this->makeShift(null,['status'=>'submitted','closed_at'=>now(),'expected_cash'=>124000,'closing_cash'=>123000,'difference'=>-1000]);
  $shiftB=$this->makeShift($this->otherCashier,['status'=>'submitted','closed_at'=>now(),'expected_cash'=>148000,'closing_cash'=>148000,'difference'=>0]);
  $snapshots=[$shiftA->fresh()->getAttributes(),$shiftB->fresh()->getAttributes()];$payments=Payment::orderBy('id')->get()->toArray();
  $this->actingAs($this->owner);
  foreach([['all',72000,2],[$this->branch->id,24000,1],[$this->otherBranch->id,48000,1]] as [$selected,$total,$count]){
   $this->choose($selected);
   $this->get(route('owner.reports.index'))->assertOk()->assertViewHas('totalRevenue',fn($value)=>(float)$value===$total*1.0)->assertViewHas('totalOrders',$count);
   $this->get(route('shifts.index'))->assertOk()->assertViewHas('shifts',fn($rows)=>$rows->total()===$count);
  }
  $this->assertSame($payments,Payment::orderBy('id')->get()->toArray());
  $this->assertSame($snapshots,[$shiftA->fresh()->getAttributes(),$shiftB->fresh()->getAttributes()]);
 }
 public function test_session_is_revalidated_after_branch_transfer_and_account_deactivation():void {
  $empty=\App\Models\Branch::create(['business_id'=>$this->business->id,'name'=>'Empty','code'=>'EMPTY']);
  $this->actingAs($this->owner);$this->choose($empty->id);
  // Empty branch can legitimately be moved by administrative maintenance; old selection must expire.
  $empty->update(['business_id'=>$this->foreignBranch->business_id]);
  $this->post(route('customers.store'),['name'=>'Never created'])->assertSessionHasErrors('active_branch');
  $this->assertDatabaseMissing('customers',['name'=>'Never created']);
  $this->owner->update(['is_active'=>false]);$this->get(route('dashboard'))->assertForbidden();
  $this->post(route('logout'))->assertRedirect(route('login'));
  $this->owner->update(['is_active'=>true]);$this->business->update(['is_active'=>false]);
  $this->actingAs($this->owner)->get(route('dashboard'))->assertForbidden();
 }
 public function test_login_logout_and_account_namespace_prevent_context_inheritance():void {
  $key=app(ActiveBranchContext::class)->sessionKey($this->owner);
  $this->actingAs($this->owner);$this->choose($this->branch->id);
  $this->actingAs($this->foreignOwner)->get(route('dashboard'))->assertOk()->assertViewHas('activeBranchId',null);
  $this->post(route('logout'))->assertRedirect(route('login'))->assertSessionMissing(ActiveBranchContext::SESSION_ROOT);
  $this->withSession([$key=>$this->branch->id])->post(route('login.store'),['username'=>'owner','password'=>'password'])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'))->assertSessionMissing(ActiveBranchContext::SESSION_ROOT);
  $this->get(route('dashboard'))->assertViewHas('activeBranchId',null);
 }
}
