<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            // Add credit duration (how many months the credit applies for)
            $table->integer('credit_duration')->nullable()->after('credit_type');
            // Add credit when applicable (conditional text for when credit applies)
            $table->text('credit_when_applicable')->nullable()->after('credit_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            $table->dropColumn(['credit_duration', 'credit_when_applicable']);
        });
    }
};
