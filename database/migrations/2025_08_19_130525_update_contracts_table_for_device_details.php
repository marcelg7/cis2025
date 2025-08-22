<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Make device_id nullable
            $table->unsignedBigInteger('device_id')->nullable()->change();
            // Add device detail columns
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('version')->nullable();
            $table->string('device_storage')->nullable();
            $table->string('extra_info')->nullable();
            $table->decimal('device_price', 8, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Revert device_id to not nullable (adjust based on original schema)
            $table->unsignedBigInteger('device_id')->nullable(false)->change();
            // Drop added columns
            $table->dropColumn(['manufacturer', 'model', 'version', 'device_storage', 'extra_info', 'device_price']);
        });
    }
};
