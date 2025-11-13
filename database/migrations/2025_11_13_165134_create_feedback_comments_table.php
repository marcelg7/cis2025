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
        Schema::create('feedback_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bug_report_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->timestamps();
        });

        // Migrate existing admin_notes to comments
        DB::statement("
            INSERT INTO feedback_comments (bug_report_id, user_id, comment, created_at, updated_at)
            SELECT id, assigned_to, admin_notes, updated_at, updated_at
            FROM bug_reports
            WHERE admin_notes IS NOT NULL AND admin_notes != '' AND assigned_to IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_comments');
    }
};
