<?php
namespace App\Http\Controllers;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ServiceController extends Controller {
 public function index(Request $request) {
  $services=Service::where('business_id',$request->user()->business_id)->orderBy('name')->paginate(15);
  return view('services.index',compact('services'));
 }
 public function create() { return view('services.form',['service'=>new Service()]); }
 public function store(Request $request) {
  $data=$this->validated($request);
  $data['business_id']=$request->user()->business_id;
  Service::create($data);
  return redirect()->route('owner.services.index')->with('success','Layanan berhasil ditambahkan.');
 }
 public function edit(Request $request,int $service) {
  $service=$this->scoped($request)->findOrFail($service);
  return view('services.form',compact('service'));
 }
 public function update(Request $request,int $service) {
  $service=$this->scoped($request)->findOrFail($service);
  $service->update($this->validated($request));
  return redirect()->route('owner.services.index')->with('success','Layanan berhasil diperbarui.');
 }
 private function scoped(Request $request) {
  return Service::query()->where('business_id',$request->user()->business_id);
 }
 private function validated(Request $request): array {
  $data=$request->validate([
   'name'=>['required','string','max:150'],
   'unit'=>['required',Rule::in(['kg','item'])],
   'price'=>['required','numeric','min:0','max:9999999999.99','decimal:0,2'],
   'minimum_weight'=>['nullable','numeric','min:0.01','max:999999.99','decimal:0,2'],
   'estimated_hours'=>['required','integer','min:1','max:8760'],
   'description'=>['nullable','string','max:2000'],
   'is_active'=>['required','boolean'],
  ]);
  if($data['unit']==='item') $data['minimum_weight']=null;
  return $data;
 }
}
