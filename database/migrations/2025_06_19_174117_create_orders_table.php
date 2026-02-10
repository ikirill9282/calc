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
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->startingValue(100500);
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('agent_id')->unsigned();
            $table->string('payment_method')->default('cash');
            $table->decimal('pick')->nullable()->index();
            $table->decimal('delivery')->nullable()->index();
            $table->decimal('additional')->nullable()->index();
            $table->decimal('total')->nullable()->index();
            $table->string('warehouse_id')->index();
            $table->string('distributor_id')->index();
            $table->string('distributor_center_id')->index();
            $table->timestamp('delivery_date')->index();
            $table->timestamp('post_date')->index();
            $table->string('transfer_method');
            $table->timestamp('transfer_method_receive_date')->nullable();
            $table->string('transfer_method_pick_address')->nullable();
            $table->timestamp('transfer_method_pick_date')->nullable();
            $table->string('cargo');
            // $table->tinyInteger('boxes')->nullable();
            $table->integer('boxes_count')->nullable();
            $table->integer('boxes_volume')->nullable();
            $table->integer('boxes_weight')->nullable();
            // $table->tinyInteger('pallets')->nullable();
            $table->integer('pallets_count')->nullable();
            $table->integer('pallets_weight')->nullable();
            $table->text('cargo_comment')->nullable();
            $table->string('cargo_type')->nullable();
            $table->string('palletizing_type')->nullable();
            $table->integer('palletizing_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('agents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
