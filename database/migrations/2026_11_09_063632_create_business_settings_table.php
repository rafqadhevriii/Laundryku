<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained('businesses')->cascadeOnDelete();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->string('currency', 3)->default('IDR');
            $table->boolean('tax_enabled')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
