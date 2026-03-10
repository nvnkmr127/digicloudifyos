<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ad_creatives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->uuid('ad_account_id'); // Local UUID
            $table->string('external_creative_id')->unique();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('call_to_action_type')->nullable();
            $table->json('object_story_spec')->nullable();
            $table->json('asset_data')->nullable(); // Store extra fields
            $table->timestamps();

            $table->foreign('ad_account_id')->references('id')->on('ad_accounts')->cascadeOnDelete();
        });

        // Add foreign key to ads table if needed, or just keep external_creative_id
        Schema::table('ads', function (Blueprint $table) {
            if (!Schema::hasColumn('ads', 'ad_creative_id')) {
                $table->uuid('ad_creative_id')->nullable()->after('ad_set_id');
                $table->foreign('ad_creative_id')->references('id')->on('ad_creatives')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropForeign(['ad_creative_id']);
            $table->dropColumn('ad_creative_id');
        });
        Schema::dropIfExists('ad_creatives');
    }
};
