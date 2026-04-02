<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_console_dimension_rows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');
            $table->string('site_url', 2048)->nullable();
            $table->enum('dimension', ['query', 'page']);
            $table->string('key', 2048);

            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->decimal('ctr', 10, 6)->nullable();
            $table->decimal('avg_position', 10, 4)->nullable();

            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'metric_date', 'dimension', 'key'], 'sc_dim_unique');
            $table->index(['client_id', 'metric_date', 'dimension']);
        });

        Schema::create('seo_opportunities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->date('opportunity_date');
            $table->string('opportunity_type', 100);
            $table->string('title');
            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'opportunity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_opportunities');
        Schema::dropIfExists('search_console_dimension_rows');
    }
};
