<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PsoDatasets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('pso_datasets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pso_environment_id');
            $table->string('user_id');
            $table->string('rota_id');
            $table->string('dataset_id');
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
        //
        Schema::dropIfExists('pso_datasets');

    }
};
