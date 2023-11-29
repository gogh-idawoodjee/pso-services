<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('psotravellog', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('input_reference');
            $table->json('input_payload');
            $table->json('output_payload');
            $table->json('pso_response');
            $table->text('response_time');
            $table->json('transfer_stats');
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
