<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('shortcode_id')->nullable()->after('location');
            $table->foreign('shortcode_id')->references('id')->on('shortcodes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['shortcode_id']);
            $table->dropColumn('shortcode_id');
        });
    }
};
