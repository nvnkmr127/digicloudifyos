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
        if (! Schema::hasTable('audience_insights') || Schema::hasColumn('audience_insights', 'leads')) {
            return;
        }

        Schema::table('audience_insights', function (Blueprint $table) {
            $table->integer('leads')->default(0)->after('conversions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('audience_insights') || ! Schema::hasColumn('audience_insights', 'leads')) {
            return;
        }

        Schema::table('audience_insights', function (Blueprint $table) {
            $table->dropColumn('leads');
        });
    }
};
