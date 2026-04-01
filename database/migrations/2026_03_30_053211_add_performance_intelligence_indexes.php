<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('performance_snapshots', function (Blueprint $table) {
            $table->index(['organization_id', 'snapshot_date', 'channel_type'], 'idx_perf_snap_org_date_channel');
        });

        Schema::table('ai_insights', function (Blueprint $table) {
            $table->index(['organization_id', 'is_dismissed', 'is_completed', 'insight_date'], 'idx_insights_org_status_date');
        });

        Schema::table('performance_anomalies', function (Blueprint $table) {
            $table->index(['organization_id', 'resolved_at', 'severity'], 'idx_anomalies_org_resolved_sev');
        });

        Schema::table('client_health_scores', function (Blueprint $table) {
            $table->index(['client_id', 'score_date'], 'idx_health_client_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_snapshots', function (Blueprint $table) {
            $table->dropIndex('idx_perf_snap_org_date_channel');
        });
        Schema::table('ai_insights', function (Blueprint $table) {
            $table->dropIndex('idx_insights_org_status_date');
        });
        Schema::table('performance_anomalies', function (Blueprint $table) {
            $table->dropIndex('idx_anomalies_org_resolved_sev');
        });
        Schema::table('client_health_scores', function (Blueprint $table) {
            $table->dropIndex('idx_health_client_date');
        });
    }
};
