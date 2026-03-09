<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creative_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('creative_request_id')->constrained()->cascadeOnDelete();
            $table->string('file_url');
            $table->enum('file_type', ['image', 'video', 'design_source', 'preview']);
            $table->integer('version');
            $table->foreignUuid('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['creative_request_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creative_assets');
    }
};
