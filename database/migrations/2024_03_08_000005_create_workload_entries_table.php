<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workload_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('employee_id');
            $table->uuid('project_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('allocated_hours', 6, 2);
            $table->decimal('actual_hours', 6, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('employee_id');
            $table->index('project_id');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workload_entries');
    }
};
