<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\AppliedJobsRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\AppliedJobs; 
use App\Models\ArtistDetails; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{AppliedJobsExport};
use App\Imports\AppliedJobsImport;
use Illuminate\Support\Collection;

class EloquentAppliedJobsRepository implements AppliedJobsRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return AppliedJobs::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return AppliedJobs::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return AppliedJobs::where($key, $value)->first();
    }
    
    /**
    * {@inheritdoc}
    */

    /**
    * {@inheritdoc}
    */
    public function findBy($user_id = [])
    {
        // return MasterFile::where('email', $email)->first();
    }

    public function save(array $data)
    {   
        $user_id = $data['user_id'];   
        $job_id = $data['job_id'];

        $c_data = [];
        $c_data['user_id'] = $user_id;
        $c_data['job_id'] = $job_id;

        $appliedJobs = AppliedJobs::create($c_data);

        return $appliedJobs;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $user_id = $data['user_id'];   
        $country = $data['country'];
        $zip_code = $data['zip_code'];
        $corresponding_address = $data['corresponding_address'];
        $permanent_address = $data['permanent_address'];
        $profile_picture = $data['profile_picture'];
        $cover_picture = $data['cover_picture'];

        $d_data = [];
        $d_data['user_id'] = $user_id;
        $d_data['country'] = $country;
        $d_data['zip_code'] = $zip_code;
        $d_data['corresponding_address'] = $corresponding_address;
        $d_data['permanent_address'] = $permanent_address;
        $d_data['profile_picture'] = $profile_picture;
        $d_data['cover_picture'] = $cover_picture;

        $artistDetails = ArtistDetails::where('id', $id)->update($d_data);  

        return $artistDetails;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $artistDetails = $this->find($id);

        if(!$artistDetails instanceof ArtistDetails) {
            return false;
        }
        
        return $artistDetails->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return AppliedJobs::select('applied_jobs.*')
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
        ->whereNull('applied_jobs.deleted_at');
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
