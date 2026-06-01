<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->boolean('is_published')->default(false)->after('status');
            $table->string('public_key')->nullable()->after('is_published');
            $table->unique('slug');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable()->after('form_id');
            $table->string('user_agent')->nullable()->after('status');
            $table->string('referer')->nullable()->after('user_agent');
            $table->timestamp('submitted_at')->nullable()->after('referer');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropIndex(['organization_id']);
            $table->dropColumn(['organization_id', 'user_agent', 'referer', 'submitted_at']);
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'is_published', 'public_key']);
        });
    }
};
