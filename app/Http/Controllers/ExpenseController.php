<?php
namespace App\Http\Controllers;
use App\Models\Expense;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ExpenseController extends Controller {
 public function index(Request $request){
  $u=$request->user();
  $expenses=app(\App\Services\ActiveBranchContext::class)->scope(Expense::query(),$request)->with('branch')->latest('expense_date')->paginate(15);
  return view('expenses.index',compact('expenses'));
 }
 public function store(Request $request){
  $u=$request->user();
  $rules=['category'=>['required',Rule::in(['Listrik dan air','Deterjen dan bahan pencuci','Perawatan mesin','Sewa tempat','Operasional lain'])],
   'description'=>['required','string','max:255'],'amount'=>['required','numeric','gt:0','max:999999999999.99','decimal:0,2'],
   'expense_date'=>['required','date','before_or_equal:today']];
  $data=$request->validate($rules);
  $data['business_id']=$u->business_id;
  $data['created_by']=$u->id;
  if($u->isCashier()){$data['branch_id']=$u->branch_id;abort_unless($u->branch?->is_active,403);}
  DB::transaction(function()use($data,$u,$request){
   $data['branch_id']=app(\App\Services\ActiveBranchContext::class)->requireBranch($request,true)->id;
   $expense=Expense::create($data);
   AuditLog::create(['business_id'=>$u->business_id,'user_id'=>$u->id,'action'=>'expense.created','subject_type'=>'expense','subject_id'=>$expense->id,'details'=>['branch_id'=>$expense->branch_id,'category'=>$expense->category,'amount'=>$expense->amount]]);
  });
  return back()->with('success','Pengeluaran berhasil dicatat.');
 }
}
