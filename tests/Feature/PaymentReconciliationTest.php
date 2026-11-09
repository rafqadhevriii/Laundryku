<?php
namespace Tests\Feature;
use App\Models\{AuditLog,Payment};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase {
 use RefreshDatabase,PaymentFixtures;
 protected function setUp():void {parent::setUp();$this->paymentFixtures();}
 private function decide(Payment $payment,array $data){
  return $this->actingAs($this->owner)->patch(route('owner.reconciliation.update',$payment->id),$data);
 }
 public function test_only_active_owner_can_access_page_and_make_decisions():void {
  $p=$this->legacy();
  $this->get(route('owner.reconciliation.index'))->assertRedirect(route('login'));
  $this->actingAs($this->cashier)->get(route('owner.reconciliation.index'))->assertForbidden();
  $this->patch(route('owner.reconciliation.update',$p->id),['decision'=>'unallocated','reason'=>'Tidak ada bukti'])->assertForbidden();
  $this->owner->update(['is_active'=>false]);
  $this->actingAs($this->owner)->get(route('owner.reconciliation.index'))->assertForbidden();
  $this->assertDatabaseMissing('audit_logs',['action'=>'payment.reconciled']);
 }
 public function test_filters_and_list_are_scoped_without_automatic_assignment():void {
  $p=$this->legacy();$shift=$this->makeShift();
  $foreign=$this->legacy($this->foreignBranch);
  $other=$this->legacy($this->otherBranch);
  $digital=$this->legacy(null,['method'=>'qris']);
  $pending=$this->legacy(null,['status'=>'pending']);
  $assigned=$this->legacy(null,['cash_shift_id'=>$shift->id]);
  $this->actingAs($this->owner)->get(route('owner.reconciliation.index',['branch_id'=>$this->branch->id,'start'=>now()->toDateString(),'end'=>now()->toDateString(),'order_number'=>$p->order->order_number]))
   ->assertOk()->assertViewHas('payments',fn($rows)=>$rows->modelKeys()===[$p->id]);
  $this->assertNull($p->fresh()->cash_shift_id);
  $this->assertDatabaseMissing('audit_logs',['action'=>'payment.reconciled']);
  $this->get(route('owner.reconciliation.index',['branch_id'=>$this->foreignBranch->id]))->assertSessionHasErrors('branch_id');
  $this->get(route('owner.reconciliation.index',['start'=>'2026-11-10','end'=>'2026-11-01']))->assertSessionHasErrors('end');
 }
 public function test_reasons_are_required_for_both_decisions_and_no_money_is_changed():void {
  $p=$this->legacy();$shift=$this->makeShift();
  foreach(['assign','unallocated'] as $decision){
   $this->decide($p,['decision'=>$decision,'cash_shift_id'=>$shift->id,'reason'=>'   '])->assertSessionHasErrors('reason');
  }
  $this->assertDatabaseHas('payments',['id'=>$p->id,'cash_shift_id'=>null,'status'=>'paid','method'=>'cash','amount'=>24000,'reconciliation_status'=>null]);
  $this->assertDatabaseMissing('audit_logs',['action'=>'payment.reconciled']);
 }
 public function test_cross_business_payment_and_shift_and_cross_branch_shift_are_rejected():void {
  $p=$this->legacy();$foreign=$this->legacy($this->foreignBranch);
  $otherShift=$this->makeShift($this->otherCashier);
  $foreignCashier=$this->makeUser('foreigncash','cashier',$this->foreignBranch);
  $foreignShift=$this->makeShift($foreignCashier);
  $this->decide($foreign,['decision'=>'unallocated','reason'=>'Bukti'])->assertNotFound();
  $this->decide($p,['decision'=>'assign','reason'=>'Bukti','cash_shift_id'=>$foreignShift->id])->assertNotFound();
  $this->decide($p,['decision'=>'assign','reason'=>'Bukti','cash_shift_id'=>$otherShift->id])->assertSessionHasErrors('cash_shift_id');
  $this->assertNull($p->fresh()->cash_shift_id);
  $this->assertDatabaseMissing('audit_logs',['action'=>'payment.reconciled']);
 }
 public function test_assignment_validates_period_missing_timestamp_and_final_shifts():void {
  $p=$this->legacy();
  $future=$this->makeShift(null,['opened_at'=>now()->addMinute()]);
  $this->decide($p,['decision'=>'assign','cash_shift_id'=>$future->id,'reason'=>'Bukti'])->assertSessionHasErrors('cash_shift_id');
  foreach(['submitted','reviewed'] as $status){
   $final=$this->makeShift(null,['status'=>$status,'closed_at'=>now(),'expected_cash'=>124000,'closing_cash'=>123000,'difference'=>-1000]);
   $this->decide($p,['decision'=>'assign','cash_shift_id'=>$final->id,'reason'=>'Bukti'])->assertSessionHasErrors('cash_shift_id');
   $this->assertDatabaseHas('cash_shifts',['id'=>$final->id,'expected_cash'=>124000,'difference'=>-1000,'status'=>$status]);
  }
  $shift=$this->makeShift();
  $p->update(['paid_at'=>null]);
  $this->decide($p,['decision'=>'assign','cash_shift_id'=>$shift->id,'reason'=>'Bukti'])->assertSessionHasErrors('cash_shift_id');
  $this->decide($p,['decision'=>'unallocated','reason'=>'Waktu bayar tidak diketahui'])->assertSessionHasNoErrors()->assertRedirect();
 }
 public function test_one_payment_can_only_be_assigned_once_and_shift_totals_do_not_duplicate_revenue():void {
  $p=$this->legacy();$a=$this->makeShift();
  $second=$this->makeUser('second','cashier',$this->branch);$b=$this->makeShift($second);
  $data=['decision'=>'assign','cash_shift_id'=>$a->id,'reason'=>'Bukti penerimaan kas shift A'];
  $this->decide($p,$data)->assertSessionHasNoErrors()->assertRedirect();
  $this->decide($p,[...$data,'cash_shift_id'=>$b->id])->assertSessionHasErrors('decision');
  $this->decide($p,['decision'=>'unallocated','reason'=>'Klik ulang'])->assertSessionHasErrors('decision');
  $this->assertDatabaseHas('payments',['id'=>$p->id,'cash_shift_id'=>$a->id,'amount'=>24000,'reconciliation_status'=>'assigned']);
  $this->assertDatabaseCount('payments',1);
  $audit=AuditLog::where('action','payment.reconciled')->sole();
  $this->assertSame($this->owner->id,$audit->user_id);
  $this->assertNull($audit->details['before']['cash_shift_id']);
  $this->assertSame($a->id,$audit->details['after']['cash_shift_id']);
  $this->assertSame('Bukti penerimaan kas shift A',$audit->details['reason']);
  $this->assertNotEmpty($audit->details['decided_at']);
  foreach([[$this->cashier,$a,124000],[$second,$b,100000]] as [$user,$shift,$total]){
   $this->actingAs($user)->patch(route('shifts.close',$shift->id),['closing_cash'=>$total])->assertSessionHasNoErrors()->assertRedirect();
   $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'expected_cash'=>$total,'difference'=>0]);
  }
  $this->actingAs($this->owner)->get(route('owner.reports.index'))->assertOk()->assertViewHas('totalRevenue',fn($total)=>(float)$total===24000.0);
  $this->get(route('owner.reconciliation.index'))->assertViewHas('payments',fn($rows)=>$rows->isEmpty())->assertSee('Bukti penerimaan kas shift A');
 }
 public function test_reviewed_unallocated_cash_does_not_block_or_enter_shift_and_is_reported():void {
  $p=$this->legacy();$shift=$this->makeShift();
  $this->decide($p,['decision'=>'unallocated','reason'=>'Bukti tidak cukup; periode tumpang tindih'])->assertSessionHasNoErrors()->assertRedirect();
  $this->actingAs($this->cashier)->patch(route('shifts.close',$shift->id),['closing_cash'=>100000])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'expected_cash'=>100000,'difference'=>0]);
  $this->assertDatabaseHas('payments',['id'=>$p->id,'cash_shift_id'=>null,'method'=>'cash','status'=>'paid','amount'=>24000]);
  $this->actingAs($this->owner)->get(route('owner.reconciliation.index',['review'=>'unallocated']))->assertOk()->assertSee('Bukti tidak cukup; periode tumpang tindih')->assertViewHas('summary',fn($s)=>(float)$s['unallocated']===24000.0);
  $this->get(route('owner.reports.index'))->assertViewHas('totalRevenue',fn($v)=>(float)$v===24000.0);
 }
 public function test_unreviewed_payment_still_blocks_until_all_relevant_payments_reviewed():void {
  $one=$this->legacy();$two=$this->legacy();$shift=$this->makeShift();
  $this->decide($one,['decision'=>'unallocated','reason'=>'Belum pasti'])->assertSessionHasNoErrors();
  $this->actingAs($this->cashier)->patch(route('shifts.close',$shift->id),['closing_cash'=>100000])->assertSessionHasErrors('closing_cash');
  $this->assertDatabaseHas('cash_shifts',['id'=>$shift->id,'status'=>'open']);
  $this->decide($two,['decision'=>'unallocated','reason'=>'Bukti kedua tidak cukup'])->assertSessionHasNoErrors();
  $this->actingAs($this->cashier)->patch(route('shifts.close',$shift->id),['closing_cash'=>100000])->assertSessionHasNoErrors()->assertRedirect();
 }
 public function test_new_evidence_can_allocate_previously_unallocated_cash_without_losing_decision_history():void {
  $p=$this->legacy();$shift=$this->makeShift();
  $this->decide($p,['decision'=>'unallocated','reason'=>'Belum ada bukti'])->assertSessionHasNoErrors();
  $this->decide($p,['decision'=>'unallocated','reason'=>'Klik dua kali'])->assertSessionHasErrors('decision');
  $this->decide($p,['decision'=>'assign','cash_shift_id'=>$shift->id,'reason'=>'Bukti baru cocok'])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertSame(2,AuditLog::where('action','payment.reconciled')->count());
  $this->actingAs($this->owner)->get(route('owner.reconciliation.index'))->assertSee('Belum ada bukti')->assertSee('Bukti baru cocok');
 }
 public function test_non_legacy_or_non_paid_cash_cannot_be_reconciled():void {
  foreach([['method'=>'transfer'],['status'=>'pending'],['cash_shift_id'=>$this->makeShift()->id]] as $extra){
   $p=$this->legacy(null,$extra);
   $r=$this->decide($p,['decision'=>'unallocated','reason'=>'Tidak sah']);
   if(isset($extra['cash_shift_id']))$r->assertSessionHasErrors('decision');else $r->assertNotFound();
  }
  $this->assertDatabaseMissing('audit_logs',['action'=>'payment.reconciled']);
 }
}
