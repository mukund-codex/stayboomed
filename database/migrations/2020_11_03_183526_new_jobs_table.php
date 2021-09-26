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
            $table->string('title')->nullable();
            $table->date('publish_start_date')->nullable();
            $table->date('publish_end_date')->nullable();
            $table->string('job_location');
            $table->string('job_description');
            $table->string('job_tags');
            $table->string('vacancies');
            $table->string('job_duration');
            $table->json('gender');
            $table->string('age_from');
            $table->string('age_to');
            $table->string('city_leaving');
            $table->string('budget_from');
            $table->string('budget_to');
            $table->string('physical_attribute');
            $table->string('experience');
            $table->string('education');
            $table->string('job_start_date');
            $table->string('job_end_date');
            $table->string('subscription_type');
            $table->boolean('audition_required')->default(0);
            $table->boolean('audition_script')->nullable();
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
