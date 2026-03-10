<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('social_channel_id')->nullable()->constrained('social_channels')->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->text('content')->nullable();
            $table->json('media_urls')->nullable(); // Array of URLs
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('external_post_id')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, publishing, published, failed
            $table->text('error_message')->nullable();
            $table->json('metrics')->nullable(); // likes, comments, shares, etc.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
