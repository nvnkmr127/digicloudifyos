<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audience_insights')) {
            return;
        }

        if (! Schema::hasColumn('audience_insights', 'age')) {
            return;
        }

        Schema::table('audience_insights', function (Blueprint $table) {
            try { $table->dropUnique('audience_insights_unique_idx'); } catch (\Throwable $e) {}

            $table->unique(
                ['ad_account_id', 'campaign_id', 'ad_set_id', 'ad_id', 'date', 'breakdown_type', 'age', 'gender', 'country', 'city', 'device', 'placement', 'hour'],
                'audience_insights_unique_idx'
            );
        });
    }

    public function down(): void
    {
    }
};

