<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\JobRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\Job; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{JobExport};
use App\Imports\JobImport;
use Illuminate\Support\Collection;

class EloquentJobRepository implements JobRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return Job::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return Job::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return Job::where($key, $value)->first();
    }
    
    /**
    * {@inheritdoc}
    */

    /**
    * {@inheritdoc}
    */
    public function findBy($state = [])
    {
        // return MasterFile::where('email', $email)->first();
    }

    public function save(array $data)
    {      
        $user_id = $data['user_id'];
        $title = $data['title'];
        $job_location = $data['job_location'];
        $job_description = $data['job_description'];
        $job_tags = $data['job_tags'];
        $vacancies = $data['vacancies'];
        $job_duration = $data['job_duration'];
        $gender = $data['gender'];
        $subscription_type = $data['subscription_type'];
        $expertise = $data['expertise'];
        $category = $data['category'];
        $language = $data['language'];
        $job_type = $data['job_type'];
        $other_categories = $data['other_categories'];
        
        $publish_start_date = $data['publish_start_date'];
        $publish_end_date = $data['publish_end_date'];
        $jobStartDate = $data['jobStartDate'];
        $jobEndDate = $data['jobEndDate'];
        $age_to = $data['age_to'];
        $age_from = $data['age_from'];
        $budget_from = $data['budget_from'];
        $budget_to = $data['budget_to'];
        $artist_based_in = $data['artist_based_in'];
        $audition_required = $data['audition_required'];
        $audition_script = $data['audition_script'];
        $subscription_type = $data['subscription_type'];



        $c_data = [];
        $c_data['user_id'] = $user_id;
        $c_data['title'] = $title;
        $c_data['job_location'] = $job_location;
        $c_data['job_description'] = $job_description;
        $c_data['job_tags'] = $job_tags;
        $c_data['vacancies'] = $vacancies;
        $c_data['job_duration'] = $job_duration;
        $c_data['gender'] = $gender;
        $c_data['language'] = $language;
        $c_data['subscription_type'] = $subscription_type;
        $c_data['expertise'] = $expertise;
        $c_data['category'] = $category;
        $c_data['language'] = $language;
        $c_data['job_type'] = $job_type;
        $c_data['other_categories'] = $other_categories;

        $c_data['publish_end_date'] = $publish_end_date;
        $c_data['publish_start_date'] = $publish_start_date;
        $c_data['jobEndDate'] = $jobEndDate;
        $c_data['age_to'] = $age_to;
        $c_data['age_from'] = $age_from;
        $c_data['budget_from'] = $budget_from;
        $c_data['budget_to'] = $budget_to;
        $c_data['jobStartDate'] = $jobStartDate;
        $c_data['artist_based_in'] = $artist_based_in;
        $c_data['audition_required'] = $audition_required;
        $c_data['audition_script'] = $audition_script;
        $c_data['subscription_type'] = $subscription_type;

        $job = Job::create($c_data);

        return $job;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $user_id = $data['user_id'];
        $title = $data['title'];
        $publish_date = $data['publish_date'];
        $end_date = $data['end_date'];
        $job_location = $data['job_location'];
        $job_description = $data['job_description'];
        $job_tags = $data['job_tags'];
        $vacancies = $data['vacancies'];
        $job_duration = $data['job_duration'];
        $gender = $data['gender'];
        $age = $data['age'];
        $city_leaving = $data['city_leaving'];
        $physical_attribute = $data['physical_attribute'];
        $experience = $data['experience'];
        $education = $data['education'];
        $profession_id = $data['profession_id'];
        $budget = $data['budget'];
        $budget_time = $data['budget_time'];
        $details = $data['details'];
        $language = $data['language'];
        $subscription_type = $data['subscription_type'];
            
        $d_data = [];
        $d_data['user_id'] = $user_id;
        $d_data['title'] = $title;
        $d_data['publish_date'] = $publish_date;
        $d_data['end_date'] = $end_date;
        $d_data['job_location'] = $job_location;
        $d_data['job_description'] = $job_description;
        $d_data['job_tags'] = $job_tags;
        $d_data['vacancies'] = $vacancies;
        $d_data['job_duration'] = $job_duration;
        $d_data['gender'] = $gender;
        $d_data['age'] = $age;
        $d_data['city_leaving'] = $city_leaving;
        $d_data['physical_attribute'] = $physical_attribute;
        $d_data['experience'] = $experience;
        $d_data['education'] = $education;
        $d_data['profession_id'] = $profession_id;
        $d_data['budget'] = $budget;
        $d_data['budget_time'] = $budget_time;
        $d_data['details'] = $details;
        $d_data['language'] = $language;
        $d_data['subscription_type'] = $subscription_type;

        $job = Job::where('id', $id)->update($d_data);  

        return $job;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $job = $this->find($id);

        if(!$job instanceof Job) {
            return false;
        }
        
        return $job->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return job::select('new_jobs.*')
        ->where(function($q) use ($filter, $operator){
            foreach ($filter as $key => $value) {
                if(\array_key_exists($key, $operator)) {
                    if(\strtoupper($operator[$key]) == "ILIKE") {
                        $q->where($key, $operator[$key], "%$value%");
                        
                    } else {
                        $q->where($key, $operator[$key], "$value");
                        
                    }
                }
            }                        
        })
        ->whereNull('new_jobs.deleted_at')
        ->orderBy('new_jobs.created_at','desc');
    }

    /**
    * {@inheritdoc}
    */
     public function export(Collection $data)
    {
        $date = Carbon::now()->format('Y-m-d-H-s');
        $filename = "course-$date.xls";
        $file = Excel::store(new CourseExport($data), $filename);
        $file_path = Storage::disk('local')->path($filename);

        $headers = [
            "Content-type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=" . $filename,
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        if($file) {
            return new BinaryFileResponse($file_path, 200, $headers);
        }
    }

    

    public function import($request)
    {
        $import = new CourseImport();
        $import->import($request->file('upload_file'));
        $failures = $import->failures();
        
        $data = [];
        $data['count'] = $import->getRowCount();

        $errors = [];
        foreach ($failures as $failure) {
            $failure_list = [];
            $failure_list['row'] = $failure->row();
            $failure_list['attribute'] = $failure->attribute();
            $failure_list['errors'] = $failure->errors();
            $failure_list['values'] = $failure->values();
            array_push($errors, $failure_list);
        }

        return [
            'error' => $errors,
            'data'  => $data
        ];
    }

}
