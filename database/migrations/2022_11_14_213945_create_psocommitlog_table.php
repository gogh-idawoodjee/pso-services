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
        Schema::create('psocommitlog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('input_reference')->nullable();
            $table->json('pso_suggestions')->nullable();
            $table->json('output_payload')->nullable();
            $table->json('pso_response')->nullable();
            $table->text('response_time')->nullable();
            $table->json('transfer_stats')->nullable();
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
        Schema::dropIfExists('psocommitlog');
    }
};
