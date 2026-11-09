<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CashShift extends Model {
 protected $fillable=['business_id','branch_id','cashier_id','opening_cash','closing_cash','expected_cash','difference','status','opened_at','closed_at','reviewed_by','notes'];
 protected function casts(): array { return ['opening_cash'=>'decimal:2','closing_cash'=>'decimal:2','expected_cash'=>'decimal:2','difference'=>'decimal:2','opened_at'=>'datetime','closed_at'=>'datetime']; }
 public function branch(){return $this->belongsTo(Branch::class);}
 public function cashier(){return $this->belongsTo(User::class,'cashier_id');}
}
