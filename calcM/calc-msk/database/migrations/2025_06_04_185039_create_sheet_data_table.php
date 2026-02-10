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
        Schema::create('sheet_data', function (Blueprint $table) {
            $table->id();
            $table->string('wh')->index();
            $table->string('wh_address')->nullable();
            $table->string('map')->nullable();
            $table->string('distributor');
            $table->string('distributor_center');
            $table->date('distributor_center_delivery_date');
            $table->timestamp('delivery_diff');
            $table->tinyInteger('delivery_weekend')->default(0)->index();
            $table->timestamp('pick_diff');
            $table->string('pick_weekend')->default(1)->index();
            $table->decimal('pick_tariff_min');
            $table->decimal('pick_tariff_vol');
            $table->decimal('pick_tariff_pallete');
            $table->string('pick_additional')->nullable();
            $table->decimal('delivery_tariff_min');
            $table->decimal('delivery_tariff_vol');
            $table->decimal('delivery_tariff_pallete');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sheet_data');
    }
};
