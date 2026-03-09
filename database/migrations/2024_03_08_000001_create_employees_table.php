<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('user_id');
            $table->string('employee_code', 50)->unique();
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('employment_type', 50)->default('full-time');
            $table->date('join_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->string('status', 50)->default('active');
            $table->uuid('manager_id')->nullable();
            $table->integer('work_hours_per_week')->default(40);
            $table->decimal('performance_rating', 3, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('user_id');
            $table->index('department');
            $table->index('status');
            $table->index('manager_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
