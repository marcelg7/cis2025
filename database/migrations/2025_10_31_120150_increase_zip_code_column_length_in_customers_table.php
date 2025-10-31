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
        Schema::table('customers', function (Blueprint $table) {
            // Increase zip_code column from 7 to 15 characters to accommodate full postal codes
            // Canadian postal codes with space: "A1A 1A1" = 7 chars
            // US ZIP+4 with dash: "12345-6789" = 10 chars
            // Giving extra room for any variations
            $table->string('zip_code', 15)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revert back to original 7 character limit
            $table->string('zip_code', 7)->nullable()->change();
        });
    }
};
