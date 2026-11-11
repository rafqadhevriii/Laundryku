<?php
namespace Tests\Feature;

use App\Models\{CashShift,Order,Payment,Service};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class StageBPresentationTest extends TestCase {
 use RefreshDatabase,PaymentFixtures;
 protected function setUp():void {parent::setUp();$this->paymentFixtures();}
 public function test_dashboard_renders_controller_statistics_and_preserves_branch_scope():void {
  $own=$this->makeOrder(null,'VISIBLE-A');$foreign=$this->makeOrder($this->otherBranch,'HIDDEN-B');
  Payment::create(['order_id'=>$own->id,'recorded_by'=>$this->cashier->id,'method'=>'transfer','status'=>'paid','amount'=>24000,'paid_at'=>now()]);
  $response=$this->actingAs($this->cashier)->get(route('dashboard'))->assertOk()->assertSee('VISIBLE-A')->assertDontSee('HIDDEN-B')->assertSee('Rp 24.000');
  $response->assertViewHas('stats',fn($rows)=>collect($rows)->firstWhere('label','Pesanan hari ini')['value']===1);
  $this->actingAs($this->owner)->get(route('dashboard'))->assertOk()->assertSee('VISIBLE-A')->assertSee('HIDDEN-B')->assertViewHas('stats',fn($rows)=>collect($rows)->firstWhere('label','Pesanan hari ini')['value']===2);
 }
 public function test_order_filters_pagination_and_mobile_cards_use_the_same_scoped_records():void {
  for($i=0;$i<16;$i++)$this->makeOrder(null,'MATCH-'.$i);
  $this->makeOrder($this->otherBranch,'MATCH-SECRET');
  $response=$this->actingAs($this->cashier)->get(route('orders.index',['search'=>'MATCH-','status'=>'ready']))->assertOk()->assertSee('orders-cards')->assertDontSee('MATCH-SECRET')->assertSee('Navigasi halaman')->assertSee('aria-label="Ke halaman 2"',false);
  $response->assertViewHas('orders',fn($orders)=>$orders->total()===16 && str_contains($orders->nextPageUrl(),'search=MATCH-') && str_contains($orders->nextPageUrl(),'status=ready'));
  $this->get(route('orders.index',['search'=>'none']))->assertOk()->assertSee('Tidak ada pesanan yang sesuai.');
 }
 public function test_create_validation_preserves_nonsequential_service_rows_and_notes():void {
  $order=$this->makeOrder();
  $service=Service::create(['business_id'=>$this->business->id,'name'=>'Cuci Kering','unit'=>'kg','price'=>8000,'estimated_hours'=>24,'minimum_weight'=>3]);
  $data=['customer_id'=>$order->customer_id,'items'=>[0=>['service_id'=>$service->id,'quantity'=>2.5],4=>['service_id'=>$service->id,'quantity'=>1.25]],'notes'=>'Pisahkan pakaian putih'];
  $this->actingAs($this->cashier)->from(route('orders.create'))->post(route('orders.store'),$data)->assertSessionHasErrors('items.0.service_id');
  $this->get(route('orders.create'))->assertOk()->assertSee('name="items[4][quantity]"',false)->assertSee('value="1.25"',false)->assertSee('Pisahkan pakaian putih')->assertSee('Tagihan dihitung saat disimpan');
  $this->assertDatabaseCount('orders',1);
 }
 public function test_order_submission_still_uses_server_prices_and_longest_duration_for_mixed_units():void {
  $order=$this->makeOrder();
  $kg=Service::create(['business_id'=>$this->business->id,'name'=>'Per kg','unit'=>'kg','price'=>8000,'estimated_hours'=>24,'minimum_weight'=>3]);
  $item=Service::create(['business_id'=>$this->business->id,'name'=>'Selimut','unit'=>'item','price'=>15000,'estimated_hours'=>48]);
  $this->actingAs($this->cashier)->post(route('orders.store'),['customer_id'=>$order->customer_id,'total'=>1,'items'=>[['service_id'=>$kg->id,'quantity'=>2.5],['service_id'=>$item->id,'quantity'=>2]]])->assertSessionHasNoErrors()->assertRedirect();
  $created=Order::latest('id')->firstOrFail();
  $this->assertEquals('50000.00',$created->total);
  $this->assertSame(now()->addHours(48)->toDateTimeString(),$created->estimated_at->toDateTimeString());
  $this->get(route('orders.show',$created->id))->assertOk()->assertSee('Rp 50.000,00')->assertSee('Progres pengerjaan');
 }
 public function test_detail_payment_actions_follow_current_state_role_and_existing_receipt_routes():void {
  $order=$this->makeOrder();
  $this->actingAs($this->cashier)->get(route('orders.show',$order->id))->assertOk()->assertDontSee('Tandai Sudah Diambil')->assertSee('paper=58',false)->assertSee('paper=80',false);
  $payment=Payment::create(['order_id'=>$order->id,'recorded_by'=>$this->cashier->id,'method'=>'transfer','status'=>'pending','amount'=>24000]);
  $this->get(route('orders.show',$order->id))->assertOk()->assertDontSee('Konfirmasi Lunas')->assertDontSee(route('payments.confirm',$order->id),false);
  $this->actingAs($this->owner)->get(route('orders.show',$order->id))->assertOk()->assertSee('Konfirmasi Lunas')->assertSee('Alasan penolakan')->assertSee('data-confirm-title=',false);
  $payment->update(['status'=>'paid','paid_at'=>now()]);
  $this->actingAs($this->cashier)->get(route('orders.show',$order->id))->assertOk()->assertSee('Tandai Sudah Diambil')->assertDontSee('Ajukan Ulang Pembayaran Digital');
 }
 public function test_shift_shows_persisted_balances_without_querying_or_recalculating_receipts():void {
  $shift=$this->makeShift(null,['status'=>'submitted','closed_at'=>now(),'expected_cash'=>123456.78,'closing_cash'=>123400,'difference'=>-56.78]);
  $queries=[];DB::listen(function($query)use(&$queries){$queries[]=$query->sql;});
  $this->actingAs($this->owner)->get(route('shifts.index'))->assertOk()->assertSee('Rp 123.456,78')->assertSee('Rp -56,78')->assertSee('Kurang')->assertSee('Tinjau Selesai');
  $this->assertFalse(collect($queries)->contains(fn($sql)=>str_contains(strtolower($sql),'sum(')));
  $this->actingAs($this->cashier)->get(route('shifts.index'))->assertOk()->assertDontSee('Tinjau Selesai');
  $this->makeShift();
  $this->get(route('shifts.index'))->assertOk()->assertSee('Tunai lunas terhubung')->assertSee('Saldo seharusnya saat ini')->assertSee('Sementara, bukan saldo final penutupan');
 }
 public function test_cancelled_order_has_no_new_payment_or_next_status_action():void {
  $order=$this->makeOrder();$order->update(['status'=>'cancelled']);
  $this->actingAs($this->cashier)->get(route('orders.show',$order->id))->assertOk()->assertSee('Pesanan dibatalkan tidak dapat dibayar.')->assertDontSee(route('payments.store',$order->id),false)->assertDontSee('Tandai Siap Diambil');
 }
}
