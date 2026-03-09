<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_rule_id')->constrained()->cascadeOnDelete();
            $table->enum('action_type', ['create_task', 'update_campaign_status', 'send_notification']);
            $table->json('config')->default('{}');
            $table->timestamps();

            $table->index('workflow_rule_id');
            $table->index('action_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_actions');
    }
};
