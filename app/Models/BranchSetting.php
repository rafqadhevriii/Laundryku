<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BranchSetting extends Model {
    protected $fillable = ['branch_id','pickup_delivery_enabled','receipt_print_enabled','receipt_paper_width','default_estimation_hours'];
    protected function casts(): array { return ['pickup_delivery_enabled'=>'boolean','receipt_print_enabled'=>'boolean']; }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
}
