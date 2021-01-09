<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\ReferRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\Refer; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{ReferExport};
use App\Imports\ReferImport;
use Illuminate\Support\Collection;

class EloquentReferRepository implements ReferRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return Refer::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return Refer::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return Refer::where($key, $value)->first();
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
        $referee_id = $data['referee_id'];
        $referral_code = $data['referral_code'];
        $user_id = $data['user_id'];
            
        $c_data = [];
        $c_data['referee_id'] = $referee_id;
        $c_data['referral_code'] = $referral_code;
        $c_data['user_id'] = $user_id;

        $state = Refer::create($c_data);

        return $state;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $name = $data['name'];   
        
        $d_data = [];
        $d_data['name'] = $name;

        $state = Refer::where('id', $id)->update($d_data);  

        return $state;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $state = $this->find($id);

        if(!$state instanceof State) {
            return false;
        }
        
        return $state->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return Refer::select('referrals.*')
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
        ->whereNull('referrals.deleted_at')
        ->orderBy('referrals.id','asc');
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
