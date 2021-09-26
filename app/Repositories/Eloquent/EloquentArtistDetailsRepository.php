<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\ArtistDetailsRepository;
use Illuminate\Http\Response;
use App\Traits\S3ForMedia;
use DB;
use App\Models\ArtistDetails; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{ArtistDetailsExport};
use App\Imports\ArtistDetailsImport;
use Illuminate\Support\Collection;

class EloquentArtistDetailsRepository implements ArtistDetailsRepository
{
    use S3ForMedia;
/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return ArtistDetails::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return ArtistDetails::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return ArtistDetails::where($key, $value)->first();
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
        $corresponding_address = $data['corresponding_address'];
        $permanent_address = $data['permanent_address'];

        $c_data = [];
        $c_data['user_id'] = $user_id;
        $c_data['country'] = $country;
        $c_data['zip_code'] = $zip_code;
        $c_data['corresponding_address'] = $corresponding_address;
        $c_data['permanent_address'] = $permanent_address;

        $extension1 = $data['profile_picture']->getClientOriginalExtension();
        $file_name1 = (string) Str::uuid().'.'.$extension1;

        $extension2 = $data['cover_picture']->getClientOriginalExtension();
        $file_name2 = (string) Str::uuid().'.'.$extension2;

        $s3 = \Storage::disk('s3');
        
        $s3UrlProfile = "artist_details/$user_id/";
        $s3UrlCover = "artist_details/$user_id/";

        $isFileUploadedProfile = Storage::disk('s3')->put($s3UrlProfile.$file_name1, file_get_contents($data['profile_picture']), 'public');
        $s3_url_profile = env('AWS_URL').$s3UrlProfile.$file_name1;

        $isFileUploadedCover = Storage::disk('s3')->put($s3UrlCover.$file_name2, file_get_contents($data['cover_picture']), 'public');
        $s3_url_cover = env('AWS_URL').$s3UrlCover.$file_name2;

        // $s3UrlProfile = "artist_details/$user_id/";
           
        // if(array_key_exists('profile_picture', $data)){
           
        //     $image = $data['cover_picture'];
        //     // $image_name = (string) Str::uuid();       
            
        //     $s3_url_profile = $this->pushMediaToS3($s3UrlProfile.$image, $image, 'public');

        // }

        // $s3UrlCover = "artist_details/$user_id/";

        // if(array_key_exists('cover_picture', $data)){
            
        //     $image = $data['cover_picture'];
        //     // $image_name = (string) Str::uuid();       
            
        //     $s3_url_cover = $this->pushMediaToS3($s3UrlCover.$image, $image, 'public');

        // }

        $c_data['profile_picture'] = $s3_url_profile;
        $c_data['cover_picture'] = $s3_url_cover;

        $artistDetails = ArtistDetails::create($c_data);

        return $artistDetails;
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
    public function updateAlternateDetails($id, array $data)
    {

        $alternateEmail = $data['alternate_email'];
        $alternateNumber = $data['alternate_number'];
            
        $d_data = [];
        $d_data['alternate_email'] = $alternateEmail;
        $d_data['alternate_number'] = $alternateNumber;

        $user = ArtistDetails::where('user_id', $id)->update($d_data);  
        
        return $user;
    
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

        return ArtistDetails::select('artist_details.*')
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
        ->whereNull('artist_details.deleted_at');
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
