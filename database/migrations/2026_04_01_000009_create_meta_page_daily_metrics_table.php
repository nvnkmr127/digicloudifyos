<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_page_daily_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_channel_connection_id')
                ->nullable()
                ->constrained('client_channel_connections')
                ->nullOnDelete();

            $table->date('metric_date');
            $table->string('page_id', 64)->nullable();
            $table->string('page_name', 255)->nullable();

            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('engaged_users')->default(0);
            $table->unsignedBigInteger('post_engagements')->default(0);

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'client_id', 'metric_date', 'page_id'],
                'meta_page_daily_unique'
            );
            $table->index(['client_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_page_daily_metrics');
    }
};

