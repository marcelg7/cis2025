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
        Schema::table('contracts', function (Blueprint $table) {
            // Add device_name field to store the device name at time of contract creation
            // This preserves historical data even if device is later removed or renamed
            $table->string('device_name', 255)->nullable()->after('bell_device_id');
        });

        // Populate device_name for existing contracts that have a bell_device_id
        DB::statement('
            UPDATE contracts c
            INNER JOIN bell_devices bd ON c.bell_device_id = bd.id
            SET c.device_name = bd.name
            WHERE c.bell_device_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('device_name');
        });
    }
};
