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
      Schema::table('sheet_data', function (Blueprint $table) {
          $table->string('payment_method_pick')->default('cash')->nullable()->change();
          $table->string('payment_method')->default('cash')->nullable()->change();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('sheet_data', function (Blueprint $table) {
          $table->string('payment_method_pick')->default('cash')->change();
          $table->string('payment_method')->default('cash')->change();
      });
    }
};
