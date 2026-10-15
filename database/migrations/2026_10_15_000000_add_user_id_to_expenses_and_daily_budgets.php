<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        Schema::table('daily_budgets', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        Schema::table('daily_budgets', function (Blueprint $table) {
            $table->dropUnique(['budget_date']);
            $table->unique(['user_id', 'budget_date']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_budgets', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'budget_date']);
            $table->unique(['budget_date']);
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
