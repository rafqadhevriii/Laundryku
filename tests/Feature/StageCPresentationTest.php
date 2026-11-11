<?php
namespace Tests\Feature;
use App\Models\{Customer,InventoryItem,Service};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class StageCPresentationTest extends TestCase {
 use RefreshDatabase,PaymentFixtures;
 protected function setUp():void {parent::setUp();$this->paymentFixtures();}
 public function test_customer_search_pagination_and_invalid_edit_keep_branch_and_input():void {
  for($i=0;$i<17;$i++)Customer::create(['business_id'=>$this->business->id,'branch_id'=>$this->branch->id,'name'=>'MATCH-'.$i]);
  $other=$this->makeOrder($this->otherBranch);
  $this->actingAs($this->cashier)->get(route('customers.index',['search'=>'MATCH']))->assertOk()->assertSee('Ke halaman 2')->assertViewHas('customers',fn($rows)=>$rows->total()===17 && str_contains($rows->nextPageUrl(),'search=MATCH'));
  $customer=Customer::where('branch_id',$this->branch->id)->firstOrFail();
  $this->from(route('customers.edit',$customer))->put(route('customers.update',$customer),['name'=>'Preserved','phone'=>'invalid','notes'=>'Keep notes'])->assertSessionHasErrors('phone');
  $this->get(route('customers.edit',$customer))->assertOk()->assertSee('value="Preserved"',false)->assertSee('Keep notes')->assertSee('aria-describedby="phone-hint phone-error"',false)->assertDontSee('name="branch_id"',false);
  $this->put(route('customers.update',$other->customer_id),['name'=>'Denied'])->assertNotFound();
 }
 public function test_service_validation_preserves_values_and_passwords_are_never_repopulated():void {
  $this->actingAs($this->owner)->from(route('owner.services.create'))->post(route('owner.services.store'),['name'=>'Keep service','unit'=>'kg','price'=>'bad','minimum_weight'=>2,'estimated_hours'=>48,'is_active'=>0])->assertSessionHasErrors('price');
  $this->get(route('owner.services.create'))->assertOk()->assertSee('value="Keep service"',false)->assertSee('aria-invalid="true"',false)->assertSee('price-error')->assertSee('value="2"',false);
  $this->from(route('owner.cashiers.index'))->post(route('owner.cashiers.store'),['name'=>'Keep cashier','username'=>'new-cashier','email'=>'bad','branch_id'=>$this->branch->id,'password'=>'secret-test-password','password_confirmation'=>'different'])->assertSessionHasErrors(['email','password']);
  $this->get(route('owner.cashiers.index'))->assertOk()->assertSee('value="Keep cashier"',false)->assertDontSee('secret-test-password')->assertSee('Cabang penugasan (wajib)');
 }
 public function test_stock_page_summary_is_explicitly_page_limited_and_mutation_old_input_is_scoped():void {
  for($i=0;$i<21;$i++)InventoryItem::create(['business_id'=>$this->business->id,'branch_id'=>$this->branch->id,'name'=>'STOCK-'.sprintf('%02d',$i),'category'=>'Deterjen','unit'=>'liter','quantity'=>1,'minimum_quantity'=>2]);
  $items=InventoryItem::orderBy('name')->get();
  $this->actingAs($this->cashier)->get(route('inventory.index'))->assertOk()->assertSee('Stok menipis')->assertSee('Pada halaman ini')->assertSee('Ke halaman 2');
  $this->from(route('inventory.index'))->post(route('inventory.move',$items[0]),['_form_context'=>'movement-'.$items[0]->id,'type'=>'out','quantity'=>3,'notes'=>'Preserved movement'])->assertSessionHasErrors('quantity');
  $response=$this->get(route('inventory.index'))->assertOk()->assertSee('quantity-error-'.$items[0]->id)->assertSee('Preserved movement');
  $this->assertSame(1,substr_count($response->getContent(),'value="Preserved movement"'));
  $this->assertEquals('1.00',$items[0]->fresh()->quantity);
 }
 public function test_reconciliation_keeps_invalid_reason_only_on_target_payment():void {
  $one=$this->legacy();$two=$this->legacy();$this->makeShift();
  $this->actingAs($this->owner)->from(route('owner.reconciliation.index'))->patch(route('owner.reconciliation.update',$one),['_form_context'=>'reconcile-'.$one->id,'decision'=>'assign','reason'=>'Preserved evidence'])->assertSessionHasErrors('cash_shift_id');
  $r=$this->get(route('owner.reconciliation.index'))->assertOk()->assertSee('shift-'.$one->id.'-error')->assertSee('data-confirm-title=',false);
  $this->assertSame(1,substr_count($r->getContent(),'Preserved evidence'));
  $this->assertNull($one->fresh()->cash_shift_id);$this->assertNull($two->fresh()->cash_shift_id);
 }
 public function test_empty_states_and_owner_only_modules_keep_existing_permissions():void {
  $this->actingAs($this->cashier);
  foreach(['owner.services.index','owner.cashiers.index','owner.reports.index','owner.operational.index','owner.reconciliation.index','owner.audit.index'] as $name)$this->get(route($name))->assertForbidden();
  foreach(['customers.index'=>'Belum ada pelanggan.','inventory.index'=>'Belum ada data stok.','expenses.index'=>'Belum ada pengeluaran.'] as $name=>$empty)$this->get(route($name))->assertOk()->assertSee($empty);
  $this->actingAs($this->owner);
  foreach(['owner.services.index'=>'Belum ada layanan laundry.','owner.reports.index'=>'Belum ada pembayaran lunas pada periode ini.','owner.operational.index'=>'Tidak ada stok menipis.','owner.reconciliation.index'=>'Tidak ada pembayaran tunai tanpa shift sesuai filter.','owner.audit.index'=>'Belum ada aktivitas.'] as $name=>$empty)$this->get(route($name))->assertOk()->assertSee($empty);
  foreach(['customers.create','inventory.index','expenses.index'] as $name)$this->get(route($name))->assertSee('Pilih lokasi kerja');
 }
}
