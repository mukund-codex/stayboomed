<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\FeedbackRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\Feedback; 
use App\Models\ArtistDetails; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{FeedbackExport};
use App\Imports\FeedbackImport;
use Illuminate\Support\Collection;

class EloquentFeedbackRepository implements FeedbackRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return Feedback::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return Feedback::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return Feedback::where($key, $value)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function getDetails(array $data)
    {
        
        // return Feedback::select('paid_users.id','paid_users.user_id','paid_users.provider_id','paid_users.deleted_at')->where($data)->first();

    }

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
        $ratings = $data['ratings'];
        $comments = $data['comments'];

        $c_data = [];
        $c_data['user_id'] = $user_id;
        $c_data['ratings'] = $ratings;
        $c_data['comments'] = $comments;

        $feedbacks = Feedback::create($c_data);

        return $feedbacks;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $user_id = $data['user_id'];   
        $ratings = $data['ratings'];
        $comments = $data['comments'];

        $d_data = [];
        $d_data['user_id'] = $user_id;
        $d_data['ratings'] = $ratings;
        $d_data['comments'] = $comments;

        $feedbacks = Feedback::where('id', $id)->update($d_data);  

        return $feedbacks;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $feedbackDetails = $this->find($id);

        if(!$feedbackDetails instanceof Feedback) {
            return false;
        }
        
        return $feedbackDetails->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return Feedback::select('feedbacks.*')
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
        ->whereNull('feedbacks.deleted_at');
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
