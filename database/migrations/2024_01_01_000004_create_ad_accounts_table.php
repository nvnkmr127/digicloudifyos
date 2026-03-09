<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['GOOGLE_ADS', 'META_ADS', 'LINKEDIN_ADS', 'TIKTOK_ADS', 'OTHER']);
            $table->string('account_name');
            $table->string('external_account_id');
            $table->char('currency_code', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'ARCHIVED'])->default('ACTIVE');
            $table->timestamps();

            $table->unique(['organization_id', 'platform', 'external_account_id'], 'ad_accounts_org_platform_external_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_accounts');
    }
};
