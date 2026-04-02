<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playbook_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 50)->default('onboarding'); // onboarding|seo|branding|ecom
            $table->boolean('is_active')->default(true);
            $table->json('steps')->default('[]');
            $table->timestamps();

            $table->index(['organization_id', 'category', 'is_active']);
        });

        Schema::create('client_playbook_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('playbook_template_id')->constrained('playbook_templates')->cascadeOnDelete();
            $table->date('run_date');
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'playbook_template_id', 'run_date'], 'client_playbook_unique');
            $table->index(['client_id', 'run_date']);
        });

        Schema::create('playbook_run_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_playbook_run_id')->constrained('client_playbook_runs')->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained()->cascadeOnDelete();
            $table->string('step_key', 191)->nullable();
            $table->timestamps();

            $table->unique(['client_playbook_run_id', 'task_id'], 'playbook_task_unique');
            $table->index(['task_id']);
        });

        Schema::create('brand_kits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->json('identity')->nullable(); // logo, colors, fonts
            $table->json('voice')->nullable(); // tone, do/dont
            $table->json('claims')->nullable(); // approved/restricted claims
            $table->timestamps();

            $table->unique(['organization_id', 'client_id'], 'brand_kits_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_kits');
        Schema::dropIfExists('playbook_run_tasks');
        Schema::dropIfExists('client_playbook_runs');
        Schema::dropIfExists('playbook_templates');
    }
};
