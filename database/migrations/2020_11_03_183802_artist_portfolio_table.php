<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArtistPortfolioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('artist_porfolio', function(Blueprint $table) {
            $table->id('id');
            $table->foreignId('user_id')->index();
            $table->string('audio_title');
            $table->string('audio_file');
            $table->string('video_title');
            $table->string('video_file');
            $table->string('picture_title');
            $table->string('picture_file');
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
        Schema::dropIfExists('artist_profolio');
    }
}
