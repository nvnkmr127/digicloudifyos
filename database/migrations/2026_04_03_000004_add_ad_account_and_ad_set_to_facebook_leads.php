<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facebook_leads')) {
            return;
        }

        Schema::table('facebook_leads', function (Blueprint $table) {
            if (! Schema::hasColumn('facebook_leads', 'ad_account_id')) {
                $table->foreignUuid('ad_account_id')->nullable()->constrained('ad_accounts')->nullOnDelete()->after('form_name');
                $table->index(['ad_account_id', 'created_at']);
            }

            if (! Schema::hasColumn('facebook_leads', 'ad_set_id')) {
                $table->uuid('ad_set_id')->nullable()->after('campaign_id');
                $table->foreign('ad_set_id')->references('id')->on('ad_sets')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
    }
};

