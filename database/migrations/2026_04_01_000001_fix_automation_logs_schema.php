<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('automation_logs', 'organization_id')) {
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->foreignUuid('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        Schema::table('automation_logs', function (Blueprint $table) {
            $table->string('action_type')->nullable()->change();
        });

        $ruleOrganizations = DB::table('workflow_rules')
            ->pluck('organization_id', 'id');

        DB::table('automation_logs')
            ->select('id', 'workflow_rule_id')
            ->get()
            ->each(function ($log) use ($ruleOrganizations) {
                $organizationId = $ruleOrganizations[$log->workflow_rule_id] ?? null;

                if ($organizationId) {
                    DB::table('automation_logs')
                        ->where('id', $log->id)
                        ->update(['organization_id' => $organizationId]);
                }
            });

        try {
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'created_at'], 'automation_logs_org_status_created_idx');
            });
        } catch (Throwable) {
        }
    }

    public function down(): void
    {
        Schema::table('automation_logs', function (Blueprint $table) {
            $table->dropIndex('automation_logs_org_status_created_idx');
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
