<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class UiShellTest extends TestCase {
 use RefreshDatabase,PaymentFixtures;
 public function test_login_uses_local_assets_and_preserves_invalid_username_with_associated_errors():void {
  $this->get(route('login'))->assertOk()->assertSee('favicon.svg')->assertSee('/build/assets/',false)->assertSee('autocomplete="current-password"',false);
  $response=$this->from(route('login'))->post(route('login.store'),['username'=>'tidak-ada','password'=>'test-password']);
  $response->assertRedirect(route('login'))->assertSessionHasErrors('username');
  $this->get(route('login'))->assertOk()->assertSee('value="tidak-ada"',false)->assertSee('id="username-error"',false)->assertSee('aria-invalid="true"',false)->assertDontSee('test-password');
 }
 public function test_setup_retains_fields_and_server_validation_without_creating_user_on_failure():void {
  $this->get(route('setup.create'))->assertOk()->assertSee('autocomplete="new-password"',false)->assertSee('name="business_name"',false)->assertSee('name="password_confirmation"',false);
  $this->from(route('setup.create'))->post(route('setup.store'),['business_name'=>'Usaha Uji','owner_name'=>'Pemilik Uji','username'=>'pemilik','email'=>'invalid','password'=>'short','password_confirmation'=>'different'])->assertSessionHasErrors(['email','password']);
  $this->get(route('setup.create'))->assertOk()->assertSee('value="Usaha Uji"',false)->assertSee('id="email-error"',false)->assertDontSee('value="short"',false);
  $this->assertDatabaseCount('users',0);
 }
 public function test_owner_navigation_contains_existing_routes_and_active_page_indicator():void {
  $this->paymentFixtures();
  $response=$this->actingAs($this->owner)->get(route('owner.reconciliation.index'))->assertOk()->assertSee('aria-current="page"',false)->assertSee('aria-controls="mobile-navigation"',false);
  foreach(['owner.reports.index','owner.operational.index','owner.reconciliation.index','owner.audit.index','owner.services.index','owner.cashiers.index'] as $route)$response->assertSee(route($route),false);
 }
 public function test_cashier_navigation_omits_owner_routes_and_keeps_operational_routes():void {
  $this->paymentFixtures();
  $response=$this->actingAs($this->cashier)->get(route('dashboard'))->assertOk()->assertDontSee('name="active_branch"',false);
  foreach(['orders.index','customers.index','shifts.index','expenses.index','inventory.index'] as $route)$response->assertSee(route($route),false);
  foreach(['owner.reports.index','owner.reconciliation.index','owner.cashiers.index'] as $route)$response->assertDontSee(route($route),false);
  $this->get(route('owner.reconciliation.index'))->assertForbidden();
 }
 public function test_receipt_stays_independent_of_application_shell_and_vite_assets():void {
  $this->paymentFixtures();$order=$this->makeOrder();
  foreach([58,80] as $width){
   $this->actingAs($this->cashier)->get(route('orders.receipt',$order->id).'?paper='.$width)->assertOk()->assertSee('@media print',false)->assertSee('width:'.$width.'mm',false)->assertDontSee('mobile-navigation')->assertDontSee('/build/assets/',false);
  }
 }
}
