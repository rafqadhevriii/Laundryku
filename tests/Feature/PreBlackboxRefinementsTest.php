<?php
namespace Tests\Feature;

use App\Models\{CashShift,Order,Payment,Service};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class PreBlackboxRefinementsTest extends TestCase {
 use RefreshDatabase,PaymentFixtures;
 protected function setUp():void {parent::setUp();$this->paymentFixtures();}
 private function kg():Service {return Service::create(['business_id'=>$this->business->id,'name'=>'Kilogram min 2','unit'=>'kg','price'=>8000,'minimum_weight'=>2,'estimated_hours'=>24]);}
 private function cash(CashShift $shift,int $amount=25000,array $extra=[]):Payment {
  $branch=\App\Models\Branch::findOrFail($shift->branch_id);$o=$this->makeOrder($branch);
  return Payment::create(['order_id'=>$o->id,'recorded_by'=>$shift->cashier_id,'method'=>'cash','status'=>'paid','amount'=>$amount,'cash_shift_id'=>$shift->id,'paid_at'=>now(),...$extra]);
 }
 public function test_below_and_above_minimum_use_actual_weight_and_keep_snapshots():void {
  $service=$this->kg();$customer=$this->makeOrder()->customer_id;
  $this->actingAs($this->cashier)->get(route('orders.create'))->assertOk()->assertSee('Minimum 2,00 kg (tetap berat asli)')->assertSee('data-minimum="2.00"',false)->assertSee('berat di bawah minimum tetap boleh');
  foreach([['0.5','4000.00'],['2.5','20000.00']] as [$weight,$total]){
   $this->post(route('orders.store'),['customer_id'=>$customer,'items'=>[['service_id'=>$service->id,'quantity'=>$weight]],'minimum_weight'=>100,'total'=>999])->assertSessionHasNoErrors();
   $order=Order::latest('id')->firstOrFail();$this->assertSame($total,$order->total);$this->assertSame((float)$weight,(float)$order->items->first()->quantity);$this->assertSame('8000.00',$order->items->first()->unit_price);
  }
  $old=Order::latest('id')->firstOrFail();$snapshot=$old->items->toArray();$service->update(['price'=>99000,'minimum_weight'=>10]);
  $this->get(route('orders.show',$old))->assertOk()->assertSee('Rp 20.000,00');
  $this->get(route('orders.receipt',$old))->assertOk()->assertDontSee('Minimum 10');
  $this->assertSame($snapshot,$old->fresh()->items->toArray());$this->assertSame('20000.00',$old->fresh()->total);
 }
 public function test_mixed_units_sum_actual_lines_and_keep_existing_quantity_validation():void {
  $kg=$this->kg();$item=Service::create(['business_id'=>$this->business->id,'name'=>'Selimut','unit'=>'item','price'=>15000,'estimated_hours'=>48]);$customer=$this->makeOrder()->customer_id;
  $this->actingAs($this->cashier)->post(route('orders.store'),['customer_id'=>$customer,'items'=>[['service_id'=>$kg->id,'quantity'=>0.5],['service_id'=>$item->id,'quantity'=>2]]])->assertSessionHasNoErrors();
  $order=Order::latest('id')->firstOrFail();$this->assertSame('34000.00',$order->total);$this->assertEquals(['4000.00','30000.00'],$order->items->pluck('subtotal')->all());
  $this->post(route('orders.store'),['customer_id'=>$customer,'items'=>[['service_id'=>$item->id,'quantity'=>1.5]]])->assertSessionHasErrors('items');
  $this->post(route('orders.store'),['customer_id'=>$customer,'items'=>[['service_id'=>$kg->id,'quantity'=>0.555]]])->assertSessionHasErrors('items.0.quantity');
  $this->assertDatabaseCount('orders',2);
 }
 public function test_live_summary_uses_only_paid_linked_cash_and_reloads_without_writing_final_fields():void {
  $shift=$this->makeShift();$this->cash($shift,25000);$this->cash($shift,15000);
  foreach([['transfer','paid'],['qris','paid'],['cash','pending'],['cash','rejected']] as [$method,$status])$this->cash($shift,30000,['method'=>$method,'status'=>$status]);
  $sameCashier=$this->makeUser('cashier-overlap','cashier',$this->branch);$overlap=$this->makeShift($sameCashier);$this->cash($overlap,60000);
  $other=$this->makeShift($this->otherCashier);$this->cash($other,70000);
  $foreignCashier=$this->makeUser('foreign-cashier','cashier',$this->foreignBranch);$foreign=$this->makeShift($foreignCashier);$this->cash($foreign,80000);
  $history=$this->legacy(null,['paid_at'=>now(),'amount'=>90000]);$snapshot=$shift->fresh()->getAttributes();
  $r=$this->actingAs($this->cashier)->get(route('shifts.index'))->assertOk()->assertSee('Rp 40.000,00')->assertSee('Rp 140.000,00')->assertSee('2 pembayaran pada shift ini')->assertSee('Tidak dimasukkan ke ringkasan.')->assertSee('Selisih kas belum dihitung');
  $r->assertViewHas('liveCash',fn($rows)=>count($rows)===1 && $rows[$shift->id]['cash']===40000.0 && $rows[$shift->id]['expected']===140000.0 && $rows[$shift->id]['count']===2 && $rows[$shift->id]['unreviewed']);
  $r->assertViewHas('shifts',fn($rows)=>$rows->total()===1 && $rows->first()->id===$shift->id);
  $this->assertSame($snapshot,$shift->fresh()->getAttributes());$this->assertNull($history->fresh()->cash_shift_id);
  $this->cash($shift,5000);$this->get(route('shifts.index'))->assertViewHas('liveCash',fn($rows)=>$rows[$shift->id]['expected']===145000.0 && $rows[$shift->id]['count']===3);
 }
 public function test_owner_live_rows_follow_active_branch_and_closed_rows_remain_final():void {
  $a=$this->makeShift();$b=$this->makeShift($this->otherCashier);$this->cash($a);$this->cash($b,15000);
  $closed=$this->makeShift($this->cashier,['status'=>'reviewed','closed_at'=>now(),'expected_cash'=>77777,'closing_cash'=>77000,'difference'=>-777]);$this->cash($closed,999);
  $this->actingAs($this->owner)->get(route('shifts.index'))->assertOk()->assertSee('Rp 77.777,00')->assertViewHas('liveCash',fn($rows)=>count($rows)===2 && !isset($rows[$closed->id]));
  $this->post(route('active-branch.update'),['active_branch'=>$this->branch->id]);
  $this->get(route('shifts.index'))->assertViewHas('liveCash',fn($rows)=>array_keys($rows)===[$a->id])->assertViewHas('shifts',fn($rows)=>$rows->total()===2);
  $this->post(route('active-branch.update'),['active_branch'=>$this->otherBranch->id]);$this->get(route('shifts.index'))->assertViewHas('liveCash',fn($rows)=>array_keys($rows)===[$b->id]);
 }
 public function test_final_close_recomputes_receipts_and_review_does_not_recompute():void {
  $shift=$this->makeShift();$this->cash($shift,25000);$this->cash($shift,30000,['method'=>'transfer']);
  $this->actingAs($this->cashier)->get(route('shifts.index'))->assertSee('Rp 125.000,00');
  $this->cash($shift,15000); // A new receipt after page load must be included by final close.
  $this->patch(route('shifts.close',$shift),['closing_cash'=>138000])->assertSessionHasNoErrors();
  $this->assertSame('140000.00',$shift->fresh()->expected_cash);$this->assertSame('-2000.00',$shift->fresh()->difference);
  $this->cash($shift,5000); // Synthetic mutation proves a final snapshot is never recomputed by GET/review.
  $this->actingAs($this->owner)->get(route('shifts.index'))->assertSee('Rp 140.000,00')->assertViewHas('liveCash',[]);
  $this->patch(route('shifts.review',$shift))->assertSessionHasNoErrors();$this->assertSame('140000.00',$shift->fresh()->expected_cash);$this->assertSame('-2000.00',$shift->fresh()->difference);
 }
 public function test_unreviewed_history_blocks_close_unallocated_is_excluded_and_assignment_counts_once():void {
  $shift=$this->makeShift();$history=$this->legacy(null,['paid_at'=>now(),'amount'=>25000]);$this->cash($shift,15000);
  $this->actingAs($this->cashier)->get(route('shifts.index'))->assertViewHas('liveCash',fn($rows)=>$rows[$shift->id]['cash']===15000.0 && $rows[$shift->id]['unreviewed']);
  $this->patch(route('shifts.close',$shift),['closing_cash'=>115000])->assertSessionHasErrors('closing_cash');
  $this->actingAs($this->owner)->patch(route('owner.reconciliation.update',$history),['decision'=>'unallocated','reason'=>'Bukti belum memadai'])->assertSessionHasNoErrors();
  $this->actingAs($this->cashier)->get(route('shifts.index'))->assertViewHas('liveCash',fn($rows)=>$rows[$shift->id]['cash']===15000.0 && !$rows[$shift->id]['unreviewed']);
  $this->actingAs($this->owner)->patch(route('owner.reconciliation.update',$history),['decision'=>'assign','cash_shift_id'=>$shift->id,'reason'=>'Bukti penerimaan ditemukan'])->assertSessionHasNoErrors();
  $this->actingAs($this->cashier)->get(route('shifts.index'))->assertViewHas('liveCash',fn($rows)=>$rows[$shift->id]['cash']===40000.0 && $rows[$shift->id]['count']===2);
  $this->patch(route('shifts.close',$shift),['closing_cash'=>138000])->assertSessionHasNoErrors();$this->assertSame('-2000.00',$shift->fresh()->difference);
 }
 public function test_empty_live_shift_has_real_zero_and_unrelated_history_does_not_warn():void {
  $shift=$this->makeShift();
  $this->legacy($this->otherBranch,['paid_at'=>now()]);$this->legacy($this->foreignBranch,['paid_at'=>now()]);
  $this->legacy(null,['paid_at'=>$shift->opened_at->copy()->subSecond()]);$this->legacy(null,['paid_at'=>now(),'reconciliation_status'=>'unallocated']);
  $this->actingAs($this->cashier)->get(route('shifts.index'))->assertViewHas('liveCash',fn($rows)=>$rows[$shift->id]===['cash'=>0.0,'count'=>0,'expected'=>100000.0,'unreviewed'=>false]);
  $this->assertNull($shift->fresh()->expected_cash);$this->assertNull($shift->fresh()->difference);
 }
 public function test_cashier_cannot_read_an_open_shift_from_a_previous_branch_assignment():void {
  $old=$this->makeShift();$this->cash($old);$this->cashier->update(['branch_id'=>$this->otherBranch->id]);
  $this->actingAs($this->cashier)->get(route('shifts.index'))->assertOk()->assertViewHas('open',null)->assertViewHas('liveCash',[])->assertViewHas('shifts',fn($rows)=>$rows->isEmpty());
 }
}
