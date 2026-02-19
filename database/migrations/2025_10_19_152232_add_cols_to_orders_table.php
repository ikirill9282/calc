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
          $table->integer('pallets_boxcount')->nullable()->after('pallets_weight');
          $table->decimal('pallets_volume')->nullable()->after('pallets_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
          $table->dropColumn('pallets_boxcount');
          $table->dropColumn('pallets_volume');
        });
    }
};
