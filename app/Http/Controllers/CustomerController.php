<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Http\Request;
class CustomerController extends Controller {
 private function scoped(Request $request) {
  return app(\App\Services\ActiveBranchContext::class)->scope(Customer::query(),$request);
 }
 public function index(Request $request) {
  $query=$this->scoped($request)->with('branch');
  if($request->filled('search')) {
   $search=trim($request->input('search'));
   $query->where(function($q)use($search){$q->where('name','like','%'.$search.'%')->orWhere('phone','like','%'.$search.'%');});
  }
  return view('customers.index',['customers'=>$query->latest()->paginate(15)->withQueryString()]);
 }
 public function create(Request $request) {
  return view('customers.form',['customer'=>new Customer()]);
 }
 public function store(Request $request) {
  $user=$request->user();
  $data=$this->validated($request);
  $data['business_id']=$user->business_id;
  \Illuminate\Support\Facades\DB::transaction(function()use($request,$data){
   $data['branch_id']=app(\App\Services\ActiveBranchContext::class)->requireBranch($request,true)->id;
   Customer::create($data);
  });
  return redirect()->route('customers.index')->with('success','Pelanggan berhasil ditambahkan.');
 }
 public function edit(Request $request, int $customer) {
  $customer=$this->scoped($request)->findOrFail($customer);
  return view('customers.form',['customer'=>$customer]);
 }
 public function update(Request $request,int $customer) {
  $customer=$this->scoped($request)->findOrFail($customer);
  $data=$this->validated($request);
  if($request->user()->isCashier()) unset($data['branch_id']);
  $customer->update($data);
  return redirect()->route('customers.index')->with('success','Pelanggan berhasil diperbarui.');
 }
 private function validated(Request $request): array {
  $rules=[
   'name'=>['required','string','max:150'],
   'phone'=>['nullable','string','max:20','regex:/^\\+?[0-9][0-9\\s-]*$/'],
   'address'=>['nullable','string','max:2000'],
   'notes'=>['nullable','string','max:2000'],
  ];
  return $request->validate($rules);
 }
}
