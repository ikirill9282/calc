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
          $table->tinyInteger('individual')->default(0)->after('payment_method_pick');
          $table->tinyInteger('palletizing_count')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
          $table->dropColumn('individual');
          $table->tinyInteger('palletizing_count')->nullable(null)->default(0)->change();
        });
    }
};
