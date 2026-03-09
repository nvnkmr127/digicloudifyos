<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('alert_type');
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM');
            $table->enum('status', ['OPEN', 'ACKNOWLEDGED', 'RESOLVED', 'DISMISSED'])->default('OPEN');
            $table->string('title');
            $table->text('message');
            $table->json('payload')->default('{}');
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status', 'triggered_at'], 'alerts_org_status_triggered_idx');
            $table->index(['organization_id', 'severity', 'status'], 'alerts_org_severity_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
