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
        Schema::create('distributor_centers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('distributor_id')->unsigned()->index();
            $table->bigInteger('manager_id')->unsigned()->index();
            $table->string('title');
            $table->string('address')->nullable();
            $table->string('slug');
            $table->timestamps();

            $table->unique(['distributor_id', 'title']);

            $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('managers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_centers');
    }
};
