<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model {
 protected $fillable=['business_id','user_id','action','subject_type','subject_id','details'];
 protected function casts():array{return ['details'=>'array'];}
 public function user(){return $this->belongsTo(User::class);}
}
