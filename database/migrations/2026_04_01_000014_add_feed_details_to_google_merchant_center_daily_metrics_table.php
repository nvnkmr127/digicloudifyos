<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_merchant_center_daily_metrics', function (Blueprint $table) {
            $table->unsignedBigInteger('feed_count')->default(0)->after('issue_breakdown');
            $table->unsignedBigInteger('feed_issue_count')->default(0)->after('feed_count');
            $table->json('feed_statuses')->nullable()->after('feed_issue_count');
            $table->json('top_issue_examples')->nullable()->after('feed_statuses');
        });
    }

    public function down(): void
    {
        Schema::table('google_merchant_center_daily_metrics', function (Blueprint $table) {
            $table->dropColumn([
                'feed_count',
                'feed_issue_count',
                'feed_statuses',
                'top_issue_examples',
            ]);
        });
    }
};

