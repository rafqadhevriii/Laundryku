<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Branch extends Model {
    protected $fillable = ['business_id','name','code','phone','address','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function settings(): HasOne { return $this->hasOne(BranchSetting::class); }
}
