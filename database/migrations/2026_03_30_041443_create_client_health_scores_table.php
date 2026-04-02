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
        Schema::create('client_health_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('client_id');
            $table->date('score_date');

            $table->unsignedTinyInteger('overall_score');
            $table->unsignedTinyInteger('ad_performance_score')->nullable();
            $table->unsignedTinyInteger('organic_score')->nullable();
            $table->unsignedTinyInteger('conversion_score')->nullable();
            $table->unsignedTinyInteger('budget_efficiency_score')->nullable();

            $table->json('score_breakdown')->nullable();
            $table->enum('trend', ['improving', 'stable', 'declining'])->default('stable');

            $table->timestamps();

            $table->unique(['client_id', 'score_date'], 'client_score_date_unique');
            $table->index(['organization_id', 'score_date']);

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_health_scores');
    }
};
