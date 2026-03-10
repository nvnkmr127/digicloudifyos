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
        Schema::create('conversion_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->uuid('campaign_id')->nullable();
            $table->uuid('adset_id')->nullable();
            $table->uuid('ad_id')->nullable();
            $table->string('event_type');
            $table->integer('count')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('cost_per_event', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
            $table->foreign('adset_id')->references('id')->on('ad_sets')->nullOnDelete();
            $table->foreign('ad_id')->references('id')->on('ads')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_events');
    }
};
