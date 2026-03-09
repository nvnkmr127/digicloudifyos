<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creative_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'carousel', 'video', 'banner']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority')->default('medium');
            $table->enum('status', ['requested', 'in_production', 'review', 'approved', 'rejected', 'published'])->default('requested');
            $table->timestamp('deadline')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('assigned_to');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creative_requests');
    }
};
