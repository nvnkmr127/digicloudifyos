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
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('channel', ['WHATSAPP', 'EMAIL', 'SMS'])->default('EMAIL');
            $table->string('target_segment')->nullable();
            $table->json('content_payload');
            $table->enum('status', ['DRAFT', 'SCHEDULED', 'PROCESSING', 'COMPLETED', 'FAILED'])->default('DRAFT');
            $table->uuid('automation_rule_id')->nullable(); // Follow-up automation mapping
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
