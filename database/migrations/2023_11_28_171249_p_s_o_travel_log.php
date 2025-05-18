<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('psotravellog', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('input_reference')->nullable();
            $table->json('address_from')->nullable();
            $table->json('address_to')->nullable();
            $table->json('google_response')->nullable();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->string('status')->nullable();
            $table->json('pso_response')->nullable();
            $table->text('response_time')->nullable();
            $table->json('transfer_stats')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
