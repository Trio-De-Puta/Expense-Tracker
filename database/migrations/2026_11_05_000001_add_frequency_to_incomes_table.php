<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->string('frequency')->default('one_time');
            $table->unsignedTinyInteger('pay_day')->nullable();
            $table->unsignedTinyInteger('pay_day_2')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'pay_day', 'pay_day_2']);
        });
    }
};
