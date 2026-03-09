<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('event_type', ['campaign_created', 'creative_requested', 'creative_approved', 'task_completed']);
            $table->enum('entity_type', ['campaign', 'creative', 'task', 'lead']);
            $table->uuid('entity_id');
            $table->json('payload')->default('{}');
            $table->timestamps();

            $table->index(['organization_id', 'event_type', 'created_at'], 'workflow_events_org_event_created_idx');
            $table->index(['organization_id', 'entity_type', 'entity_id', 'created_at'], 'workflow_events_org_entity_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_events');
    }
};
