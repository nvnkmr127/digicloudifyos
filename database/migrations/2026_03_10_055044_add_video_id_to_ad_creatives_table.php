<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ad_creatives', function (Blueprint $table) {
            if (!Schema::hasColumn('ad_creatives', 'video_id')) {
                $table->string('video_id')->nullable()->after('image_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_creatives', function (Blueprint $table) {
            $table->dropColumn('video_id');
        });
    }
};
