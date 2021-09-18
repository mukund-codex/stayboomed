<?php 

namespace App\Transformers;

use App\Models\Job;
use League\Fractal\TransformerAbstract;

class JobTransformer extends TransformerAbstract
{
    public function transform(Job $job)
    {   
        return [
            'id'    =>  $job->id,
            'title' => $job->title,
            'publish_date' => $job->publish_date,
            'end_date' => $job->end_date,
            'job_location' => $job->job_location,
            'job_description' => $job->job_description,
            'job_tags' => $job->job_tags,
            'vacancies' => $job->vacancies,
            'job_duration' => $job->job_duration,
            'gender' => $job->gender,
            'age' =>  $job->age,
            'city_leaving' => $job->city_leaving,
            'language' => $job->language,
            'physical_attribute' => $job->physical_attribute,
            'experience' => $job->experience,
            'education' => $job->education,
            'budget' => $job->budget,
            'budget_time' => $job->budget_time,
            'details' => $job->details,
            'subscription_type' => $job->subscription_type,
            'user' => $job->user,
            'profession' => $job->profession,
            'expertise' => $job->expertise,
            'category' => $job->category,
            'language' => $job->language,
            'job_type' => $job->job_type,
            'other_categories' => $job->other_categories
     ];

    }
    
}

?>