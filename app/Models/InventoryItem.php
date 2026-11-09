<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryItem extends Model {
 protected $fillable=['business_id','branch_id','name','category','unit','quantity','minimum_quantity'];
 protected function casts():array{return ['quantity'=>'decimal:2','minimum_quantity'=>'decimal:2'];}
 public function branch(){return $this->belongsTo(Branch::class);}
 public function movements(){return $this->hasMany(InventoryMovement::class);}
}
