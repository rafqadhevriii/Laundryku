<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  Schema::table('payments',function(Blueprint $table){
   // NULL berarti belum ditinjau. Tidak ada backfill/atribusi otomatis.
   $table->enum('reconciliation_status',['assigned','unallocated'])->nullable();
   $table->foreignId('reconciled_by')->nullable()->constrained('users')->restrictOnDelete();
   $table->timestamp('reconciled_at')->nullable();
   $table->text('reconciliation_reason')->nullable();
  });
  Schema::create('payment_attempts',function(Blueprint $table){
   $table->id();
   $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
   $table->unsignedInteger('attempt_number');
   $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
   $table->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
   $table->string('method',20);
   $table->string('status',20);
   $table->decimal('amount',14,2);
   $table->string('reference',120)->nullable();
   $table->text('notes')->nullable();
   $table->text('rejection_reason')->nullable();
   $table->timestamp('submitted_at')->nullable();
   $table->timestamp('decided_at')->nullable();
   $table->timestamp('paid_at')->nullable();
   $table->timestamps();
   $table->unique(['payment_id','attempt_number']);
  });
 }
 public function down(): void {
  Schema::dropIfExists('payment_attempts');
  Schema::table('payments',function(Blueprint $table){
   $table->dropConstrainedForeignId('reconciled_by');
   $table->dropColumn(['reconciliation_status','reconciled_at','reconciliation_reason']);
  });
 }
};
