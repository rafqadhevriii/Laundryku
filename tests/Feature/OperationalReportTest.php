<?php
namespace Tests\Feature;
use App\Models\{Business,Branch,User,Customer,Order,InventoryItem,CashShift};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class OperationalReportTest extends TestCase {
 use RefreshDatabase;
 public function test_owner_can_filter_operational_report_by_branch_and_cashier_cannot_access():void {
  $business=Business::create(['name'=>'Laundry Uji','code'=>'OPS']);
  $a=Branch::create(['business_id'=>$business->id,'name'=>'Cabang Alfa','code'=>'A']);
  $b=Branch::create(['business_id'=>$business->id,'name'=>'Cabang Beta','code'=>'B']);
  $owner=User::create(['business_id'=>$business->id,'name'=>'Pemilik','username'=>'opsowner','email'=>'opsowner@example.test','password'=>'password','role'=>'owner','is_active'=>true]);
  $cashier=User::create(['business_id'=>$business->id,'branch_id'=>$a->id,'name'=>'Kasir','username'=>'opscashier','email'=>'opscashier@example.test','password'=>'password','role'=>'cashier','is_active'=>true]);
  $ca=Customer::create(['business_id'=>$business->id,'branch_id'=>$a->id,'name'=>'Pelanggan Alfa']);
  $cb=Customer::create(['business_id'=>$business->id,'branch_id'=>$b->id,'name'=>'Pelanggan Beta']);
  Order::create(['business_id'=>$business->id,'branch_id'=>$a->id,'customer_id'=>$ca->id,'created_by'=>$cashier->id,'order_number'=>'OPS-A-001','status'=>'processing','estimated_at'=>now()->subDay(),'subtotal'=>10000,'total'=>10000]);
  Order::create(['business_id'=>$business->id,'branch_id'=>$b->id,'customer_id'=>$cb->id,'created_by'=>$owner->id,'order_number'=>'OPS-B-001','status'=>'processing','estimated_at'=>now()->subDay(),'subtotal'=>10000,'total'=>10000]);
  InventoryItem::create(['business_id'=>$business->id,'branch_id'=>$a->id,'name'=>'Deterjen Alfa','category'=>'Deterjen','unit'=>'liter','quantity'=>1,'minimum_quantity'=>2]);
  InventoryItem::create(['business_id'=>$business->id,'branch_id'=>$b->id,'name'=>'Deterjen Beta','category'=>'Deterjen','unit'=>'liter','quantity'=>1,'minimum_quantity'=>2]);
  $params=['start'=>today()->toDateString(),'end'=>today()->toDateString(),'branch_id'=>$a->id];
  $this->actingAs($owner)->get(route('owner.operational.index',$params))->assertOk()->assertSee('OPS-A-001')->assertSee('Deterjen Alfa')->assertDontSee('OPS-B-001')->assertDontSee('Deterjen Beta');
  $excel=$this->get(route('owner.operational.excel',$params));
  $excel->assertOk()->assertHeader('content-type','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  $bytes=$excel->streamedContent();$this->assertStringStartsWith('PK',$bytes);
  $tmp=tempnam(sys_get_temp_dir(),'ops');file_put_contents($tmp,$bytes);
  try{$book=\PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);$this->assertSame(['Ringkasan','Pesanan Terlambat','Stok Menipis','Shift Kasir','Aktivitas Stok'],$book->getSheetNames());$this->assertSame('OPS-A-001',$book->getSheet(1)->getCell('A2')->getValue());$this->assertSame('Deterjen Alfa',$book->getSheet(2)->getCell('A2')->getValue());$this->assertNull($book->getSheet(1)->getCell('A3')->getValue());}finally{unlink($tmp);}
  $this->actingAs($cashier)->get(route('owner.operational.index'))->assertForbidden();
  $this->get(route('owner.operational.excel'))->assertForbidden();
 }
}
