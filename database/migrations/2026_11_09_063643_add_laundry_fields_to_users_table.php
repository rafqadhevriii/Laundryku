<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->after('business_id');
            $table->string('username', 50)->nullable()->unique()->after('name');
            $table->enum('role', ['owner', 'cashier'])->default('cashier')->after('username');
            $table->boolean('is_active')->default(true)->after('role');
            $table->foreign(['branch_id', 'business_id'])->references(['id', 'business_id'])->on('branches')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id', 'business_id']);
            $table->dropForeign(['business_id']);
            $table->dropUnique(['username']);
            $table->dropColumn(['business_id', 'branch_id', 'username', 'role', 'is_active']);
        });
    }
};
