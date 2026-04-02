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
        Schema::create('briefing_action_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('briefing_id');
            $table->uuid('client_id');
            $table->uuid('ai_insight_id')->nullable();

            $table->integer('sort_order')->default(0);
            $table->enum('priority_level', ['urgent', 'important', 'opportunity']);

            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('action');
            $table->text('expected_impact')->nullable();
            $table->string('effort', 50)->nullable();

            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->uuid('completed_by')->nullable();

            $table->timestamps();

            $table->foreign('briefing_id')->references('id')->on('daily_briefings')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('ai_insight_id')->references('id')->on('ai_insights')->onDelete('set null');

            $table->index(['briefing_id', 'sort_order']);
            $table->index(['briefing_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('briefing_action_items');
    }
};
