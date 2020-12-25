<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CityMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('city_master', function(Blueprint $table){
            $table->id('id');
            $table->string('name');
            $table->foreignId('state_id')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('state_id')
                ->references('id')
                ->on('state_master')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('city_master');
    }
}
