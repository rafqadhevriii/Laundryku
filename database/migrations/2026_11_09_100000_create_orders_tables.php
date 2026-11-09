<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('orders',function(Blueprint $table){
   $table->id();
   $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
   $table->foreignId('branch_id');
   $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
   $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
   $table->string('order_number',65)->unique();
   $table->enum('status',['received','processing','ready','picked_up','cancelled'])->default('received');
   $table->decimal('subtotal',14,2)->default(0);
   $table->decimal('total',14,2)->default(0);
   $table->timestamp('estimated_at')->nullable();
   $table->timestamp('picked_up_at')->nullable();
   $table->text('notes')->nullable();
   $table->timestamps();
   $table->foreign(['branch_id','business_id'])->references(['id','business_id'])->on('branches')->restrictOnDelete();
   $table->index(['business_id','branch_id','status']);
  });
  Schema::create('order_items',function(Blueprint $table){
   $table->id();
   $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
   $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
   $table->string('service_name',150);
   $table->enum('unit',['kg','item']);
   $table->decimal('quantity',10,2);
   $table->decimal('unit_price',12,2);
   $table->decimal('subtotal',14,2);
   $table->timestamps();
  });
 }
 public function down(): void {
  Schema::dropIfExists('order_items');
  Schema::dropIfExists('orders');
 }
};
