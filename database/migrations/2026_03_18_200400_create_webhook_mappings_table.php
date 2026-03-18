<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('direction');
            $table->string('name');
            $table->string('source_key');
            $table->string('target_key');
            $table->string('transform_rule')->nullable();
            $table->foreignUuid('webhook_id')->nullable()->constrained('webhooks')->nullOnDelete();
            $table->foreignUuid('inbound_webhook_id')->nullable()->constrained('inbound_webhooks')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'direction', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_mappings');
    }
};
