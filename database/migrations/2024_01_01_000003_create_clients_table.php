<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('external_ref')->nullable();
            $table->string('industry')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'ARCHIVED'])->default('ACTIVE');
            $table->timestamps();

            $table->unique(['organization_id', 'external_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
