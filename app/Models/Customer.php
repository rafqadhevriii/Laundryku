<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Customer extends Model {
 protected $fillable=['business_id','branch_id','name','phone','address','notes'];
 public function business(): BelongsTo { return $this->belongsTo(Business::class); }
 public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
}
