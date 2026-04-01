<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_sync_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_channel_connection_id')
                ->nullable()
                ->constrained('client_channel_connections')
                ->nullOnDelete();

            $table->string('channel_type', 100);
            $table->date('run_date');

            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempt')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();

            $table->text('error_message')->nullable();
            $table->json('metrics')->nullable();

            $table->timestamps();

            $table->unique(
                ['organization_id', 'client_id', 'channel_type', 'run_date'],
                'integration_sync_unique'
            );
            $table->index(['organization_id', 'run_date']);
            $table->index(['client_id', 'run_date']);
            $table->index(['status', 'next_retry_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_sync_runs');
    }
};

