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
        Schema::table('ad_accounts', function (Blueprint $table) {
            $table->decimal('target_cpl', 10, 2)->nullable()->after('facebook_page_token');
            $table->decimal('target_ctr', 5, 2)->nullable()->after('target_cpl');
            $table->decimal('target_frequency', 5, 2)->nullable()->after('target_ctr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_accounts', function (Blueprint $table) {
            $table->dropColumn(['target_cpl', 'target_ctr', 'target_frequency']);
        });
    }
};
