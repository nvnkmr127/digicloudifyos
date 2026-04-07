<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audience_insights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('ad_account_id')->constrained()->cascadeOnDelete();
            $table->uuid('campaign_id')->nullable();
            $table->uuid('ad_set_id')->nullable();
            $table->uuid('ad_id')->nullable();
            $table->date('date');

            $table->string('breakdown_type', 50);
            $table->string('age', 20)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('device', 50)->nullable();
            $table->string('placement', 100)->nullable();
            $table->string('hour', 10)->nullable();

            $table->decimal('spend', 18, 4)->default(0);
            $table->integer('impressions')->default(0);
            $table->integer('reach')->default(0);
            $table->integer('clicks')->default(0);
            $table->decimal('conversions', 18, 4)->default(0);
            $table->integer('leads')->default(0);

            $table->json('metadata')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
            $table->foreign('ad_set_id')->references('id')->on('ad_sets')->nullOnDelete();
            $table->foreign('ad_id')->references('id')->on('ads')->nullOnDelete();

            $table->index(['ad_account_id', 'date', 'breakdown_type']);
            $table->unique(['ad_account_id', 'campaign_id', 'ad_set_id', 'ad_id', 'date', 'breakdown_type', 'age', 'gender', 'country', 'city', 'device', 'placement', 'hour'], 'audience_insights_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audience_insights');
    }
};
