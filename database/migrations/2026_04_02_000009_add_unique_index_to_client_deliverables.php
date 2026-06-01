<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_deliverables')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('
                DELETE d1 FROM client_deliverables d1
                INNER JOIN client_deliverables d2
                  ON d1.organization_id = d2.organization_id
                 AND d1.client_id = d2.client_id
                 AND d1.deliverable_template_id = d2.deliverable_template_id
                 AND d1.deliverable_date = d2.deliverable_date
                 AND d1.created_at > d2.created_at
            ');
        }

        if ($driver === 'pgsql') {
            DB::statement('
                DELETE FROM client_deliverables d1
                USING client_deliverables d2
                WHERE d1.organization_id = d2.organization_id
                  AND d1.client_id = d2.client_id
                  AND d1.deliverable_template_id = d2.deliverable_template_id
                  AND d1.deliverable_date = d2.deliverable_date
                  AND d1.created_at > d2.created_at
            ');
        }

        Schema::table('client_deliverables', function (Blueprint $table) {
            $table->unique(
                ['organization_id', 'client_id', 'deliverable_template_id', 'deliverable_date'],
                'client_deliverables_unique_per_day'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_deliverables')) {
            return;
        }

        Schema::table('client_deliverables', function (Blueprint $table) {
            $table->dropUnique('client_deliverables_unique_per_day');
        });
    }
};
