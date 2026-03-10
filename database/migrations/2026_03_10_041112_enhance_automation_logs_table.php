<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('automation_logs', function (Blueprint $table) {
            $table->string('event_type')->nullable()->after('event_id');
            $table->timestamp('executed_at')->nullable()->after('status');
            $table->json('details')->nullable()->after('result');
            $table->text('error_message')->nullable()->after('details');
        });
    }

    public function down(): void
    {
    }
};
