<?php
namespace Tests\Feature;
use App\Models\{Business,Branch,User,Customer,Order,InventoryItem};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class DashboardTest extends TestCase {
 use RefreshDatabase;
 public function test_cashier_dashboard_shows_only_own_branch_alerts():void {
  $business=Business::create(['name'=>'Laundry Dashboard','code'=>'DASH']);
  $a=Branch::create(['business_id'=>$business->id,'name'=>'Cabang A','code'=>'A']);
  $b=Branch::create(['business_id'=>$business->id,'name'=>'Cabang B','code'=>'B']);
  $cashier=User::create(['business_id'=>$business->id,'branch_id'=>$a->id,'name'=>'Kasir','username'=>'dashcashier','email'=>'dashcashier@example.test','password'=>'password','role'=>'cashier','is_active'=>true]);
  $owner=User::create(['business_id'=>$business->id,'name'=>'Pemilik','username'=>'dashowner','email'=>'dashowner@example.test','password'=>'password','role'=>'owner','is_active'=>true]);
  $customerA=Customer::create(['business_id'=>$business->id,'branch_id'=>$a->id,'name'=>'Pelanggan Cabang A']);
  $customerB=Customer::create(['business_id'=>$business->id,'branch_id'=>$b->id,'name'=>'Pelanggan Cabang B']);
  Order::create(['business_id'=>$business->id,'branch_id'=>$a->id,'customer_id'=>$customerA->id,'created_by'=>$cashier->id,'order_number'=>'DASH-A-OVERDUE','status'=>'processing','estimated_at'=>now()->subHour(),'subtotal'=>12000,'total'=>12000]);
  Order::create(['business_id'=>$business->id,'branch_id'=>$b->id,'customer_id'=>$customerB->id,'created_by'=>$owner->id,'order_number'=>'DASH-B-OVERDUE','status'=>'processing','estimated_at'=>now()->subHour(),'subtotal'=>12000,'total'=>12000]);
  InventoryItem::create(['business_id'=>$business->id,'branch_id'=>$a->id,'name'=>'Stok A','category'=>'Deterjen','unit'=>'liter','quantity'=>1,'minimum_quantity'=>2]);
  InventoryItem::create(['business_id'=>$business->id,'branch_id'=>$b->id,'name'=>'BahanRahasiaCabangB','category'=>'Deterjen','unit'=>'liter','quantity'=>1,'minimum_quantity'=>2]);
  $this->actingAs($cashier)->get(route('dashboard'))->assertOk()->assertSee('DASH-A-OVERDUE')->assertSee('Stok A')->assertDontSee('DASH-B-OVERDUE')->assertDontSee('BahanRahasiaCabangB');
  $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertSee('DASH-A-OVERDUE')->assertSee('DASH-B-OVERDUE')->assertSee('Stok A')->assertSee('BahanRahasiaCabangB');
 }
}
