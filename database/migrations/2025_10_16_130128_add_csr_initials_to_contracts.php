<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('financing_csr_initials_path')->nullable()->after('financing_signature_path');
            $table->timestamp('financing_csr_initialed_at')->nullable()->after('financing_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['financing_csr_initials_path', 'financing_csr_initialed_at']);
        });
    }
};