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
        Schema::create('psorun', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('sla_type_id');
            $table->string('dataset_id');
            $table->string('username');
            $table->string('password');
            $table->string('appointment_template_id');
            $table->string('base_url');
            $table->string('base_value');
            $table->integer('activity_count');
            $table->integer('activities_booked');
            $table->integer('activities_failed');
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
        Schema::dropIfExists('psorun');
    }
};
