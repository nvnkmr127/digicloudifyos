<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (! Schema::hasTable('order_items') || ! Schema::hasColumn('order_items', 'product_id')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            try {
                $table->dropForeign(['product_id']);
            } catch (Throwable $e) {
            }
            $table->unsignedBigInteger('product_id')->change();
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void {}
};
