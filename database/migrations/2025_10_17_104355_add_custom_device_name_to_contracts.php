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
    Schema::table('contracts', function (Blueprint $table) {
        $table->string('custom_device_name')->nullable()->after('bell_device_id'); // For BYOD unlisted devices
    });
}

public function down(): void
{
    Schema::table('contracts', function (Blueprint $table) {
        $table->dropColumn('custom_device_name');
    });
}
};
