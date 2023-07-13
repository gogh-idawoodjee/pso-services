<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_request', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('run_id')->nullable();
            $table->json('appointment_request');
            $table->json('input_request');
            $table->json('appointment_response');
            $table->json('valid_offers')->nullable();
            $table->json('invalid_offers')->nullable();
            $table->json('best_offer');
            $table->string('summary');
            $table->smallInteger('total_offers_returned');
            $table->smallInteger('total_valid_offers_returned');
            $table->smallInteger('total_invalid_offers_returned');
            $table->smallInteger('status'); // 0 unacknowledged, 1 accepted, 2 declined
            $table->smallInteger('accepted_offer_id')->nullable();
            $table->smallInteger('appointed_check_offer_id')->nullable();
            $table->json('accepted_offer')->nullable();
            $table->string('activity_id');
            $table->string('base_url');
            $table->string('input_reference_id');
            $table->smallInteger('appointed_check_complete')->nullable();
            $table->smallInteger('appointed_check_result')->nullable();
            $table->string('accept_decline_input_reference_id')->nullable();
            $table->string('appointed_check_input_reference_id')->nullable();
            $table->string('appointment_template_id');
            $table->string('slot_usage_rule_id')->nullable();
            $table->string('dataset_id');
            $table->dateTime('appointment_template_datetime');
            $table->dateTime('offer_expiry_datetime');
            $table->dateTime('appointed_check_datetime')->nullable();
            $table->dateTime('accept_decline_datetime')->nullable();
            $table->string('appointment_template_duration');
            $table->string('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointment_request');
    }
};
