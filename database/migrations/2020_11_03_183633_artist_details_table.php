<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArtistDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('artist_details', function(Blueprint $table) {
            $table->id('id');
            $table->foreignId('user_id')->index();
            $table->string('corresponding_address');
            $table->string('permanent_address');
            $table->string('country');
            $table->string('zip_code');
            $table->string('profile_picture');
            $table->string('cover_picture');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')  
                ->on('users')
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
        Schema::dropIfExists('artist_details');
    }
}
