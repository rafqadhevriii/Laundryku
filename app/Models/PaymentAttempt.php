<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model {
 protected $fillable=['payment_id','attempt_number','recorded_by','confirmed_by','method','status','amount','reference','notes','rejection_reason','submitted_at','decided_at','paid_at'];
 protected function casts():array {
  return ['amount'=>'decimal:2','submitted_at'=>'datetime','decided_at'=>'datetime','paid_at'=>'datetime'];
 }
 public function payment(){return $this->belongsTo(Payment::class);}
 public function recorder(){return $this->belongsTo(User::class,'recorded_by');}
 public function confirmer(){return $this->belongsTo(User::class,'confirmed_by');}
}
