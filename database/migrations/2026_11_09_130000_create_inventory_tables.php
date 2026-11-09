<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('inventory_items',function(Blueprint $table){
   $table->id();$table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
   $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
   $table->string('name',150);$table->string('category',80);$table->string('unit',30);
   $table->decimal('quantity',12,2)->default(0);$table->decimal('minimum_quantity',12,2)->default(0);
   $table->timestamps();$table->index(['business_id','branch_id','category']);
  });
  Schema::create('inventory_movements',function(Blueprint $table){
   $table->id();$table->foreignId('inventory_item_id')->constrained('inventory_items')->restrictOnDelete();
   $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
   $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
   $table->enum('type',['in','out','adjustment']);
   $table->enum('status',['approved','pending','rejected'])->default('approved');
   $table->decimal('quantity',12,2);$table->decimal('before_quantity',12,2)->nullable();
   $table->decimal('after_quantity',12,2)->nullable();
   $table->string('notes',255)->nullable();$table->timestamps();
   $table->index(['status','type']);
  });
 }
 public function down(): void {Schema::dropIfExists('inventory_movements');Schema::dropIfExists('inventory_items');}
};
