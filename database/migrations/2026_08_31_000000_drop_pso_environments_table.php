<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the table backing the now-deleted PsoEnvironment V1 model. Its last
 * consumer, the V1 scheduled rota-to-DSE task, was removed in 8feac84 — see
 * 2026_08_08_203421_drop_pso_datasets_and_pso_tokens_tables.php for the
 * matching removal of PsoDataset/PsoToken. Written but not run, same as
 * that migration, by request.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pso_environments');
    }

    public function down(): void
    {
        Schema::create('pso_environments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id');
            $table->string('name')->nullable();
            $table->string('base_url');
            $table->string('account_id');
            $table->string('username');
            $table->string('manual_scheduling_shift_id');
            $table->string('standard_shift_id');
            $table->text('password');
            $table->timestamps();
        });
    }
};
