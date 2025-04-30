<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToDevicesTable extends Migration {
    public function up(): void {
        Schema::table('devices', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('image');
        });
    }

    public function down(): void {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
