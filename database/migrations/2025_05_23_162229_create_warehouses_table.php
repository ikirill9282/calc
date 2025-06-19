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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('phone')->nullable();
            $table->integer('delivery_date')->default(1);
            $table->tinyInteger('delivery_weekend')->default(0);
            $table->integer('pick_date')->default(1);
            $table->tinyInteger('pick_weekend')->default(0);
            
            $table->decimal('tariff_pick');

            $table->decimal('tariff_delivery');

            $table->text('address')->nullable();
            $table->string('slug');

            $table->tinyInteger('disabled')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
