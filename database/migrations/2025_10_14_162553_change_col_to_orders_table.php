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
          $table->decimal('pick', 12, 2)->nullable()->index();
          $table->decimal('delivery', 12, 2)->nullable()->index();
          $table->decimal('additional', 12, 2)->nullable()->index();
          $table->decimal('total', 12, 2)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
