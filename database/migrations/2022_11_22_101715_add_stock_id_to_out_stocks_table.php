<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockIdToOutStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('out_stocks', function (Blueprint $table) {
            $table->bigInteger('stock_id')->unsigned()->after('user_id');
            $table->foreign('stock_id')
                ->references('id')->on('stocks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('out_stocks', function (Blueprint $table) {
            //
        });
    }
}