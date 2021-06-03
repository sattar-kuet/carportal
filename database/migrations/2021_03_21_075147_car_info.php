<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CarInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->increments('id');
                        $table->integer('ba_no');
                        $table->integer('model_id');
                        $table->integer('unit_id');
                        $table->string('work_order_no');
                        $table->integer('km');
                        $table->string('problem');
                        $table->string('note');
                        $table->integer('status_id');
                        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cars');
    }
}
