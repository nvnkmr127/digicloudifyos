<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_competitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();

            $table->string('platform', 50);
            $table->string('identifier', 255);
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'platform', 'identifier'], 'client_competitors_unique');
            $table->index(['client_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_competitors');
    }
};
