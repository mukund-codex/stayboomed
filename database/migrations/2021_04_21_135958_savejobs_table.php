<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SavejobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('saved_jobs', function(Blueprint $table) {
            $table->id('id');
            $table->foreignId('user_id')->index();
            $table->foreignId('job_id')->index();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('job_id')
                ->references('id')
                ->on('new_jobs')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        }) ;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('saved_jobs');
    }
}
