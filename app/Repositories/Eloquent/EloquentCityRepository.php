<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\CityRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\City; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\CityExport;
use App\Imports\CityImport;
use Illuminate\Support\Collection;

class EloquentCityRepository implements CityRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return City::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return City::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return City::where($key, $value)->first();
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
        $name = $data['name'];   
        $state_id = $data['state_id'];
            
        $c_data = [];
        $c_data['name'] = $name;
        $c_data['state_id'] = $state_id;

        $city = City::create($c_data);

        return $city;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $name = $data['name'];   
        $state_id = $data['state_id'];
        
        $d_data = [];
        $d_data['name'] = $name;
        $d_data['state_id'] = $state_id;

        $city = City::where('id', $id)->update($d_data);  

        return $city;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $city = $this->find($id);

        if(!$city instanceof City) {
            return false;
        }
        
        return $city->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return City::select('city_master.*')
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
        ->whereNull('city_master.deleted_at')
        ->orderBy('city_master.name','asc');
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
