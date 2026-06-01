<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audience_insights')) {
            return;
        }

        Schema::table('audience_insights', function (Blueprint $table) {
            if (! Schema::hasColumn('audience_insights', 'age')) {
                $table->string('age')->nullable()->after('breakdown_type');
            }
            if (! Schema::hasColumn('audience_insights', 'gender')) {
                $table->string('gender')->nullable()->after('age');
            }
            if (! Schema::hasColumn('audience_insights', 'country')) {
                $table->string('country')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('audience_insights', 'city')) {
                $table->string('city')->nullable()->after('country');
            }
            if (! Schema::hasColumn('audience_insights', 'device')) {
                $table->string('device')->nullable()->after('city');
            }
            if (! Schema::hasColumn('audience_insights', 'placement')) {
                $table->string('placement')->nullable()->after('device');
            }
            if (! Schema::hasColumn('audience_insights', 'hour')) {
                $table->string('hour')->nullable()->after('placement');
            }
            if (! Schema::hasColumn('audience_insights', 'leads')) {
                $table->integer('leads')->default(0)->after('conversions');
            }
        });
    }

    public function down(): void {}
};
