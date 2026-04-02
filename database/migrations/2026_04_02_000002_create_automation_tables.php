<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('channel_type', 100)->nullable();
            $table->string('trigger_type', 100); // anomaly|threshold|schedule
            $table->json('trigger_config')->nullable();
            $table->string('action_type', 100); // create_task|propose_change
            $table->json('action_config')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('automation_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('automation_rule_id')->nullable()->constrained('automation_rules')->nullOnDelete();

            $table->string('channel_type', 100)->nullable();
            $table->string('action_type', 100);
            $table->json('payload')->nullable();

            $table->enum('status', ['proposed', 'approved', 'rejected', 'applied', 'failed'])->default('proposed');
            $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('result')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status', 'created_at']);
            $table->index(['client_id', 'channel_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_actions');
        Schema::dropIfExists('automation_rules');
    }
};
