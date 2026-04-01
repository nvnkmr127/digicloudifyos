<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_insights', function (Blueprint $table) {
            $table->index(['organization_id', 'insight_date'], 'ai_insights_org_date_idx');
            $table->index(['client_id', 'is_dismissed', 'is_completed'], 'ai_insights_client_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ai_insights', function (Blueprint $table) {
            $table->dropIndex('ai_insights_org_date_idx');
            $table->dropIndex('ai_insights_client_status_idx');
        });
    }
};
