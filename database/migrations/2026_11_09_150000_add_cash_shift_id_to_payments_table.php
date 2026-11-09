<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('payments',function(Blueprint $table){
   // Nullable untuk mempertahankan pembayaran historis tanpa menebak shift.
   $table->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts')->restrictOnDelete();
  });
 }
 public function down(): void {
  Schema::table('payments',function(Blueprint $table){
   $table->dropConstrainedForeignId('cash_shift_id');
  });
 }
};
