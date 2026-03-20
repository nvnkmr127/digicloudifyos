<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'contacts',
            'pipelines',
            'pipeline_stages',
            'opportunities',
            'conversations',
            'messages',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'organization_id')) {
                        $table->foreignUuid('organization_id')->nullable()->after('id')->constrained('organizations')->cascadeOnDelete();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'contacts',
            'pipelines',
            'pipeline_stages',
            'opportunities',
            'conversations',
            'messages',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'organization_id')) {
                        $table->dropForeign(['organization_id']);
                        $table->dropColumn('organization_id');
                    }
                });
            }
        }
    }
};
