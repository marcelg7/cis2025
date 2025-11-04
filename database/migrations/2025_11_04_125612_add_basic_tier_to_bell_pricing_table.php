<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL requires ALTER to change ENUM values
        DB::statement("ALTER TABLE bell_pricing MODIFY COLUMN tier ENUM('Ultra', 'Max', 'Select', 'Lite', 'Basic') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'Basic' from enum (if there are any Basic records, this will fail)
        DB::statement("ALTER TABLE bell_pricing MODIFY COLUMN tier ENUM('Ultra', 'Max', 'Select', 'Lite') NOT NULL");
    }
};
