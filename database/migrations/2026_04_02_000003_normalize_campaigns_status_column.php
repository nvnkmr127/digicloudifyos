<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaigns') || ! Schema::hasColumn('campaigns', 'status')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE campaigns MODIFY status VARCHAR(50) NOT NULL DEFAULT 'planning'");
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE campaigns ALTER COLUMN status TYPE VARCHAR(50)');
            DB::statement("ALTER TABLE campaigns ALTER COLUMN status SET DEFAULT 'planning'");
        }

        if (in_array($driver, ['mysql', 'pgsql'], true)) {
            DB::statement("
                UPDATE campaigns
                SET status = CASE status
                    WHEN 'ACTIVE' THEN 'running'
                    WHEN 'INACTIVE' THEN 'planning'
                    WHEN 'ARCHIVED' THEN 'completed'
                    ELSE status
                END
            ");
        }
    }

    public function down(): void {}
};
