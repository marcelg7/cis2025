<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['sim_number', 'amount_paid_for_device', 'dro_amount']);
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('sim_number')->nullable();
            $table->decimal('amount_paid_for_device', 8, 2)->default(0.00);
            $table->decimal('dro_amount', 8, 2)->nullable();
        });
    }
};