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
        Schema::create('order_prints', function (Blueprint $table) {
            $table->bigInteger('order_id')->unsigned()->unique();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_prints');
    }
};
// * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

// root@3432267-cs37647:/home/lk.skif-logistik.ru/public_html/account# /usr/local/lsws/lsphp82/bin/php artisan migrate:refresh