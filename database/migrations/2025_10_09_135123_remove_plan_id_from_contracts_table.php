<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            DB::table('contracts')->update(['plan_id' => null]);
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->constrained('plans');
        });
    }
};