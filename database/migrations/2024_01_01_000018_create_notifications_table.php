<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('trigger', ['new_alert', 'new_task', 'campaign_issue']);
            $table->string('title', 180);
            $table->text('message');
            $table->json('metadata')->default('{}');
            $table->string('channels');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['organization_id', 'created_at'], 'notifications_org_created_idx');
            $table->index(['organization_id', 'is_read'], 'notifications_org_is_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
