<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creative_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('creative_request_id')->constrained()->cascadeOnDelete();
            $table->text('feedback');
            $table->foreignUuid('commented_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('creative_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creative_feedback');
    }
};
