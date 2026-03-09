<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('creative_request_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('task_type')->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'review', 'completed', 'blocked'])->default('pending');
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deadline')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'assigned_to', 'status', 'deadline'], 'tasks_org_assigned_status_deadline_idx');
            $table->index(['organization_id', 'campaign_id', 'status'], 'tasks_org_campaign_status_idx');
            $table->index(['organization_id', 'creative_request_id'], 'tasks_org_creative_request_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
