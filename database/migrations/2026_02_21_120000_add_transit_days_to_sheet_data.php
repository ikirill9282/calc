<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sheet_data', function (Blueprint $table) {
            $table->unsignedSmallInteger('transit_days')->nullable()->after('distributor_address');
        });
    }

    public function down(): void
    {
        Schema::table('sheet_data', function (Blueprint $table) {
            $table->dropColumn('transit_days');
        });
    }
};
