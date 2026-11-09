<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
 public function showLogin() { return view('auth.login'); }
 public function login(Request $request) {
  $data=$request->validate(['username'=>['required','string'],'password'=>['required','string']]);
  $key=mb_strtolower($data['username']).'|'.$request->ip();
  if(RateLimiter::tooManyAttempts($key,5)) throw ValidationException::withMessages(['username'=>'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.']);
  if(!Auth::attempt(['username'=>$data['username'],'password'=>$data['password'],'is_active'=>true])){
   RateLimiter::hit($key,60);
   throw ValidationException::withMessages(['username'=>'Username atau password salah.']);
  }
  $user=Auth::user();
  if(!$user->business?->is_active || ($user->isCashier() && (!$user->branch?->is_active || $user->branch->business_id !== $user->business_id))){
   Auth::logout(); throw ValidationException::withMessages(['username'=>'Akun atau cabang tidak aktif.']);
  }
  app(\App\Services\ActiveBranchContext::class)->reset($request);
  RateLimiter::clear($key);
  $request->session()->regenerate();
  return redirect()->intended(route('dashboard'));
 }
 public function logout(Request $request) {
  app(\App\Services\ActiveBranchContext::class)->reset($request);
  Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
  return redirect()->route('login');
 }
}