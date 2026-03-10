<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // facebook, google, etc.
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->timestamps();
        });

        Schema::create('facebook_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('access_token_id')->constrained('access_tokens')->cascadeOnDelete();
            $table->string('facebook_user_id')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('profile_pic')->nullable();
            $table->timestamps();
        });

        Schema::table('ad_accounts', function (Blueprint $table) {
            $table->foreignUuid('access_token_id')->nullable()->after('client_id')->constrained('access_tokens')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ad_accounts', function (Blueprint $table) {
            $table->dropForeign(['access_token_id']);
            $table->dropColumn('access_token_id');
        });
        Schema::dropIfExists('facebook_users');
        Schema::dropIfExists('access_tokens');
    }
};
