<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('audience_insights', function (Blueprint $table) {
            $table->string('age')->nullable()->after('breakdown_type');
            $table->string('gender')->nullable()->after('age');
            $table->string('country')->nullable()->after('gender');
            $table->string('city')->nullable()->after('country');
            $table->string('device')->nullable()->after('city');
            $table->string('placement')->nullable()->after('device');
            $table->string('hour')->nullable()->after('placement');
        });
    }

    public function down(): void
    {
        Schema::table('audience_insights', function (Blueprint $table) {
            $table->dropColumn(['age', 'gender', 'country', 'city', 'device', 'placement', 'hour']);
        });
    }
};
