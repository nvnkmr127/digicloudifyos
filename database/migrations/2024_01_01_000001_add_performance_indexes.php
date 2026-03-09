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
        // Add indexes for performance optimization

        // Invoices table indexes
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'issue_date'], 'idx_invoices_org_status_date');
                $table->index(['organization_id', 'status'], 'idx_invoices_org_status');
                $table->index('issue_date', 'idx_invoices_issue_date');
            });
        }

        // Time entries table indexes
        if (Schema::hasTable('time_entries')) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->index(['organization_id', 'date', 'billable'], 'idx_time_entries_org_date_billable');
                $table->index(['organization_id', 'task_id'], 'idx_time_entries_org_task');
                $table->index('date', 'idx_time_entries_date');
            });
        }

        // Leads table indexes
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'source'], 'idx_leads_org_status_source');
                $table->index(['organization_id', 'created_at'], 'idx_leads_org_created');
                $table->index('status', 'idx_leads_status');
            });
        }

        // Projects table indexes
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'health_status'], 'idx_projects_org_status_health');
                $table->index(['organization_id', 'client_id'], 'idx_projects_org_client');
                $table->index('status', 'idx_projects_status');
            });
        }

        // Tasks table indexes
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'assigned_to'], 'idx_tasks_org_status_assigned');
                $table->index(['organization_id', 'project_id'], 'idx_tasks_org_project');
                $table->index('due_date', 'idx_tasks_due_date');
            });
        }

        // Campaigns table indexes
        if (Schema::hasTable('campaigns')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'client_id'], 'idx_campaigns_org_status_client');
                $table->index(['organization_id', 'ad_account_id'], 'idx_campaigns_org_ad_account');
                $table->index('status', 'idx_campaigns_status');
            });
        }

        // Daily metrics table indexes
        if (Schema::hasTable('daily_metrics')) {
            Schema::table('daily_metrics', function (Blueprint $table) {
                $table->index(['campaign_id', 'date'], 'idx_daily_metrics_campaign_date');
                $table->index('date', 'idx_daily_metrics_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes if they exist
        $indexes = [
            'invoices' => ['idx_invoices_org_status_date', 'idx_invoices_org_status', 'idx_invoices_issue_date'],
            'time_entries' => ['idx_time_entries_org_date_billable', 'idx_time_entries_org_task', 'idx_time_entries_date'],
            'leads' => ['idx_leads_org_status_source', 'idx_leads_org_created', 'idx_leads_status'],
            'projects' => ['idx_projects_org_status_health', 'idx_projects_org_client', 'idx_projects_status'],
            'tasks' => ['idx_tasks_org_status_assigned', 'idx_tasks_org_project', 'idx_tasks_due_date'],
            'campaigns' => ['idx_campaigns_org_status_client', 'idx_campaigns_org_ad_account', 'idx_campaigns_status'],
            'daily_metrics' => ['idx_daily_metrics_campaign_date', 'idx_daily_metrics_date'],
        ];

        foreach ($indexes as $table => $indexNames) {
            foreach ($indexNames as $indexName) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        }
    }
};
