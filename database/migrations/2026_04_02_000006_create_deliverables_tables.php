<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverable_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->enum('frequency', ['weekly', 'monthly'])->default('weekly');
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('client_deliverables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('deliverable_template_id')->nullable()->constrained('deliverable_templates')->nullOnDelete();

            $table->date('deliverable_date');
            $table->string('title');
            $table->enum('status', ['scheduled', 'generated', 'failed'])->default('scheduled');
            $table->timestamp('generated_at')->nullable();
            $table->longText('body_html')->nullable();
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'deliverable_date']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_deliverables');
        Schema::dropIfExists('deliverable_templates');
    }
};
