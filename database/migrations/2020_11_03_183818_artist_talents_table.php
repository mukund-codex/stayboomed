<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArtistTalentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('artist_talent', function(Blueprint $table) {
            $table->id('id');
            $table->foreignId('user_id')->index();
            $table->string('waist');
            $table->string('height');
            $table->string('bust');
            $table->string('eye_color');
            $table->string('hair_color');
            $table->string('skin_color');
            $table->string('hair_length');
            $table->string('hair_type');
            $table->string('interest');
            $table->string('awards');
            $table->string('experience');
            $table->string('role_type');
            $table->string('worked_type');
            $table->string('memberships');
            $table->string('available_for');
            $table->string('relocate');
            $table->string('trained_id');
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
        Schema::dropIfExists('artist_talent');
    }
}
