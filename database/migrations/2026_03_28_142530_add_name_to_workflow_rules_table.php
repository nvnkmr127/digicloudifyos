<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_rules', 'name')) {
                $table->string('name')->nullable()->after('organization_id');
                $table->string('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('workflow_rules', 'conditions')) {
                $table->json('conditions')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workflow_rules', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'conditions']);
        });
    }
};
