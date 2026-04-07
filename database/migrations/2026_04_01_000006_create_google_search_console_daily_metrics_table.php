<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_search_console_daily_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_channel_connection_id')
                ->nullable()
                ->constrained('client_channel_connections', 'id', 'gsc_metrics_conn_id_fk')
                ->nullOnDelete();

            $table->date('metric_date');
            $table->string('site_url', 512)->nullable();

            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->decimal('ctr', 10, 6)->nullable();
            $table->decimal('avg_position', 10, 4)->nullable();

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'client_id', 'metric_date', 'site_url'],
                'gsc_daily_unique'
            );
            $table->index(['client_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_search_console_daily_metrics');
    }
};
