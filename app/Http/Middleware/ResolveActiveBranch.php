<?php
namespace App\Http\Middleware;

use App\Services\ActiveBranchContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,View};
use Illuminate\Validation\ValidationException;

class ResolveActiveBranch {
 public function __construct(private ActiveBranchContext $context){}
 public function handle(Request $request,Closure $next){
  if($request->routeIs('logout'))return $next($request);
  $resolved=$this->context->resolve($request);
  Auth::guard()->setUser($resolved['user']);
  $request->setUserResolver(fn()=>$resolved['user']);
  if($resolved['invalid']){
   $message='Cabang aktif sebelumnya tidak lagi valid. Konteks dikembalikan ke Semua Cabang; pilih ulang sebelum menyimpan.';
   if(!$request->isMethodSafe() && !$request->routeIs('active-branch.update'))throw ValidationException::withMessages(['active_branch'=>$message]);
   $request->session()->now('branch_context_warning',$message);
  }
  View::share([
   'activeBranchId'=>$resolved['id'],'activeBranch'=>$resolved['branch'],'activeBranchLabel'=>$resolved['label'],
   'switchableBranches'=>$resolved['user']->isOwner()?$resolved['user']->business->branches()->where('is_active',true)->orderBy('name')->get():collect(),
   'branchReturnRoute'=>$this->context->returnRoute($request->route()?->getName()),
  ]);
  return $next($request);
 }
}
