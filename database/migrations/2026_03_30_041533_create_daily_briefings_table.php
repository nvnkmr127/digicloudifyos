<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_briefings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->date('briefing_date');
            
            $table->enum('status', ['generating', 'ready', 'sent'])->default('generating');
            
            $table->integer('total_clients_analyzed')->default(0);
            $table->integer('critical_alerts_count')->default(0);
            $table->integer('opportunities_count')->default(0);
            
            $table->json('summary')->nullable();
            
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'briefing_date']);
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_briefings');
    }
};
