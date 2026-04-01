<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_channel_connections', function (Blueprint $table) {
            $table->string('channel_type', 100)->change();
            $table->foreignUuid('integration_credential_id')
                ->nullable()
                ->after('client_id')
                ->constrained('integration_credentials')
                ->nullOnDelete();

            $table->timestamp('sync_disabled_at')->nullable()->after('last_synced_at');
            $table->string('last_sync_status', 20)->nullable()->after('sync_disabled_at');
        });
    }

    public function down(): void
    {
        Schema::table('client_channel_connections', function (Blueprint $table) {
            $table->dropForeign(['integration_credential_id']);
            $table->dropColumn('integration_credential_id');
            $table->dropColumn(['sync_disabled_at', 'last_sync_status']);
            $table->enum('channel_type', [
                'meta_ads',
                'google_ads',
                'linkedin_ads',
                'ga4',
                'instagram',
                'facebook_organic',
                'inbound_webhook'
            ])->change();
        });
    }
};
