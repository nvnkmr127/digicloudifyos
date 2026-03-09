<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('client_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('project_code', 50)->unique();
            $table->string('status', 50)->default('planning');
            $table->string('priority', 50)->default('medium');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->decimal('actual_cost', 12, 2)->default(0);
            $table->string('billing_type', 50)->default('fixed');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->uuid('project_manager_id')->nullable();
            $table->string('health_status', 50)->default('on_track');
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('project_manager_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
