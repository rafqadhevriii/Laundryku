<?php
namespace Tests\Feature;
use App\Models\{Business,Branch,User,Customer,Order,Payment,CashShift};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class CashPaymentShiftTest extends TestCase {
 use RefreshDatabase;
 private Business $business;
 private Branch $branch;
 private User $owner;
 private User $a;
 private User $b;
 protected function setUp():void {
  parent::setUp();
  $this->business=Business::create(['name'=>'Usaha','code'=>'SHIFT']);
  $this->branch=Branch::create(['business_id'=>$this->business->id,'name'=>'Utama','code'=>'A']);
  $this->owner=$this->user('owner','owner');$this->a=$this->user('a');$this->b=$this->user('b');
 }
 private function user(string $name,string $role='cashier',?Branch $branch=null):User {
  $branch??=$this->branch;
  return User::create(['business_id'=>$branch->business_id,'branch_id'=>$role==='owner'?null:$branch->id,'name'=>$name,'username'=>$name,'email'=>$name.'@example.test','password'=>'password','role'=>$role,'is_active'=>true]);
 }
 private function order(?Branch $branch=null):Order {
  $branch??=$this->branch;
  $customer=Customer::create(['business_id'=>$branch->business_id,'branch_id'=>$branch->id,'name'=>'Pelanggan']);
  return Order::create(['business_id'=>$branch->business_id,'branch_id'=>$branch->id,'customer_id'=>$customer->id,'created_by'=>$this->a->id,'order_number'=>uniqid('SHIFT-'),'subtotal'=>24000,'total'=>24000]);
 }
 private function open(User $user):CashShift {
  $this->actingAs($user)->post(route('shifts.store'),['opening_cash'=>100000])->assertSessionHasNoErrors()->assertRedirect();
  return CashShift::where('cashier_id',$user->id)->where('status','open')->firstOrFail();
 }
 private function pay(User $user,Order $order,array $extra=[]):void {
  $this->actingAs($user)->post(route('payments.store',$order->id),['method'=>'cash',...$extra])->assertSessionHasNoErrors()->assertRedirect();
 }
 public function test_overlapping_cashiers_count_only_their_own_payments_once():void {
  $this->freezeTime();$a=$this->open($this->a);$b=$this->open($this->b);
  $one=$this->order();$two=$this->order();
  $this->pay($this->a,$one,['cash_shift_id'=>$b->id]);
  $this->pay($this->b,$two);
  $this->assertDatabaseHas('payments',['order_id'=>$one->id,'cash_shift_id'=>$a->id]);
  $this->assertDatabaseHas('payments',['order_id'=>$two->id,'cash_shift_id'=>$b->id]);
  $this->actingAs($this->a)->post(route('payments.store',$one->id),['method'=>'cash'])->assertSessionHasErrors('method');
  foreach([[$this->a,$a],[$this->b,$b]] as [$user,$shift]){
   $this->actingAs($user)->patch(route('shifts.close',$shift->id),['closing_cash'=>124000])->assertSessionHasNoErrors()->assertRedirect();
   $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'expected_cash'=>124000,'difference'=>0]);
  }
  $this->assertDatabaseCount('payments',2);
 }
 public function test_cash_without_own_open_shift_is_rejected_even_when_another_cashier_has_one():void {
  $this->open($this->b);$order=$this->order();
  $this->actingAs($this->a)->post(route('payments.store',$order->id),['method'=>'cash'])->assertSessionHasErrors('method');
  $this->assertDatabaseCount('payments',0);
  $this->assertDatabaseMissing('audit_logs',['action'=>'payment.recorded']);
 }
 public function test_closed_shift_cannot_receive_cash_and_next_shift_does_not_recount_it():void {
  $this->freezeTime();$first=$this->open($this->a);$this->pay($this->a,$this->order());
  $this->actingAs($this->a)->patch(route('shifts.close',$first->id),['closing_cash'=>124000])->assertSessionHasNoErrors()->assertRedirect();
  $order=$this->order();
  $this->post(route('payments.store',$order->id),['method'=>'cash','cash_shift_id'=>$first->id])->assertSessionHasErrors('method');
  $next=$this->open($this->a);$this->pay($this->a,$order);
  $this->patch(route('shifts.close',$next->id),['closing_cash'=>124000])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertDatabaseHas('cash_shifts',['id'=>$next->id,'expected_cash'=>124000]);
 }
 public function test_owner_must_select_open_shift_in_order_branch_and_business():void {
  $valid=$this->open($this->a);$order=$this->order();
  $branch=Branch::create(['business_id'=>$this->business->id,'name'=>'Kedua','code'=>'B']);
  $other=$this->open($this->user('other','cashier',$branch));
  $business=Business::create(['name'=>'Asing','code'=>'OTHER']);
  $foreignBranch=Branch::create(['business_id'=>$business->id,'name'=>'Asing','code'=>'C']);
  $foreign=$this->open($this->user('foreign','cashier',$foreignBranch));
  foreach([null,$other->id,$foreign->id] as $id){
   $this->actingAs($this->owner)->post(route('payments.store',$order->id),['method'=>'cash','cash_shift_id'=>$id])->assertSessionHasErrors('method');
  }
  $this->assertDatabaseCount('payments',0);
  $this->pay($this->owner,$order,['cash_shift_id'=>$valid->id]);
  $this->assertDatabaseHas('payments',['order_id'=>$order->id,'cash_shift_id'=>$valid->id,'recorded_by'=>$this->owner->id]);
  $this->actingAs($this->a)->patch(route('shifts.close',$valid->id),['closing_cash'=>124000])->assertSessionHasNoErrors()->assertRedirect();
  $this->actingAs($this->owner)->post(route('payments.store',$this->order()->id),['method'=>'cash','cash_shift_id'=>$valid->id])->assertSessionHasErrors('method');
 }
 public function test_branches_reconcile_independently_and_cashier_cannot_pay_other_branch():void {
  $a=$this->open($this->a);
  $branch=Branch::create(['business_id'=>$this->business->id,'name'=>'Kedua','code'=>'B']);
  $user=$this->user('other','cashier',$branch);$b=$this->open($user);
  $one=$this->order();$two=$this->order($branch);
  $this->actingAs($user)->post(route('payments.store',$one->id),['method'=>'cash'])->assertNotFound();
  $this->pay($this->a,$one);$this->pay($user,$two);
  foreach([[$this->a,$a],[$user,$b]] as [$cashier,$shift]){
   $this->actingAs($cashier)->patch(route('shifts.close',$shift->id),['closing_cash'=>124000])->assertSessionHasNoErrors()->assertRedirect();
   $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'expected_cash'=>124000,'difference'=>0]);
  }
 }
 public function test_digital_payment_does_not_require_or_affect_cash_shift():void {
  $order=$this->order();
  $this->actingAs($this->a)->post(route('payments.store',$order->id),['method'=>'qris'])->assertSessionHasNoErrors()->assertRedirect();
  $this->actingAs($this->owner)->patch(route('payments.confirm',$order->id))->assertSessionHasNoErrors()->assertRedirect();
  $shift=$this->open($this->a);
  $this->assertDatabaseHas('payments',['order_id'=>$order->id,'cash_shift_id'=>null,'status'=>'paid']);
  $this->patch(route('shifts.close',$shift->id),['closing_cash'=>100000])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'expected_cash'=>100000]);
 }
 public function test_legacy_payment_is_preserved_and_blocks_ambiguous_shift_closure():void {
  $shift=$this->open($this->a);$order=$this->order();
  $payment=Payment::create(['order_id'=>$order->id,'recorded_by'=>$this->a->id,'confirmed_by'=>$this->a->id,'method'=>'cash','status'=>'paid','amount'=>24000,'paid_at'=>now()]);
  $this->actingAs($this->a)->patch(route('shifts.close',$shift->id),['closing_cash'=>124000])->assertSessionHasErrors('closing_cash');
  $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'status'=>'open','expected_cash'=>null]);
  $this->assertDatabaseHas('payments',['id'=>$payment->id,'cash_shift_id'=>null,'amount'=>24000]);
 }
 public function test_owner_payment_form_lists_only_open_shifts_in_order_branch():void {
  $shift=$this->open($this->a);
  $branch=Branch::create(['business_id'=>$this->business->id,'name'=>'Kedua','code'=>'B']);
  $other=$this->open($this->user('KasirRahasia','cashier',$branch));
  $this->actingAs($this->owner)->get(route('orders.show',$this->order()->id))->assertOk()->assertSee('name="cash_shift_id"',false)->assertSee('value="'.$shift->id.'"',false)->assertDontSee('KasirRahasia');
 }

 public function test_owner_retries_rejected_digital_payment_as_pending_without_entering_cash_shift():void {
  $shift=$this->open($this->a);$order=$this->order();
  $this->actingAs($this->a)->post(route('payments.store',$order->id),['method'=>'transfer'])->assertSessionHasNoErrors()->assertRedirect();
  $this->actingAs($this->owner)->patch(route('payments.reject',$order->id),['notes'=>'Belum diterima'])->assertSessionHasNoErrors()->assertRedirect();
  $id=Payment::where('order_id',$order->id)->value('id');
  $this->post(route('payments.retry',$order->id),['method'=>'qris'])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertDatabaseCount('payments',1);
  $this->assertDatabaseHas('payments',['id'=>$id,'cash_shift_id'=>null,'method'=>'qris','status'=>'pending']);
  $this->assertDatabaseHas('audit_logs',['action'=>'payment.retried','subject_id'=>$id]);
  $this->actingAs($this->a)->patch(route('shifts.close',$shift->id),['closing_cash'=>100000])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'expected_cash'=>100000,'difference'=>0]);
 }

}
