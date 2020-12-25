<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\PaidUsersRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\PaidUsers; 
use App\Models\ArtistDetails; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{PaidUsersExport};
use App\Imports\PaidUsersImport;
use Illuminate\Support\Collection;

class EloquentPaidUsersRepository implements PaidUsersRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return PaidUsers::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return PaidUsers::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return PaidUsers::where($key, $value)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function getDetails(array $data)
    {
        // DB::enableQueryLog();
        return PaidUsers::select('paid_users.id','paid_users.user_id','paid_users.provider_id','paid_users.deleted_at')->where($data)->first();
        
        //  $query = DB::getQueryLog(); 
        //  print_r($query);exit;

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
        $provider_id = $data['provider_id'];

        $c_data = [];
        $c_data['user_id'] = $user_id;
        $c_data['provider_id'] = $provider_id;

        $paidUsers = PaidUsers::create($c_data);

        return $paidUsers;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $user_id = $data['user_id'];   
        $provider_id = $data['provider_id'];

        $d_data = [];
        $d_data['user_id'] = $user_id;
        $d_data['provider_id'] = $provider_id;

        $paidUsers = PaidUsers::where('id', $id)->update($d_data);  

        return $paidUsers;
    
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

        return PaidUsers::select('paid_users.*')
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
        ->whereNull('paid_users.deleted_at');
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
