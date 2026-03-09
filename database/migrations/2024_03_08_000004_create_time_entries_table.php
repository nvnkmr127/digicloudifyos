<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('employee_id');
            $table->uuid('project_id')->nullable();
            $table->uuid('task_id')->nullable();
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->text('description')->nullable();
            $table->boolean('billable')->default(true);
            $table->boolean('approved')->default(false);
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('employee_id');
            $table->index('project_id');
            $table->index('date');
            $table->index('billable');
            $table->index('approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
