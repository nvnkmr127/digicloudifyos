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
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('client_id');
            $table->uuid('anomaly_id')->nullable();

            $table->string('channel_type', 100)->nullable();
            $table->date('insight_date');

            $table->enum('priority', ['critical', 'high', 'medium', 'low', 'opportunity'])->default('medium');
            $table->enum('category', ['ad_performance', 'budget', 'organic', 'conversion', 'opportunity']);

            $table->string('title', 255);
            $table->text('issue_description');
            $table->text('root_cause')->nullable();
            $table->text('recommended_action');
            $table->text('expected_impact')->nullable();

            $table->enum('effort_level', ['low', 'medium', 'high'])->default('low');
            $table->enum('urgency', ['today', 'this_week', 'next_week'])->default('this_week');

            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('dismissed_at')->nullable();
            $table->uuid('dismissed_by')->nullable();

            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->uuid('completed_by')->nullable();

            $table->json('raw_ai_response')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('anomaly_id')->references('id')->on('performance_anomalies')->onDelete('set null');

            $table->index(['organization_id', 'client_id', 'insight_date'], 'ai_insight_list_idx');
            $table->index(['organization_id', 'is_dismissed', 'is_completed', 'priority'], 'ai_insight_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
    }
};
