<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type'); // daily, campaign, audience, creative
            $table->string('format'); // pdf, excel
            $table->string('file_path')->nullable();
            $table->json('parameters')->nullable(); // date range, etc.
            $table->string('status')->default('PENDING'); // PENDING, PROCESSING, COMPLETED, FAILED
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
