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
        Schema::create('psorundata', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('run_id');
            $table->string('activity_id');
            $table->string('activity_type_id');
            $table->smallInteger('priority')->nullable();
            $table->dateTime('created_datetime');
            $table->integer('activity_duration');
            $table->string('region_id')->nullable();
            $table->decimal('lat');
            $table->decimal('long');
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
        Schema::dropIfExists('psorundata');
    }
};
