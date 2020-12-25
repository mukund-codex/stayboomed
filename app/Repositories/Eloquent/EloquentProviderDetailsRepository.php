<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\ProviderDetailsRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\ProviderDetails; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{ProviderDetailsExport};
use App\Imports\ProviderDetailsImport;
use Illuminate\Support\Collection;

class EloquentProviderDetailsRepository implements ProviderDetailsRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return ProviderDetails::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return ProviderDetails::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return ProviderDetails::where($key, $value)->first();
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
        $country = $data['country'];
        $zip_code = $data['zip_code'];
        $description = $data['description'];
            
        $c_data = [];
        $c_data['user_id'] = $user_id;
        $c_data['country'] = $country;
        $c_data['zip_code'] = $zip_code;
        $c_data['description'] = $description;

        $providerDetails = ProviderDetails::create($c_data);

        return $providerDetails;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $user_id = $data['user_id'];   
        $country = $data['country'];
        $zip_code = $data['zip_code'];
        $description = $data['description'];
            
        $d_data = [];
        $d_data['user_id'] = $user_id;
        $d_data['country'] = $country;
        $d_data['zip_code'] = $zip_code;
        $d_data['description'] = $description;

        $providerDetails = ProviderDetails::where('id', $id)->update($d_data);  

        return $providerDetails;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $providerDetails = $this->find($id);

        if(!$providerDetails instanceof ProviderDetails) {
            return false;
        }
        
        return $providerDetails->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return State::select('provider_details.*')
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
        ->whereNull('provider_details.deleted_at');
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
