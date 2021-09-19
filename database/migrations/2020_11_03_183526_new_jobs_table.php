<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('new_jobs', function(Blueprint $table) {
            $table->id('id');
            $table->foreignId('user_id');
            $table->string('title');
            $table->date('publish_date');
            $table->date('end_date');
            $table->string('job_location');
            $table->string('job_description');
            $table->string('job_tags');
            $table->string('vacancies');
            $table->string('job_duration');
            $table->json('gender');
            $table->string('age');
            $table->string('city_leaving');
            $table->string('physical_attribute');
            $table->string('experience');
            $table->string('education');
            $table->foreignId('profession_id');
            $table->string('subscription_type');
            $table->string('budget');
            $table->string('budget_time');
            $table->string('details');
            $table->json('expertise');
            $table->json('category');
            $table->json('language');
            $table->json('job_type');
            $table->json('other_categories');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('profession_id')
                ->references('id')
                ->on('profession_master')
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
        Schema::dropIfExists('new_jobs');
    }
}
