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
        // Add 'Basic' to the tier ENUM to support Basic rate plans
        DB::statement("ALTER TABLE `rate_plans` MODIFY COLUMN `tier` ENUM('Basic','Lite','Select','Max','Ultra') DEFAULT NULL COMMENT 'Plan tier'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'Basic' from the tier ENUM (only if no plans are using it)
        DB::statement("ALTER TABLE `rate_plans` MODIFY COLUMN `tier` ENUM('Lite','Select','Max','Ultra') DEFAULT NULL COMMENT 'Plan tier'");
    }
};
