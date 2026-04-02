<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('industry', 100)->nullable();
            $table->enum('cadence', ['weekly', 'monthly', 'quarterly'])->default('monthly');
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 1-7 (Mon-Sun)
            $table->unsignedTinyInteger('day_of_month')->nullable(); // 1-28
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable(); // {playbook_template_ids: []}
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index(['organization_id', 'industry']);
        });

        Schema::create('client_service_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('service_package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('started_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'service_package_id'], 'client_service_pkg_unique');
            $table->index(['client_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_service_packages');
        Schema::dropIfExists('service_packages');
    }
};
