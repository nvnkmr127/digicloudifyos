<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_merchant_center_daily_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_channel_connection_id')
                ->nullable()
                ->constrained('client_channel_connections')
                ->nullOnDelete();

            $table->date('metric_date');
            $table->string('merchant_id', 32)->nullable();

            $table->unsignedBigInteger('items_checked')->default(0);
            $table->unsignedBigInteger('items_disapproved')->default(0);
            $table->unsignedBigInteger('items_pending')->default(0);
            $table->unsignedBigInteger('items_approved')->default(0);

            $table->unsignedBigInteger('issue_count')->default(0);
            $table->json('issue_breakdown')->nullable();

            $table->unsignedBigInteger('pages_fetched')->default(0);
            $table->unsignedBigInteger('records_fetched')->default(0);
            $table->boolean('truncated')->default(false);

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'client_id', 'metric_date', 'merchant_id'],
                'gmc_daily_unique'
            );
            $table->index(['client_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_merchant_center_daily_metrics');
    }
};

