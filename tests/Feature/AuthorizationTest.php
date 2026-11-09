<?php
namespace Tests\Feature;
use App\Models\User;
use Tests\TestCase;
class AuthorizationTest extends TestCase {
 private function user(string $role='cashier',bool $active=true):User {
  $user=new User(['name'=>'Penguji','username'=>'penguji','role'=>$role,'is_active'=>$active]);
  $user->id=999;
  return $user;
 }
 public function test_guest_cannot_access_protected_pages():void {
  $this->get('/dashboard')->assertRedirect('/login');
  $this->get('/orders')->assertRedirect('/login');
  $this->get('/owner/reports')->assertRedirect('/login');
 }
 public function test_cashier_cannot_access_owner_only_pages():void {
  $this->actingAs($this->user('cashier'));
  foreach(['/owner/reports','/owner/audit-logs','/owner/services','/owner/cashiers'] as $path){
   $this->get($path)->assertForbidden();
  }
  $this->post('/inventory',['name'=>'Tidak boleh'])->assertForbidden();
  $this->patch('/shifts/123/review')->assertForbidden();
  $this->patch('/orders/123/payment/confirm')->assertForbidden();
 }
 public function test_inactive_owner_cannot_access_owner_pages():void {
  $this->actingAs($this->user('owner',false));
  $this->get('/owner/reports')->assertForbidden();
  $this->get('/owner/audit-logs')->assertForbidden();
 }
 public function test_cashier_role_helpers_are_consistent():void {
  $cashier=$this->user('cashier');$owner=$this->user('owner');
  $this->assertTrue($cashier->isCashier());
  $this->assertFalse($cashier->isOwner());
  $this->assertTrue($owner->isOwner());
  $this->assertFalse($owner->isCashier());
 }
}
