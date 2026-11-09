<?php
namespace App\Http\Controllers;

use App\Services\ActiveBranchContext;
use Illuminate\Http\Request;

class ActiveBranchController extends Controller {
 public function update(Request $request,ActiveBranchContext $context){
  $context->select($request,$request->input('active_branch'));
  return redirect()->route($context->returnRoute($request->input('return_route')))->with('success','Konteks cabang aktif diperbarui.');
 }
}
