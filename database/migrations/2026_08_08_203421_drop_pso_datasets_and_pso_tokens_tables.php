<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the tables backing the now-deleted PsoDataset and PsoToken V1
 * models. pso_environments is NOT dropped here — PsoEnvironment is still
 * used by IFSPSOAssistService for the scheduled rota-to-DSE task; it'll
 * be addressed once that task's fate is decided.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pso_tokens');
        Schema::dropIfExists('pso_datasets');
    }

    public function down(): void
    {
        Schema::create('pso_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('pso_environment_id');
            $table->text('token');
            $table->timestamp('token_expiry');
            $table->timestamps();
        });

        Schema::create('pso_datasets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pso_environment_id');
            $table->string('user_id');
            $table->string('rota_id');
            $table->string('dataset_id');
            $table->timestamps();
        });
    }
};
