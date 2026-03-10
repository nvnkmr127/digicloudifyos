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
        Schema::create('ads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('ad_set_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('external_ad_id')->unique();
            $table->string('status')->nullable();
            $table->string('external_creative_id')->nullable();
            $table->json('creative_data')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'ad_set_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
