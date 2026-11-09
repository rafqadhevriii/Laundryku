<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Order extends Model {
 protected $fillable=['business_id','branch_id','customer_id','created_by','order_number','status','subtotal','total','estimated_at','picked_up_at','notes'];
 protected function casts(): array { return ['subtotal'=>'decimal:2','total'=>'decimal:2','estimated_at'=>'datetime','picked_up_at'=>'datetime']; }
 public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
 public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
 public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
 public function items(): HasMany { return $this->hasMany(OrderItem::class); }
 public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(Payment::class); }
}
