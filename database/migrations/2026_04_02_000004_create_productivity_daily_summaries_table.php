<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productivity_daily_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->date('summary_date');

            $table->decimal('hours_tracked', 8, 2)->default(0);
            $table->decimal('billable_hours', 8, 2)->default(0);
            $table->decimal('billable_ratio', 6, 2)->default(0);

            $table->unsignedInteger('tasks_completed')->default(0);
            $table->decimal('avg_task_cycle_days', 8, 2)->default(0);
            $table->unsignedInteger('overdue_tasks')->default(0);

            $table->decimal('allocated_hours', 8, 2)->default(0);
            $table->decimal('utilization_rate', 6, 2)->default(0);

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'employee_id', 'summary_date'], 'prod_daily_unique');
            $table->index(['organization_id', 'summary_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productivity_daily_summaries');
    }
};
