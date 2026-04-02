<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_analytics_daily_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_channel_connection_id')
                ->nullable()
                ->constrained('client_channel_connections')
                ->nullOnDelete();

            $table->date('metric_date');
            $table->string('property_id', 64)->nullable();

            $table->unsignedBigInteger('sessions')->default(0);
            $table->unsignedBigInteger('users')->default(0);
            $table->unsignedBigInteger('new_users')->default(0);
            $table->unsignedBigInteger('engaged_sessions')->default(0);
            $table->unsignedBigInteger('conversions')->default(0);
            $table->decimal('revenue', 14, 4)->default(0);

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'client_id', 'metric_date', 'property_id'],
                'ga_daily_unique'
            );
            $table->index(['client_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_analytics_daily_metrics');
    }
};
