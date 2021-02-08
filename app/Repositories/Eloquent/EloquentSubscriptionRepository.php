<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\SubscriptionRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\Subscription; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{SubscriptionExport};
use App\Imports\SubscriptionImport;
use Illuminate\Support\Collection;

class EloquentSubscriptionRepository implements SubscriptionRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return Subscription::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return Subscription::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return Subscription::where($key, $value)->first();
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
        $price = $data['price'];
        $job_apply = $data['job_apply'];
        $subscription_type = $data['subscription_type'];
        $expiry = $data['expiry'];
            
        $c_data = [];
        $c_data['user_id'] = $user_id;
        $c_data['title'] = $title;
        $c_data['price'] = $price;
        $c_data['job_apply'] = $job_apply;
        $c_data['subscription_type'] = $subscription_type;
        $c_data['expiry'] = $expiry;

        $subscription = Subscription::create($c_data);

        return $subscription;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $user_id = $data['user_id'];
        $title = $data['title'];
        $price = $data['price'];
        $job_apply = $data['job_apply'];
        $subscription_type = $data['subscription_type'];
        $expiry = $data['expiry'];
            
        $d_data = [];
        $d_data['user_id'] = $user_id;
        $d_data['title'] = $title;
        $d_data['price'] = $price;
        $d_data['job_apply'] = $job_apply;
        $d_data['subscription_type'] = $subscription_type;
        $d_data['expiry'] = $expiry;

        $subscription = Subscription::where('id', $id)->update($d_data);  

        return $subscription;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $subscription = $this->find($id);

        if(!$subscription instanceof Subscription) {
            return false;
        }
        
        return $subscription->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return Subscription::select('subscription.*')
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
        ->whereNull('subscription.deleted_at')
        ->orderBy('subscription.created_at','desc');
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
