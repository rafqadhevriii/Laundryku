<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('cash_shifts',function(Blueprint $table){
   $table->id();
   $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
   $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
   $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
   $table->decimal('opening_cash',14,2);
   $table->decimal('closing_cash',14,2)->nullable();
   $table->decimal('expected_cash',14,2)->nullable();
   $table->decimal('difference',14,2)->nullable();
   $table->enum('status',['open','submitted','reviewed'])->default('open');
   $table->timestamp('opened_at');
   $table->timestamp('closed_at')->nullable();
   $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
   $table->text('notes')->nullable();
   $table->timestamps();
   $table->index(['business_id','branch_id','status']);
  });
  Schema::create('expenses',function(Blueprint $table){
   $table->id();
   $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
   $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
   $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
   $table->string('category',80);
   $table->string('description',255);
   $table->decimal('amount',14,2);
   $table->date('expense_date');
   $table->timestamps();
   $table->index(['business_id','branch_id','expense_date']);
  });
 }
 public function down(): void { Schema::dropIfExists('expenses');Schema::dropIfExists('cash_shifts'); }
};
