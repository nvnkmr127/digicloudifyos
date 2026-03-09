<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('event_type', ['campaign_created', 'creative_requested', 'creative_approved', 'task_completed']);
            $table->enum('action_type', ['create_task', 'update_campaign_status', 'send_notification']);
            $table->json('action_config')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'event_type', 'is_active'], 'workflow_rules_org_event_active_idx');
            $table->index(['organization_id', 'action_type', 'is_active'], 'workflow_rules_org_action_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
    }
};
