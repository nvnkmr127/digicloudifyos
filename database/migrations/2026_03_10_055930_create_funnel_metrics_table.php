<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('funnel_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->uuid('campaign_id')->nullable();

            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('landing_views')->default(0);
            $table->integer('leads')->default(0);
            $table->integer('sales')->default(0);

            // Conversion rates as decimal
            $table->decimal('ctr', 8, 4)->default(0); // clicks / impressions
            $table->decimal('lpc_rate', 8, 4)->default(0); // landing_views / clicks
            $table->decimal('lead_conv_rate', 8, 4)->default(0); // leads / landing_views
            $table->decimal('sales_conv_rate', 8, 4)->default(0); // sales / leads

            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funnel_metrics');
    }
};
