<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amazon_sp_daily_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_channel_connection_id')
                ->nullable()
                ->constrained('client_channel_connections')
                ->nullOnDelete();

            $table->date('metric_date');
            $table->string('seller_id', 64)->nullable();
            $table->string('marketplace_id', 32)->nullable();
            $table->string('currency_code', 3)->nullable();

            $table->unsignedBigInteger('orders_count')->default(0);
            $table->decimal('gross_sales', 14, 4)->default(0);
            $table->decimal('net_sales', 14, 4)->default(0);

            $table->unsignedBigInteger('pages_fetched')->default(0);
            $table->unsignedBigInteger('records_fetched')->default(0);
            $table->boolean('truncated')->default(false);

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'client_id', 'metric_date', 'seller_id', 'marketplace_id'],
                'amazon_sp_daily_unique'
            );
            $table->index(['client_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amazon_sp_daily_metrics');
    }
};
