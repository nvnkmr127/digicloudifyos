<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('forms') && Schema::hasColumn('forms', 'organization_id')) {
            Schema::table('forms', function (Blueprint $table) {
                try {
                    $table->dropForeign(['organization_id']);
                } catch (Throwable $e) {
                }
                $table->uuid('organization_id')->change();
                $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('feedback') && Schema::hasColumn('feedback', 'organization_id')) {
            Schema::table('feedback', function (Blueprint $table) {
                try {
                    $table->dropForeign(['organization_id']);
                } catch (Throwable $e) {
                }
                try {
                    $table->dropForeign(['user_id']);
                } catch (Throwable $e) {
                }
                $table->uuid('organization_id')->change();
                $table->uuid('user_id')->nullable()->change();
                $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'organization_id')) {
            Schema::table('orders', function (Blueprint $table) {
                try {
                    $table->dropForeign(['organization_id']);
                } catch (Throwable $e) {
                }
                try {
                    $table->dropForeign(['client_id']);
                } catch (Throwable $e) {
                }
                $table->uuid('organization_id')->change();
                $table->uuid('client_id')->nullable()->change();
                $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
                $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            });
        }
    }

    public function down(): void {}
};
