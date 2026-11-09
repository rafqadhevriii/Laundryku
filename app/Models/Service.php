<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Service extends Model {
 protected $fillable=['business_id','name','unit','price','minimum_weight','estimated_hours','description','is_active'];
 protected function casts(): array { return ['price'=>'decimal:2','minimum_weight'=>'decimal:2','is_active'=>'boolean']; }
 public function business(): BelongsTo { return $this->belongsTo(Business::class); }
}
