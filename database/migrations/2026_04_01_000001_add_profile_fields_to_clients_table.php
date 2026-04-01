<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('website_url')->nullable()->after('email');
            $table->string('phone')->nullable()->after('website_url');

            $table->string('timezone')->nullable()->after('industry');
            $table->string('currency_code', 3)->nullable()->after('timezone');

            $table->string('address_line1')->nullable()->after('currency_code');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country_code', 2)->nullable()->after('postal_code');

            $table->text('business_description')->nullable()->after('country_code');
            $table->json('goals')->nullable()->after('business_description');
            $table->json('target_audience')->nullable()->after('goals');
            $table->json('competitors')->nullable()->after('target_audience');
            $table->json('primary_kpis')->nullable()->after('competitors');

            $table->timestamp('gdpr_consent_at')->nullable()->after('primary_kpis');
            $table->timestamp('ccpa_opt_out_at')->nullable()->after('gdpr_consent_at');
            $table->integer('data_retention_days')->nullable()->after('ccpa_opt_out_at');
            $table->string('privacy_contact_email')->nullable()->after('data_retention_days');

            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'website_url',
                'phone',
                'timezone',
                'currency_code',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'postal_code',
                'country_code',
                'business_description',
                'goals',
                'target_audience',
                'competitors',
                'primary_kpis',
                'gdpr_consent_at',
                'ccpa_opt_out_at',
                'data_retention_days',
                'privacy_contact_email',
            ]);
        });
    }
};

