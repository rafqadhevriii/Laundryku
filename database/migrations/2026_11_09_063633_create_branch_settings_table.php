<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->unique()->constrained('branches')->cascadeOnDelete();
            $table->boolean('pickup_delivery_enabled')->default(false);
            $table->boolean('receipt_print_enabled')->default(true);
            $table->unsignedTinyInteger('receipt_paper_width')->default(80);
            $table->unsignedInteger('default_estimation_hours')->default(48);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_settings');
    }
};
