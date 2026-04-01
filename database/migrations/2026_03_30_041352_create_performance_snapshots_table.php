<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('client_id');
            $table->string('channel_type', 100);
            $table->date('snapshot_date');
            
            // Core Metrics
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('spend', 14, 4)->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('revenue', 14, 4)->default(0);
            
            // Performance Calcs
            $table->decimal('ctr', 10, 6)->nullable();
            $table->decimal('cpc', 10, 4)->nullable();
            $table->decimal('cpm', 10, 4)->nullable();
            $table->decimal('roas', 10, 4)->nullable();
            
            // Organic / Engagement
            $table->unsignedBigInteger('reach')->default(0);
            $table->decimal('engagement_rate', 10, 6)->nullable();
            
            // Lead Metrics
            $table->integer('leads')->default(0);
            $table->decimal('cost_per_lead', 10, 4)->nullable();
            
            // Baselines (7-day rolling avg)
            $table->decimal('baseline_ctr', 10, 6)->nullable();
            $table->decimal('baseline_cpc', 10, 4)->nullable();
            $table->decimal('baseline_roas', 10, 4)->nullable();
            $table->decimal('baseline_leads', 10, 2)->nullable();
            
            // Intelligence Data
            $table->json('anomaly_flags')->nullable();
            $table->json('raw_data')->nullable();
            
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'channel_type', 'snapshot_date'], 'perf_snapshot_unique');
            $table->index(['client_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_snapshots');
    }
};
