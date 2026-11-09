<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('payments',function(Blueprint $table){
   $table->id();
   $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
   $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
   $table->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
   $table->enum('method',['cash','transfer','qris']);
   $table->enum('status',['pending','paid','rejected'])->default('pending');
   $table->decimal('amount',14,2);
   $table->string('reference',120)->nullable();
   $table->timestamp('paid_at')->nullable();
   $table->text('notes')->nullable();
   $table->timestamps();
   $table->index(['status','paid_at']);
  });
 }
 public function down(): void { Schema::dropIfExists('payments'); }
};
