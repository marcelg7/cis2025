<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip this migration for SQLite (testing)
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('contracts', function (Blueprint $table) {
            // Clear references first to avoid integrity violations (only if table has data)
            if (DB::table('contracts')->exists()) {
                DB::table('contracts')->update(['shortcode_id' => null]);
            }

            // Drop the foreign key using column name (Laravel auto-generates the constraint name)
            $table->dropForeign(['shortcode_id']);

            // Drop the column
            $table->dropColumn('shortcode_id');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Re-add the column and foreign key (adjust 'after' to match original position, e.g., after 'location')
            $table->foreignId('shortcode_id')->nullable()->after('location')->constrained('shortcodes');
        });
    }
};