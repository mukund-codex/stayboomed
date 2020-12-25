<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('users', function (Blueprint $table) {
            $table->id('id');
            $table->string('fullname');
            $table->string('designation')->nullable();
            $table->string('organisation')->nullable();
            $table->foreignId('profession_id')->nullable();
            $table->string('email')->unique();
            $table->string('user_type');
            $table->rememberToken();
            $table->string('password');
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('number')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('state_id')->nullable();
            $table->foreignId('city_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('profession_id')
                ->references('id')
                ->on('profession_master')
                ->onDelete('restrict')
                ->onUpdate('cascade'); 

            $table->foreign('state_id')
                ->references('id')
                ->on('state_master')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('city_id')
                ->references('id')
                ->on('city_master')
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
        Schema::dropIfExists('users');
    }
}
