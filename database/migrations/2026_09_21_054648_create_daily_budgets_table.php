<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_budgets', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->date('budget_date')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_budgets');
    }
};
