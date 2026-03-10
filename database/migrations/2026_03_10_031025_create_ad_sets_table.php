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
        Schema::create('ad_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('external_adset_id')->unique();
            $table->string('status')->nullable();
            $table->decimal('daily_budget', 18, 4)->nullable();
            $table->decimal('lifetime_budget', 18, 4)->nullable();
            $table->json('targeting')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'campaign_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_sets');
    }
};
