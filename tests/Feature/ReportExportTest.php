<?php
namespace Tests\Feature;
use App\Models\{Business,Branch,User,Customer,Order,Payment,Expense};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ReportExportTest extends TestCase {
 use RefreshDatabase;
 public function test_owner_can_download_pdf_and_csv_for_own_business():void {
  $business=Business::create(['name'=>'Laundry Uji','code'=>'REP']);
  $branch=Branch::create(['business_id'=>$business->id,'name'=>'Cabang Utama','code'=>'UT']);
  $owner=User::create(['business_id'=>$business->id,'name'=>'Pemilik','username'=>'ownerreport','email'=>'ownerreport@example.test','password'=>'password','role'=>'owner','is_active'=>true]);
  $customer=Customer::create(['business_id'=>$business->id,'branch_id'=>$branch->id,'name'=>'Pelanggan']);
  $order=Order::create(['business_id'=>$business->id,'branch_id'=>$branch->id,'customer_id'=>$customer->id,'created_by'=>$owner->id,'order_number'=>'REP-001','subtotal'=>18000,'total'=>18000]);
  Payment::create(['order_id'=>$order->id,'recorded_by'=>$owner->id,'confirmed_by'=>$owner->id,'method'=>'cash','status'=>'paid','amount'=>18000,'paid_at'=>now()]);
  Expense::create(['business_id'=>$business->id,'branch_id'=>$branch->id,'created_by'=>$owner->id,'category'=>'Listrik dan air','description'=>'Listrik','amount'=>5000,'expense_date'=>today()]);
  $params=['start'=>today()->toDateString(),'end'=>today()->toDateString(),'branch_id'=>$branch->id];
  $pdf=$this->actingAs($owner)->get(route('owner.reports.pdf',$params));
  $pdf->assertOk()->assertHeader('content-type','application/pdf');
  $this->assertStringStartsWith('%PDF', $pdf->getContent());
  $xlsx=$this->get(route('owner.reports.excel',$params));
  $xlsx->assertOk()->assertHeader('content-type','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  $bytes=$xlsx->streamedContent();
  $this->assertStringStartsWith('PK',$bytes);
  $tmp=tempnam(sys_get_temp_dir(),'xlsx');file_put_contents($tmp,$bytes);
  try { $book=\PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);$this->assertSame('Ringkasan',$book->getSheet(0)->getTitle());$this->assertEquals(18000,$book->getSheet(0)->getCell('B8')->getValue());$this->assertEquals(5000,$book->getSheet(0)->getCell('B9')->getValue());$this->assertSame('REP-001',$book->getSheet(1)->getCell('B2')->getValue());$book->disconnectWorksheets(); } finally {unlink($tmp);}
  $csv=$this->get(route('owner.reports.export',$params));
  $csv->assertOk();
  $this->assertStringContainsString('REP-001',$csv->streamedContent());
 }
 public function test_cashier_cannot_export_owner_reports():void {
  $business=Business::create(['name'=>'Laundry Uji','code'=>'REP2']);
  $branch=Branch::create(['business_id'=>$business->id,'name'=>'Cabang','code'=>'A']);
  $cashier=User::create(['business_id'=>$business->id,'branch_id'=>$branch->id,'name'=>'Kasir','username'=>'cashierreport','email'=>'cashierreport@example.test','password'=>'password','role'=>'cashier','is_active'=>true]);
  $this->actingAs($cashier)->get(route('owner.reports.pdf'))->assertForbidden();
  $this->get(route('owner.reports.export'))->assertForbidden();
  $this->get(route('owner.reports.excel'))->assertForbidden();
 }
}
