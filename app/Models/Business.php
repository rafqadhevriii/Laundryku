<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Business extends Model {
    protected $fillable = ['name','code','phone','address','logo_path','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function branches(): HasMany { return $this->hasMany(Branch::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function settings(): HasOne { return $this->hasOne(BusinessSetting::class); }
}
