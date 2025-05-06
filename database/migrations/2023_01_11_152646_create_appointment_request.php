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

            // fields upon creation
            $table->uuid('id')->primary();
            $table->uuid('run_id')->nullable();
            $table->json('appointment_request');
            $table->uuid('appointment_request_id');
            $table->smallInteger('status'); // 0 unacknowledged (new), 1 accepted, 2 declined, 3, checked, -1 failed
            $table->json('activity');
            $table->string('activity_id');
            $table->string('base_url');
            $table->string('dataset_id');
            $table->string('input_reference_id');
            $table->json('input_request'); // just the input ref
            $table->string('appointment_template_id');
            $table->string('slot_usage_rule_id')->nullable();
            $table->string('appointment_template_duration')->nullable();
            $table->dateTime('appointment_template_datetime')->nullable();
            $table->dateTime('offer_expiry_datetime');


            // fields upon offers returned
            $table->json('appointment_response')->nullable();
            $table->json('valid_offers')->nullable();
            $table->json('invalid_offers')->nullable();
            $table->json('best_offer')->nullable();
            $table->string('summary')->nullable();
            $table->smallInteger('total_offers_returned')->nullable();
            $table->smallInteger('total_valid_offers_returned')->nullable();
            $table->smallInteger('total_invalid_offers_returned')->nullable();

            // fields up on check
            $table->smallInteger('appointed_check_offer_id')->nullable();
            $table->smallInteger('appointed_check_complete')->nullable();
            $table->smallInteger('appointed_check_result')->nullable();
            $table->string('appointed_check_input_reference_id')->nullable();
            $table->dateTime('appointed_check_datetime')->nullable();


            // fields upon accept/decline/check
            $table->smallInteger('accepted_offer_id')->nullable();
            $table->json('accepted_offer')->nullable();
            $table->string('accept_decline_input_reference_id')->nullable();
            $table->dateTime('accept_decline_datetime')->nullable();

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
