<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change enum to string for extensibility
        Schema::table('workflow_rules', function (Blueprint $table) {
            $table->string('event_type')->change();
            $table->string('action_type')->change();
        });

        Schema::table('workflow_events', function (Blueprint $table) {
            $table->string('event_type')->change();
        });

        Schema::table('workflow_actions', function (Blueprint $table) {
            $table->string('action_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
