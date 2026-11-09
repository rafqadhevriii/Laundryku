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
  $expenses=Expense::where('business_id',$u->business_id)->when($u->isCashier(),fn($q)=>$q->where('branch_id',$u->branch_id))->with('branch')->latest('expense_date')->paginate(15);
  $branches=$u->isOwner()?$u->business->branches()->where('is_active',true)->get():collect();
  return view('expenses.index',compact('expenses','branches'));
 }
 public function store(Request $request){
  $u=$request->user();
  $rules=['category'=>['required',Rule::in(['Listrik dan air','Deterjen dan bahan pencuci','Perawatan mesin','Sewa tempat','Operasional lain'])],
   'description'=>['required','string','max:255'],'amount'=>['required','numeric','gt:0','max:999999999999.99','decimal:0,2'],
   'expense_date'=>['required','date','before_or_equal:today']];
  if($u->isOwner())$rules['branch_id']=['required',Rule::exists('branches','id')->where('business_id',$u->business_id)->where('is_active',true)];
  $data=$request->validate($rules);
  $data['business_id']=$u->business_id;
  $data['created_by']=$u->id;
  if($u->isCashier()){$data['branch_id']=$u->branch_id;abort_unless($u->branch?->is_active,403);}
  DB::transaction(function()use($data,$u){
   $expense=Expense::create($data);
   AuditLog::create(['business_id'=>$u->business_id,'user_id'=>$u->id,'action'=>'expense.created','subject_type'=>'expense','subject_id'=>$expense->id,'details'=>['branch_id'=>$expense->branch_id,'category'=>$expense->category,'amount'=>$expense->amount]]);
  });
  return back()->with('success','Pengeluaran berhasil dicatat.');
 }
}
