<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('event_id')->constrained('workflow_events')->cascadeOnDelete();
            $table->enum('action_type', ['create_task', 'update_campaign_status', 'send_notification']);
            $table->string('status');
            $table->json('result')->default('{}');
            $table->timestamps();

            $table->index(['workflow_rule_id', 'created_at'], 'automation_logs_workflow_created_idx');
            $table->index(['event_id', 'created_at'], 'automation_logs_event_created_idx');
            $table->index(['action_type', 'status', 'created_at'], 'automation_logs_action_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
    }
};
