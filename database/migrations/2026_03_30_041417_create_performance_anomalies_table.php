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
        Schema::create('performance_anomalies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('client_id');
            $table->uuid('snapshot_id')->nullable();
            
            $table->string('anomaly_type', 100);
            $table->string('channel_type', 100);
            $table->string('metric_name', 100);
            
            $table->decimal('current_value', 14, 6);
            $table->decimal('baseline_value', 14, 6);
            $table->decimal('deviation_percentage', 8, 2);
            
            $table->enum('severity', ['critical', 'high', 'medium', 'low']);
            
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            
            $table->json('context')->nullable();
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('performance_snapshots')->onDelete('set null');
            
            $table->index(['organization_id', 'client_id', 'detected_at'], 'perf_anomaly_list_idx');
            $table->index(['organization_id', 'severity', 'resolved_at'], 'perf_anomaly_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_anomalies');
    }
};
