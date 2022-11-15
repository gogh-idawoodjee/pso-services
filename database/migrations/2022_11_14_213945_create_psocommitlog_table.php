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
            $table->uuid('id');
            $table->json('pso_suggestions');
            $table->json('output_payload');
            $table->json('pso_response');
            $table->text('response_time');
            $table->json('transfer_stats');
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
