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
            $table->json('delivery_weekdays_config')->nullable()->after('distributor_center_delivery_date');
            $table->json('shipment_weekdays_config')->nullable()->after('delivery_weekdays_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sheet_data', function (Blueprint $table) {
            $table->dropColumn(['delivery_weekdays_config', 'shipment_weekdays_config']);
        });
    }
};

