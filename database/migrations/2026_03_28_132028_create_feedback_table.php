<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type')->nullable(); // project, creative_request, lead
            $table->uuid('entity_id')->nullable();
            $table->integer('rating')->default(5);
            $table->text('comment');
            $table->string('status')->default('PENDING'); // PENDING, REVIEWED, ARCHIVED
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
