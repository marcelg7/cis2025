<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            // Rename columns to be more generic
            $table->renameColumn('hay_credit_eligible', 'credit_eligible');
            $table->renameColumn('hay_credit_amount', 'credit_amount');
            
            // Add column to store credit type/name
            $table->string('credit_type', 100)->nullable()->after('credit_amount');
        });
    }

    public function down(): void
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            $table->dropColumn('credit_type');
            $table->renameColumn('credit_eligible', 'hay_credit_eligible');
            $table->renameColumn('credit_amount', 'hay_credit_amount');
        });
    }
};