<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('dro_status')->default('not_required')->after('financing_csr_initialed_at');
            $table->string('dro_signature_path')->nullable()->after('dro_status');
            $table->string('dro_csr_initials_path')->nullable()->after('dro_signature_path');
            $table->timestamp('dro_signed_at')->nullable()->after('dro_csr_initials_path');
            $table->timestamp('dro_csr_initialed_at')->nullable()->after('dro_signed_at');
            $table->string('dro_pdf_path')->nullable()->after('dro_csr_initialed_at');
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'dro_status',
                'dro_signature_path',
                'dro_csr_initials_path',
                'dro_signed_at',
                'dro_csr_initialed_at',
                'dro_pdf_path'
            ]);
        });
    }
};