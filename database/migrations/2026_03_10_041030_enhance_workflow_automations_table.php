<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workflow_rules', function (Blueprint $table) {
            $table->string('name')->after('organization_id');
            $table->string('event_type')->change();
            $table->string('action_type')->nullable()->change();
            $table->json('conditions')->nullable()->after('event_type');
            $table->text('description')->nullable()->after('name');
        });

        Schema::table('workflow_actions', function (Blueprint $table) {
            $table->string('action_type')->change();
        });

        Schema::table('workflow_events', function (Blueprint $table) {
            $table->string('event_type')->change();
            $table->string('entity_type')->change();
        });
    }

    public function down(): void
    {
        // No down migration needed for this change as we are just loosening types
    }
};
