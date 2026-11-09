<?php
namespace Tests\Feature;
use App\Models\{AuditLog,Payment,PaymentAttempt};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class DigitalPaymentRetryTest extends TestCase {
 use RefreshDatabase,PaymentFixtures;
 protected function setUp():void {parent::setUp();$this->paymentFixtures();}
 private function rejected($branch=null):Payment {
  return $this->legacy($branch,['method'=>'transfer','status'=>'rejected','paid_at'=>null,'reference'=>'REF-LAMA','notes'=>'Bukti lama tidak cocok']);
 }
 public function test_cashier_retry_preserves_legacy_rejection_and_requires_owner_confirmation():void {
  $p=$this->rejected();
  $this->actingAs($this->cashier)->get(route('orders.show',$p->order_id))->assertOk()->assertSee('Bukti lama tidak cocok')->assertSee('Ajukan Ulang Pembayaran Digital');
  $this->post(route('payments.retry',$p->order_id),['method'=>'qris','reference'=>'REF-BARU','notes'=>'Bukti baru','amount'=>1,'status'=>'paid'])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertDatabaseHas('payments',['id'=>$p->id,'status'=>'pending','method'=>'qris','amount'=>24000,'confirmed_by'=>null,'paid_at'=>null,'cash_shift_id'=>null]);
  $this->assertDatabaseCount('payments',1);
  $this->assertDatabaseCount('payment_attempts',2);
  $this->assertDatabaseHas('payment_attempts',['payment_id'=>$p->id,'attempt_number'=>1,'status'=>'rejected','reference'=>'REF-LAMA','rejection_reason'=>'Bukti lama tidak cocok','confirmed_by'=>$this->owner->id]);
  $this->assertDatabaseHas('payment_attempts',['payment_id'=>$p->id,'attempt_number'=>2,'status'=>'pending','reference'=>'REF-BARU','notes'=>'Bukti baru','amount'=>24000]);
  $this->get(route('orders.show',$p->order_id))->assertOk()->assertSee('Bukti lama tidak cocok')->assertSee('REF-LAMA')->assertSee('REF-BARU')->assertSee('Menunggu konfirmasi');
  $this->patch(route('payments.confirm',$p->order_id))->assertForbidden();
  $this->patch(route('orders.status',$p->order_id),['status'=>'picked_up'])->assertSessionHasErrors('status');
  $this->actingAs($this->owner)->patch(route('payments.confirm',$p->order_id))->assertSessionHasNoErrors()->assertRedirect();
  $this->assertDatabaseHas('payments',['id'=>$p->id,'status'=>'paid','confirmed_by'=>$this->owner->id]);
  $this->assertDatabaseHas('payment_attempts',['payment_id'=>$p->id,'attempt_number'=>1,'status'=>'rejected']);
  $this->assertDatabaseHas('payment_attempts',['payment_id'=>$p->id,'attempt_number'=>2,'status'=>'paid']);
  $audit=AuditLog::where('action','payment.retried')->sole();
  $this->assertSame('Bukti lama tidak cocok',$audit->details['before']['notes']);
  $this->assertSame('pending',$audit->details['after']['status']);
  $this->get(route('owner.reports.index'))->assertViewHas('totalRevenue',fn($v)=>(float)$v===24000.0);
 }
 public function test_pending_paid_and_repeated_retry_requests_do_not_duplicate_payment_or_attempts():void {
  $p=$this->rejected();
  $this->actingAs($this->cashier)->post(route('payments.retry',$p->order_id),['method'=>'transfer'])->assertSessionHasNoErrors()->assertRedirect();
  foreach(['payments.retry','payments.store'] as $route){
   $this->post(route($route,$p->order_id),['method'=>'qris'])->assertSessionHasErrors('method');
  }
  $this->assertDatabaseCount('payment_attempts',2);
  $this->actingAs($this->owner)->patch(route('payments.confirm',$p->order_id))->assertSessionHasNoErrors()->assertRedirect();
  $this->post(route('payments.retry',$p->order_id),['method'=>'transfer'])->assertSessionHasErrors('method');
  $this->assertDatabaseCount('payment_attempts',2);
  $this->assertDatabaseCount('payments',1);
  $this->assertSame(1,AuditLog::where('action','payment.retried')->count());
 }
 public function test_cashier_and_owner_cannot_retry_payments_outside_their_scope():void {
  $other=$this->rejected($this->otherBranch);$foreign=$this->rejected($this->foreignBranch);
  $this->actingAs($this->cashier)->post(route('payments.retry',$other->order_id),['method'=>'transfer'])->assertNotFound();
  $this->post(route('payments.store',$other->order_id),['method'=>'transfer'])->assertNotFound();
  $this->post(route('payments.retry',$foreign->order_id),['method'=>'transfer'])->assertNotFound();
  $this->actingAs($this->owner)->post(route('payments.retry',$foreign->order_id),['method'=>'transfer'])->assertNotFound();
  $this->assertDatabaseCount('payment_attempts',0);
  $this->assertDatabaseMissing('audit_logs',['action'=>'payment.retried']);
 }
 public function test_repeated_rejections_keep_all_attempts_references_notes_and_reasons():void {
  $order=$this->makeOrder();
  $this->actingAs($this->cashier)->post(route('payments.store',$order->id),['method'=>'transfer','reference'=>'REF-1','notes'=>'Catatan pertama'])->assertSessionHasNoErrors()->assertRedirect();
  foreach([1,2] as $number){
   $this->actingAs($this->owner)->patch(route('payments.reject',$order->id),['notes'=>'Penolakan '.$number])->assertSessionHasNoErrors()->assertRedirect();
   $this->actingAs($this->cashier)->post(route('payments.retry',$order->id),['method'=>'qris','reference'=>'REF-'.($number+1)])->assertSessionHasNoErrors()->assertRedirect();
  }
  $payment=Payment::where('order_id',$order->id)->sole();
  $this->assertDatabaseHas('payment_attempts',['payment_id'=>$payment->id,'attempt_number'=>1,'reference'=>'REF-1','notes'=>'Catatan pertama','rejection_reason'=>'Penolakan 1','status'=>'rejected']);
  $this->assertDatabaseHas('payment_attempts',['payment_id'=>$payment->id,'attempt_number'=>2,'reference'=>'REF-2','rejection_reason'=>'Penolakan 2','status'=>'rejected']);
  $this->assertDatabaseHas('payment_attempts',['payment_id'=>$payment->id,'attempt_number'=>3,'reference'=>'REF-3','status'=>'pending']);
  $this->assertDatabaseCount('payments',1);
  $this->assertDatabaseCount('payment_attempts',3);
  $this->assertSame(2,AuditLog::where('action','payment.rejected')->count());
  $this->assertSame(2,AuditLog::where('action','payment.retried')->count());
  $this->get(route('orders.show',$order->id))->assertSee('Penolakan 1')->assertSee('Penolakan 2')->assertSee('REF-3');
 }
 public function test_retry_requires_digital_method_and_existing_rejected_payment_even_for_owner():void {
  $p=$this->rejected();$shift=$this->makeShift();
  foreach([$this->cashier,$this->owner] as $user){
   foreach(['payments.store','payments.retry'] as $route){
    $this->actingAs($user)->post(route($route,$p->order_id),['method'=>'cash','cash_shift_id'=>$shift->id])->assertSessionHasErrors('method');
   }
  }
  $this->post(route('payments.retry',$this->makeOrder()->id),['method'=>'transfer'])->assertSessionHasErrors('method');
  $this->assertDatabaseHas('payments',['id'=>$p->id,'status'=>'rejected','reference'=>'REF-LAMA']);
  $this->assertDatabaseCount('payment_attempts',0);
 }
 public function test_inactive_account_branch_and_business_cannot_retry():void {
  $p=$this->rejected();
  $this->cashier->update(['is_active'=>false]);
  $this->actingAs($this->cashier)->post(route('payments.retry',$p->order_id),['method'=>'transfer'])->assertForbidden();
  $this->cashier->update(['is_active'=>true]);$this->branch->update(['is_active'=>false]);
  $this->post(route('payments.retry',$p->order_id),['method'=>'transfer'])->assertForbidden();
  $this->branch->update(['is_active'=>true]);$this->business->update(['is_active'=>false]);
  $this->actingAs($this->owner)->post(route('payments.retry',$p->order_id),['method'=>'transfer'])->assertForbidden();
  $this->assertDatabaseCount('payment_attempts',0);
 }
 public function test_owner_can_retry_other_branch_of_same_business_but_retry_does_not_enter_cash_shift():void {
  $p=$this->rejected($this->otherBranch);$shift=$this->makeShift($this->otherCashier);
  $this->actingAs($this->owner)->post(route('payments.retry',$p->order_id),['method'=>'transfer','cash_shift_id'=>$shift->id])->assertSessionHasNoErrors()->assertRedirect();
  $this->patch(route('payments.confirm',$p->order_id))->assertSessionHasNoErrors()->assertRedirect();
  $this->actingAs($this->otherCashier)->patch(route('shifts.close',$shift->id),['closing_cash'=>100000])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'expected_cash'=>100000,'difference'=>0]);
  $this->assertDatabaseHas('payments',['id'=>$p->id,'cash_shift_id'=>null,'amount'=>24000]);
 }
}
