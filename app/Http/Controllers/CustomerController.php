<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CustomerController extends Controller {
 private function scoped(Request $request) {
  $user=$request->user();
  $query=Customer::query()->where('business_id',$user->business_id);
  if($user->isCashier()) $query->where('branch_id',$user->branch_id);
  return $query;
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
  return view('customers.form',['customer'=>new Customer(),'branches'=>$request->user()->isOwner()?$request->user()->business->branches()->where('is_active',true)->get():collect()]);
 }
 public function store(Request $request) {
  $user=$request->user();
  $data=$this->validated($request);
  $data['business_id']=$user->business_id;
  if($user->isCashier()) $data['branch_id']=$user->branch_id;
  Customer::create($data);
  return redirect()->route('customers.index')->with('success','Pelanggan berhasil ditambahkan.');
 }
 public function edit(Request $request, int $customer) {
  $customer=$this->scoped($request)->findOrFail($customer);
  return view('customers.form',['customer'=>$customer,'branches'=>$request->user()->isOwner()?$request->user()->business->branches()->where('is_active',true)->get():collect()]);
 }
 public function update(Request $request,int $customer) {
  $customer=$this->scoped($request)->findOrFail($customer);
  $data=$this->validated($request);
  if($request->user()->isCashier()) unset($data['branch_id']);
  $customer->update($data);
  return redirect()->route('customers.index')->with('success','Pelanggan berhasil diperbarui.');
 }
 private function validated(Request $request): array {
  $user=$request->user();
  $rules=[
   'name'=>['required','string','max:150'],
   'phone'=>['nullable','string','max:20','regex:/^\\+?[0-9][0-9\\s-]*$/'],
   'address'=>['nullable','string','max:2000'],
   'notes'=>['nullable','string','max:2000'],
  ];
  if($user->isOwner()) $rules['branch_id']=['required',Rule::exists('branches','id')->where('business_id',$user->business_id)->where('is_active',true)];
  return $request->validate($rules);
 }
}
