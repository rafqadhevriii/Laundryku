<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('customers',function(Blueprint $table){
   $table->id();
   $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
   $table->foreignId('branch_id');
   $table->string('name',150);
   $table->string('phone',20)->nullable();
   $table->text('address')->nullable();
   $table->text('notes')->nullable();
   $table->timestamps();
   $table->foreign(['branch_id','business_id'])->references(['id','business_id'])->on('branches')->restrictOnDelete();
   $table->index(['business_id','branch_id','name']);
   $table->index(['business_id','branch_id','phone']);
  });
 }
 public function down(): void { Schema::dropIfExists('customers'); }
};
