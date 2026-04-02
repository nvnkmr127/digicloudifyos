<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_business_profile_daily_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');

            $table->unsignedBigInteger('website_clicks')->default(0);
            $table->unsignedBigInteger('call_clicks')->default(0);
            $table->unsignedBigInteger('directions_requests')->default(0);

            $table->unsignedBigInteger('impressions_search_desktop')->default(0);
            $table->unsignedBigInteger('impressions_search_mobile')->default(0);
            $table->unsignedBigInteger('impressions_maps_desktop')->default(0);
            $table->unsignedBigInteger('impressions_maps_mobile')->default(0);

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'metric_date'], 'gbp_daily_unique');
            $table->index(['client_id', 'metric_date']);
        });

        Schema::create('google_business_profile_monthly_keywords', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->date('month_start');
            $table->string('keyword', 512);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'month_start', 'keyword'], 'gbp_kw_unique');
            $table->index(['client_id', 'month_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_business_profile_monthly_keywords');
        Schema::dropIfExists('google_business_profile_daily_metrics');
    }
};
