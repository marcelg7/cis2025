<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_pricings', function (Blueprint $table) {
            // Make column nullable if not already
            $table->unsignedBigInteger('device_id')->nullable()->change();
        });

        // Now update to NULL
        DB::table('device_pricings')->update(['device_id' => null]);

        Schema::table('device_pricings', function (Blueprint $table) {
            $table->dropForeign('device_pricings_device_id_foreign');
            $table->dropColumn('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('device_pricings', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->constrained('devices');
        });
    }
};