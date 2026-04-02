<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_site_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->date('audit_date');
            $table->string('base_url', 2048);
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'audit_date'], 'seo_site_audit_unique');
            $table->index(['client_id', 'audit_date']);
        });

        Schema::create('seo_site_audit_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('seo_site_audit_id')->constrained('seo_site_audits')->cascadeOnDelete();
            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->string('issue_type', 100);
            $table->string('url', 2048)->nullable();
            $table->string('title')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'created_at']);
            $table->index(['seo_site_audit_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_site_audit_issues');
        Schema::dropIfExists('seo_site_audits');
    }
};
