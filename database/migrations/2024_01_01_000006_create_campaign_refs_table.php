<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_refs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->string('external_campaign_id');
            $table->string('platform');
            $table->string('campaign_name');
            $table->enum('status', ['planning', 'creative_requested', 'ready', 'running', 'optimizing', 'completed'])->default('planning');
            $table->string('analytics_url')->nullable();
            $table->timestamps();

            $table->index('client_id');
            $table->index('external_campaign_id');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_refs');
    }
};
