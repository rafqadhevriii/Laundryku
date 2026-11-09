<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected $fillable = ['business_id','branch_id','name','username','email','password','role','is_active'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array {
        return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];
    }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isCashier(): bool { return $this->role === 'cashier'; }
}
