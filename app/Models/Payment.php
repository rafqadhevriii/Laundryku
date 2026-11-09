<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model {
 protected $fillable=['order_id','recorded_by','confirmed_by','method','status','amount','reference','paid_at','notes'];
 protected function casts(): array { return ['amount'=>'decimal:2','paid_at'=>'datetime']; }
 public function order(): BelongsTo { return $this->belongsTo(Order::class); }
 public function recorder(): BelongsTo { return $this->belongsTo(User::class,'recorded_by'); }
 public function confirmer(): BelongsTo { return $this->belongsTo(User::class,'confirmed_by'); }
}
