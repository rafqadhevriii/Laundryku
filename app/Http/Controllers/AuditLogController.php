<?php
namespace App\Http\Controllers;
use App\Models\AuditLog;
use Illuminate\Http\Request;
class AuditLogController extends Controller {
 public function index(Request $request){
  abort_unless($request->user()->isOwner(),403);
  $logs=AuditLog::where('business_id',$request->user()->business_id)
   ->with('user')->latest()->paginate(25);
  return view('audit.index',compact('logs'));
 }
}
