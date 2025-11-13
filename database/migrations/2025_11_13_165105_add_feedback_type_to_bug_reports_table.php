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
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->enum('feedback_type', ['bug', 'feature', 'change', 'general'])
                ->default('bug')
                ->after('severity');
        });

        // Update existing records to have feedback_type = 'bug' (already default)
        // This ensures all existing bug reports are properly categorized
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->dropColumn('feedback_type');
        });
    }
};
