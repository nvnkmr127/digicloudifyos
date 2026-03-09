<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recommendation_type');
            $table->decimal('priority_score', 5, 2)->default(50.00);
            $table->text('summary');
            $table->text('rationale')->nullable();
            $table->json('action_payload')->default('{}');
            $table->enum('status', ['PENDING', 'APPLIED', 'DISMISSED'])->default('PENDING');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status', 'priority_score', 'generated_at'], 'recommendations_org_status_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
