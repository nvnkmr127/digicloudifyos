<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facebook_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('facebook_lead_id')->unique();
            $table->string('form_id');
            $table->string('form_name')->nullable();

            // Link to ads structure
            $table->uuid('campaign_id')->nullable();
            $table->uuid('ad_id')->nullable();

            // Lead Data
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->json('custom_questions')->nullable();

            $table->json('raw_data')->nullable();
            $table->timestamps();

            // Foreign keys to our ads tables
            $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
            $table->foreign('ad_id')->references('id')->on('ads')->nullOnDelete();

            $table->index(['organization_id', 'form_id']);
        });

        // Add page_id and page_access_token to ad_accounts for lead gen
        Schema::table('ad_accounts', function (Blueprint $table) {
            $table->string('facebook_page_id')->nullable()->after('external_account_id');
            $table->text('facebook_page_token')->nullable()->after('facebook_page_id');
        });
    }

    public function down(): void
    {
        Schema::table('ad_accounts', function (Blueprint $table) {
            $table->dropColumn(['facebook_page_id', 'facebook_page_token']);
        });
        Schema::dropIfExists('facebook_leads');
    }
};
