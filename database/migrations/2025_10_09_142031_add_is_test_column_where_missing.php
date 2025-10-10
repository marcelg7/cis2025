<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bell_devices', function (Blueprint $table) {
            $table->boolean('is_test')->default(false)->after('is_active');
        });

        Schema::table('bell_pricing', function (Blueprint $table) {
            $table->boolean('is_test')->default(false)->after('is_current');
        });

        Schema::table('bell_dro_pricing', function (Blueprint $table) {
            $table->boolean('is_test')->default(false)->after('is_current');
        });
    }

    public function down(): void
    {
        Schema::table('bell_devices', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });

        Schema::table('bell_pricing', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });

        Schema::table('bell_dro_pricing', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
    }
};