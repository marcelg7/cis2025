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
            if (DB::table('contracts')->exists()) {
                DB::table('contracts')->update(['device_id' => null]); // Clear references
            }
            $table->dropForeign(['device_id']); // Laravel auto-generates name 'contracts_device_id_foreign'
            $table->dropColumn('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->constrained('devices')->after('bell_plan_plus_device');
        });
    }
};