<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class CashierController extends Controller {
 public function index(Request $request) {
  $business=$request->user()->business;
  return view('owner.cashiers',['cashiers'=>$business->users()->where('role','cashier')->with('branch')->latest()->get(),
   'branches'=>$business->branches()->where('is_active',true)->orderBy('name')->get()]);
 }
 public function store(Request $request) {
  $business=$request->user()->business;
  $data=$request->validate([
   'name'=>['required','string','max:255'],
   'username'=>['required','alpha_dash','min:3','max:50','unique:users,username'],
   'email'=>['required','email','max:255','unique:users,email'],
   'branch_id'=>['required',Rule::exists('branches','id')->where('business_id',$business->id)->where('is_active',true)],
   'password'=>['required','confirmed',Password::min(8)],
  ]);
  $business->users()->create([...$data,'role'=>'cashier','is_active'=>true]);
  return redirect()->route('owner.cashiers.index')->with('success','Akun kasir berhasil dibuat.');
 }
}