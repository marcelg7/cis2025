<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration {
    public function up(): void {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('activity_type_id')->nullable();
            $table->date('contract_date');
            $table->enum('location', ['zurich', 'exeter', 'grand_bend']);
            $table->unsignedBigInteger('device_id')->nullable();
            $table->string('sim_number')->nullable();
            $table->string('imei_number')->nullable();
            $table->decimal('amount_paid_for_device', 8, 2)->default(0.00);
            $table->decimal('agreement_credit_amount', 8, 2)->default(0.00);
            $table->unsignedBigInteger('plan_id')->nullable(); // Made nullable
            $table->unsignedBigInteger('commitment_period_id')->nullable(); // Made nullable
            $table->date('first_bill_date');
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->foreign('subscriber_id')->references('id')->on('subscribers')->onDelete('cascade');
            $table->foreign('activity_type_id')->references('id')->on('activity_types')->onDelete('set null');
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null');
            $table->foreign('commitment_period_id')->references('id')->on('commitment_periods')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('contracts');
    }
}
