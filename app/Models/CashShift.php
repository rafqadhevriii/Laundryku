<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CashShift extends Model {
 protected $fillable=['business_id','branch_id','cashier_id','opening_cash','closing_cash','expected_cash','difference','status','opened_at','closed_at','reviewed_by','notes'];
 protected function casts(): array { return ['opening_cash'=>'decimal:2','closing_cash'=>'decimal:2','expected_cash'=>'decimal:2','difference'=>'decimal:2','opened_at'=>'datetime','closed_at'=>'datetime']; }
 public function payments(){return $this->hasMany(Payment::class);}
 // Same receipt criteria for the live display and final close; attribution stays by shift ID.
 public function paidCashPayments(){return $this->payments()->where('method','cash')->where('status','paid');}
 public function unreviewedHistoricalCash(){
  return Payment::whereNull('cash_shift_id')->where('method','cash')->where('status','paid')
   ->whereNull('reconciliation_status')->whereBetween('paid_at',[$this->opened_at,now()])
   ->whereHas('order',fn($q)=>$q->where('business_id',$this->business_id)->where('branch_id',$this->branch_id));
 }
 public function branch(){return $this->belongsTo(Branch::class);}
 public function cashier(){return $this->belongsTo(User::class,'cashier_id');}
}
