<?php
namespace App\Http\Controllers;
use App\Models\Business;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
class SetupController extends Controller {
 public function create() { abort_if(User::exists(),404); return view('auth.setup'); }
 public function store(Request $request) {
  abort_if(User::exists(),404);
  $data=$request->validate([
   'business_name'=>['required','string','max:150'],
   'owner_name'=>['required','string','max:255'],
   'username'=>['required','alpha_dash','min:3','max:50','unique:users,username'],
   'email'=>['required','email','max:255','unique:users,email'],
   'password'=>['required','confirmed',Password::min(8)],
  ]);
  $owner=DB::transaction(function() use($data) {
   if(User::exists()) abort(409,'Aplikasi sudah memiliki pengguna.');
   $business=Business::create(['name'=>$data['business_name'],'code'=>'LY'.strtoupper(bin2hex(random_bytes(4)))]);
   $business->settings()->create([]);
   $branch=Branch::create(['business_id'=>$business->id,'name'=>'Cabang Utama','code'=>'UTM']);
   $branch->settings()->create([]);
   return User::create(['business_id'=>$business->id,'branch_id'=>null,'name'=>$data['owner_name'],
    'username'=>$data['username'],'email'=>$data['email'],'password'=>$data['password'],'role'=>'owner','is_active'=>true]);
  });
  app(\App\Services\ActiveBranchContext::class)->reset($request);
  Auth::login($owner); $request->session()->regenerate();
  return redirect()->route('dashboard')->with('success','Usaha Laundryku berhasil disiapkan.');
 }
}