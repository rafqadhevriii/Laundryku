<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('audit_logs',function(Blueprint $table){
   $table->id();
   $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
   $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
   $table->string('action',80);
   $table->string('subject_type',100);
   $table->unsignedBigInteger('subject_id');
   $table->json('details')->nullable();
   $table->timestamps();
   $table->index(['business_id','created_at']);
  });
 }
 public function down():void {Schema::dropIfExists('audit_logs');}
};
