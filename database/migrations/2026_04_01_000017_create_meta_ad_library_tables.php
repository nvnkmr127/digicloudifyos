<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ad_library_ads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_competitor_id')->constrained('client_competitors')->cascadeOnDelete();

            $table->string('library_ad_id', 64);
            $table->string('page_id', 64)->nullable();
            $table->string('page_name')->nullable();
            $table->string('ad_snapshot_url', 2048)->nullable();

            $table->timestamp('ad_creation_time')->nullable();
            $table->timestamp('ad_delivery_start_time')->nullable();
            $table->timestamp('ad_delivery_stop_time')->nullable();

            $table->json('publisher_platforms')->nullable();
            $table->json('creative_bodies')->nullable();
            $table->json('creative_link_titles')->nullable();
            $table->json('creative_link_descriptions')->nullable();
            $table->json('creative_link_captions')->nullable();

            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'client_competitor_id', 'library_ad_id'], 'meta_ad_unique');
            $table->index(['client_competitor_id', 'last_seen_at']);
        });

        Schema::create('meta_ad_library_daily_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_competitor_id')->constrained('client_competitors')->cascadeOnDelete();

            $table->date('metric_date');
            $table->unsignedBigInteger('active_ads_count')->default(0);
            $table->unsignedBigInteger('new_ads_count')->default(0);

            $table->unsignedBigInteger('pages_fetched')->default(0);
            $table->unsignedBigInteger('records_fetched')->default(0);
            $table->boolean('truncated')->default(false);

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'client_competitor_id', 'metric_date'], 'meta_ad_daily_unique');
            $table->index(['client_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_library_daily_summaries');
        Schema::dropIfExists('meta_ad_library_ads');
    }
};
