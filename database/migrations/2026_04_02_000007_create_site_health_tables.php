<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_speed_daily_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');
            $table->string('url', 512);

            $table->unsignedSmallInteger('performance_mobile')->nullable();
            $table->unsignedSmallInteger('performance_desktop')->nullable();
            $table->decimal('lcp_ms_mobile', 10, 2)->nullable();
            $table->decimal('lcp_ms_desktop', 10, 2)->nullable();
            $table->decimal('cls_mobile', 10, 4)->nullable();
            $table->decimal('cls_desktop', 10, 4)->nullable();

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'metric_date', 'url'], 'pagespeed_daily_unique');
            $table->index(['client_id', 'metric_date']);
        });

        Schema::create('domain_expiry_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->date('check_date');
            $table->string('domain', 255);

            $table->date('expires_on')->nullable();
            $table->integer('days_remaining')->nullable();
            $table->string('registrar')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'check_date', 'domain'], 'domain_expiry_unique');
            $table->index(['client_id', 'check_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_expiry_checks');
        Schema::dropIfExists('page_speed_daily_metrics');
    }
};
