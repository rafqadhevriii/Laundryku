<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BusinessSetting extends Model {
    protected $fillable = ['business_id','timezone','currency','tax_enabled','tax_rate'];
    protected function casts(): array { return ['tax_enabled'=>'boolean','tax_rate'=>'decimal:2']; }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
}
