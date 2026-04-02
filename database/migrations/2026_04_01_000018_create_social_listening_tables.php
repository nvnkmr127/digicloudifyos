<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_listening_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_competitor_id')->nullable()->constrained('client_competitors')->nullOnDelete();

            $table->string('source_type', 50); // rss|webhook|twitter_search|manual
            $table->string('source_label')->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->string('query')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'source_type']);
        });

        Schema::create('social_listening_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_competitor_id')->nullable()->constrained('client_competitors')->nullOnDelete();

            $table->string('source_type', 50);
            $table->string('external_id', 255)->nullable();
            $table->string('title')->nullable();
            $table->string('url', 2048)->nullable();
            $table->text('content')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->date('event_date');
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'client_id', 'source_type', 'external_id'], 'social_event_unique');
            $table->index(['client_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_listening_events');
        Schema::dropIfExists('social_listening_sources');
    }
};
