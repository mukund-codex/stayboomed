<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\ArtistUserRepository;
use Illuminate\Http\Response;
use DB;
use App\Models\ArtistUser; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{UserExport};
use App\Imports\UserImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class EloquentArtistUserRepository implements ArtistUserRepository
{

/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return ArtistUser::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return ArtistUser::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return ArtistUser::where($key, $value)->first();
    }
    
    /**
    * {@inheritdoc}
    */

    /**
    * {@inheritdoc}
    */
    public function findBy($email = [])
    {
        return ArtistUser::where('email', $email)->first();
    }

    public function getUserByCode($code = [])
    {
        return ArtistUser::where('referral_code', $code)->first();
    }

    public function save(array $data)
    {   
        $fullname = $data['fullname'];
        $username = $data['username'];
        $email = $data['email'];
        $user_type = 'artist';
        $password = $data['password'];
        $number = $data['number'];
        $state_id = $data['state_id'];
        $city_id = $data['city_id'];
        $profession_id = $data['profession_id'];
        $dob = $data['dob']; 
        $gender = $data['gender'];
        $address = $data['address'];
        $referral_code = $data['referral_code'];
            
        $c_data = [];
        $c_data['fullname'] = $fullname;
        $c_data['user_type'] = $user_type;
        $c_data['username'] = $username;
        $c_data['email'] = $email;
        $c_data['password'] = Hash::make($password);
        $c_data['number'] = $number;
        $c_data['state_id'] = $state_id;
        $c_data['city_id'] = $city_id;
        $c_data['profession_id'] = $profession_id; 
        $c_data['dob'] = $dob;
        $c_data['gender'] = $gender;
        $c_data['address'] = $address;
        $c_data['referral_code'] = $referral_code;
        
        $user = ArtistUser::create($c_data);

        return $user;
    }


    /**
    * {@inheritdoc}
    */
    public function update($id, array $data)
    {

        $fullname = $data['fullname'];
        $username = $data['username'];
        $email = $data['email'];
        $user_type = 'artist';
        $password = $data['password'];
        $number = $data['number'];
        $state_id = $data['state_id'];
        $city_id = $data['city_id'];
        $profession_id = $data['profession_id'];
        $dob = $data['dob']; 
        $gender = $data['gender'];
        $address = $data['address'];
            
        $d_data = [];
        $d_data['fullname'] = $fullname;

        $d_data['designation'] = $designation;
        $d_data['organisation'] = $organisation;

        $d_data['user_type'] = $user_type;
        $d_data['username'] = $username;
        $d_data['email'] = $email;

        $d_data['password'] = Hash::make($password);
        $d_data['number'] = $number;
        $d_data['state_id'] = $state_id;
        $d_data['city_id'] = $city_id;

        $d_data['profession_id'] = $profession_id; 
        $d_data['dob'] = $dob;

        $d_data['gender'] = $gender;
        $d_data['address'] = $address;

        $user = ArtistUser::where('id', $id)->update($d_data);  

        return $user;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $user = $this->find($id);

        if(!$user instanceof ArtistUser) {
            return false;
        }
        
        return $user->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return User::select('users.*')
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
        ->whereNull('users.deleted_at')
        ->orderBy('users.fullname','asc');
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
