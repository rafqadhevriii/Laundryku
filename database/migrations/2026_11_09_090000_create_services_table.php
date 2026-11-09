<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('services',function(Blueprint $table){
   $table->id();
   $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
   $table->string('name',150);
   $table->enum('unit',['kg','item']);
   $table->decimal('price',12,2);
   $table->decimal('minimum_weight',8,2)->nullable();
   $table->unsignedInteger('estimated_hours')->default(48);
   $table->text('description')->nullable();
   $table->boolean('is_active')->default(true);
   $table->timestamps();
   $table->index(['business_id','is_active']);
  });
 }
 public function down(): void { Schema::dropIfExists('services'); }
};
