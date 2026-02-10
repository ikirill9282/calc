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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('boxes_volume', 12, 2)->nullable()->change();
            $table->decimal('boxes_weight', 12, 2)->nullable()->change();
            $table->decimal('pallets_volume', 12, 2)->nullable()->change();
            $table->decimal('pallets_weight', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('boxes_volume', 8, 4)->nullable()->change();
            $table->decimal('boxes_weight', 8, 4)->nullable()->change();
            $table->integer('pallets_volume')->nullable()->change();
            $table->integer('pallets_weight')->nullable()->change();
        });
    }
};
