<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('facebook_leads', function (Blueprint $table) {
            $table->uuid('ad_set_id')->nullable()->after('campaign_id');
            $table->foreign('ad_set_id')->references('id')->on('ad_sets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('facebook_leads', function (Blueprint $table) {
            $table->dropForeign(['ad_set_id']);
            $table->dropColumn('ad_set_id');
        });
    }
};
