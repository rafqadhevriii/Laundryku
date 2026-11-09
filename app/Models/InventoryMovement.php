<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryMovement extends Model {
 protected $fillable=['inventory_item_id','created_by','reviewed_by','type','status','quantity','before_quantity','after_quantity','notes'];
 protected function casts():array{return ['quantity'=>'decimal:2','before_quantity'=>'decimal:2','after_quantity'=>'decimal:2'];}
 public function item(){return $this->belongsTo(InventoryItem::class,'inventory_item_id');}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
}
