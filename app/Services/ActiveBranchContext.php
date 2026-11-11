<?php
namespace App\Services;

use App\Models\{Branch,User};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ActiveBranchContext {
 public const SESSION_ROOT='active_branch_context';
 private const ATTRIBUTE='resolved_active_branch_context';
 public const RETURN_ROUTES=['dashboard','orders.index','orders.create','customers.index','customers.create','inventory.index','expenses.index','shifts.index','owner.reports.index','owner.operational.index','owner.reconciliation.index'];
 public function sessionKey(User $user):string {
  return self::SESSION_ROOT.'.'.$user->id.'.'.$user->business_id;
 }
 public function reset(Request $request):void {$request->session()->forget(self::SESSION_ROOT);$request->attributes->remove(self::ATTRIBUTE);}
 public function resolve(Request $request):array {
  if($request->attributes->has(self::ATTRIBUTE))return $request->attributes->get(self::ATTRIBUTE);
  $user=$request->user()?->fresh();
  abort_unless($user?->is_active && $user->business?->is_active && ($user->isOwner()||$user->isCashier()),403);
  $branch=null;$invalid=false;
  if($user->isCashier()){
   // Assignment is authoritative; owner/session/query/form choices never apply.
   $branch=Branch::where('business_id',$user->business_id)->where('is_active',true)->find($user->branch_id);
   abort_unless($branch,403,'Cabang akun Kasir tidak aktif atau tidak valid.');
  }else{
   $key=$this->sessionKey($user);
   if($request->session()->exists($key)){
    $saved=$request->session()->get($key);
    if($saved!=='all'){
     if((is_int($saved)||is_string($saved)) && preg_match('/^[1-9][0-9]*$/',(string)$saved)){
      $branch=Branch::where('business_id',$user->business_id)->where('is_active',true)->find($saved);
     }
     if(!$branch){$request->session()->forget($key);$invalid=true;}
    }
   }
  }
  $resolved=['user'=>$user,'branch'=>$branch,'id'=>$branch?->id,'invalid'=>$invalid,'label'=>$branch?->name??'Semua Cabang'];
  $request->attributes->set(self::ATTRIBUTE,$resolved);
  return $resolved;
 }
 public function id(Request $request):?int {return $this->resolve($request)['id'];}
 public function label(Request $request):string {return $this->resolve($request)['label'];}
 public function scope(Builder $query,Request $request,string $branchColumn='branch_id',string $businessColumn='business_id'):Builder {
  $context=$this->resolve($request);
  return $query->where($businessColumn,$context['user']->business_id)->when($context['id']!==null,fn($q)=>$q->where($branchColumn,$context['id']));
 }
 public function requireBranch(Request $request,bool $lock=false):Branch {
  $context=$this->resolve($request);
  if($context['id']===null)throw ValidationException::withMessages(['active_branch'=>'Pilih lokasi kerja melalui menu akun sebelum menyimpan data baru.']);
  $query=Branch::where('business_id',$context['user']->business_id)->where('is_active',true)->whereKey($context['id']);
  if($lock)$query->lockForUpdate();
  $branch=$query->first();
  if(!$branch){
   $request->session()->forget($this->sessionKey($context['user']));
   $request->attributes->remove(self::ATTRIBUTE);
   throw ValidationException::withMessages(['active_branch'=>'Cabang aktif tidak lagi valid. Pilih ulang melalui menu akun dan ulangi tindakan.']);
  }
  return $branch;
 }
 public function select(Request $request,mixed $value):void {
  $user=$this->resolve($request)['user'];abort_unless($user->isOwner(),403);
  $valid=(is_string($value)||is_int($value)) && ($value==='all'||preg_match('/^[1-9][0-9]*$/',(string)$value));
  if(!$valid)throw ValidationException::withMessages(['active_branch'=>'Pilih Semua Cabang atau satu cabang aktif milik usaha Anda.']);
  $branch=$value==='all'?null:Branch::where('business_id',$user->business_id)->where('is_active',true)->find($value);
  if($value!=='all' && !$branch)throw ValidationException::withMessages(['active_branch'=>'Pilih cabang aktif milik usaha Anda.']);
  $request->session()->put($this->sessionKey($user),$branch?->id??'all');
  // Never carry a customer, form error, or old POST from the previous branch.
  $request->session()->forget(['_old_input','errors']);
  $request->attributes->remove(self::ATTRIBUTE);
 }
 public function reportBranch(Request $request,mixed $requested=null):?int {
  $active=$this->id($request);
  if($active!==null){
   if($requested!==null && $requested!=='' && (int)$requested!==$active)throw ValidationException::withMessages(['branch_id'=>'Filter laporan berbeda dari cabang aktif. Ubah lokasi kerja melalui menu akun atau gunakan Semua Cabang.']);
   return $active;
  }
  return $requested===null||$requested===''?null:(int)$requested;
 }
 public function returnRoute(mixed $name):string {return is_string($name)&&in_array($name,self::RETURN_ROUTES,true)?$name:'dashboard';}
}
