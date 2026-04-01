<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('twitter_daily_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_channel_connection_id')
                ->nullable()
                ->constrained('client_channel_connections')
                ->nullOnDelete();

            $table->date('metric_date');
            $table->string('twitter_user_id', 64)->nullable();
            $table->string('twitter_username', 128)->nullable();

            $table->unsignedBigInteger('followers')->default(0);
            $table->unsignedBigInteger('following')->default(0);
            $table->unsignedBigInteger('tweets')->default(0);
            $table->unsignedBigInteger('listed')->default(0);

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'client_id', 'metric_date', 'twitter_user_id'],
                'twitter_daily_unique'
            );
            $table->index(['client_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('twitter_daily_metrics');
    }
};

