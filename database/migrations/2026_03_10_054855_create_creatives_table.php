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
        Schema::create('creatives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('ad_id')->constrained()->cascadeOnDelete();
            $table->string('creative_id'); // External ID from Meta
            $table->string('headline')->nullable();
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();
            $table->string('video_id')->nullable();
            $table->string('cta')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creatives');
    }
};
