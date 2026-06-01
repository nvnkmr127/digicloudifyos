<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'trigger')) {
            if (! Schema::hasTable('app_notifications')) {
                Schema::rename('notifications', 'app_notifications');
            }
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->uuidMorphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && ! Schema::hasColumn('notifications', 'trigger')) {
            Schema::drop('notifications');
        }

        if (Schema::hasTable('app_notifications') && ! Schema::hasTable('notifications')) {
            Schema::rename('app_notifications', 'notifications');
        }
    }
};
