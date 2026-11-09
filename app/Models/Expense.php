<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Expense extends Model {
 protected $fillable=['business_id','branch_id','created_by','category','description','amount','expense_date'];
 protected function casts(): array {return ['amount'=>'decimal:2','expense_date'=>'date'];}
 public function branch(){return $this->belongsTo(Branch::class);}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
}
