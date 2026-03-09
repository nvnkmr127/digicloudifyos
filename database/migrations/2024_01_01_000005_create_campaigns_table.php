<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('ad_account_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('external_campaign_id')->nullable();
            $table->string('objective')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'ARCHIVED'])->default('ACTIVE');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('daily_budget', 18, 4)->nullable();
            $table->decimal('lifetime_budget', 18, 4)->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'ad_account_id', 'external_campaign_id'], 'campaigns_org_account_external_unique');
            $table->index(['organization_id', 'ad_account_id', 'status', 'start_date', 'end_date'], 'campaigns_org_account_status_dates_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
