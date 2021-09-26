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
            'publish_end_date' => $job->publish_end_date,
            'publish_start_date' => $job->publish_start_date,
            'jobEndDate' => $job->jobEndDate,
            'jobStartDate' => $job->jobStartDate,
            'job_location' => $job->job_location,
            'job_description' => $job->job_description,
            'job_tags' => $job->job_tags,
            'vacancies' => $job->vacancies,
            'job_duration' => $job->job_duration,
            'gender' => $job->gender,
            'age_to' =>  $job->age_to,
            'age_from' =>  $job->age_from,
            'city_leaving' => $job->city_leaving,
            'language' => $job->language,
            'physical_attribute' => $job->physical_attribute,
            'experience' => $job->experience,
            'education' => $job->education,
            'budget_from' => $job->budget_from,
            'budget_to' => $job->budget_to,
            'details' => $job->details,
            'subscription_type' => $job->subscription_type,
            'user' => $job->user,
            'profession' => $job->profession,
            'expertise' => $job->expertise,
            'category' => $job->category,
            'language' => $job->language,
            'job_type' => $job->job_type,
            'artist_based_in' => $job->artist_based_in,
            'audition_required' => $job->audition_required,
            'audition_script' => $job->audition_script,
            'subscription_type' => $job->subscription_type,
     ];

    }
    
}

?>