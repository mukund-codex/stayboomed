<?php

namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\ArtistPorfolioRepository;
use Illuminate\Http\Response;
use App\Traits\S3ForMedia;
use DB;
use App\Models\ArtistPorfolio; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\{ArtistPorfolioExport};
use App\Imports\ArtistPorfolioImport;
use Illuminate\Support\Collection;

class EloquentArtistPorfolioRepository implements ArtistPorfolioRepository
{
    use S3ForMedia;
/**
    * {@inheritdoc}
    */
    public function find($id)
    {
        return ArtistPorfolio::find($id);
    }

    /**
    * {@inheritdoc}
    */
    public function findOneBy(array $data)
    {
        return ArtistPorfolio::where($data)->first();
    }
    
    /**
    * {@inheritdoc}
    */
    public function findIn($key, $value)
    {
        return ArtistPorfolio::where($key, $value)->first();
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
        $audio_title = $data['audio_title'];
        $video_title = $data['video_title'];
        $picture_title = $data['picture_title'];

        $c_data = [];
        $c_data['user_id'] = $user_id;
        $c_data['audio_title'] = $audio_title;
        $c_data['video_title'] = $video_title;
        $c_data['picture_title'] = $picture_title;

        if($data['audio_file']) {
            $audio_extension = $data['audio_file']->getClientOriginalExtension();
            $audio_file_name = (string) Str::uuid().'.'.$audio_extension;
            $s3 = \Storage::disk('s3');
        
            $s3UrlAudio = "artist_porfolio/$user_id/";

            $isFileUploadedAudio = Storage::disk('s3')->put($s3UrlAudio.$audio_file_name, file_get_contents($data['audio_file']), 'public');
            $s3_url_audio = env('AWS_URL').$s3UrlAudio.$audio_file_name;

            $c_data['audio_file'] = $s3_url_audio;
        }

        if($data['video_file']) {
            $video_extension = $data['video_file']->getClientOriginalExtension();
            $video_file_name = (string) Str::uuid().'.'.$video_extension;
            $s3 = \Storage::disk('s3');
        
            $s3UrlVideo = "artist_porfolio/$user_id/";

            $isFileUploadedVideo = Storage::disk('s3')->put($s3UrlVideo.$video_file_name, file_get_contents($data['video_file']), 'public');
            $s3_url_video = env('AWS_URL').$s3UrlVideo.$video_file_name;

            $c_data['video_file'] = $s3_url_video;
        }

        if($data['picture_file']) {
            $picture_extension = $data['picture_file']->getClientOriginalExtension();
            $picture_file_name = (string) Str::uuid().'.'.$picture_extension;
            $s3 = \Storage::disk('s3');
        
            $s3UrlPicture = "artist_porfolio/$user_id/";

            $isFileUploadedPicture = Storage::disk('s3')->put($s3UrlPicture.$picture_file_name, file_get_contents($data['picture_file']), 'public');
            $s3_url_picture = env('AWS_URL').$s3UrlPicture.$picture_file_name;

            $c_data['picture_file'] = $s3_url_picture;
        }

        $artistPorfolio = ArtistPorfolio::create($c_data);

        return $artistPorfolio;
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

        $artistDetails = ArtistPorfolio::where('id', $id)->update($d_data);  

        return $artistDetails;
    
    }


    /**
    * {@inheritdoc}
    */
     public function delete($id)
    {
        $artistDetails = $this->find($id);

        if(!$artistDetails instanceof ArtistPorfolio) {
            return false;
        }
        
        return $artistDetails->delete();
    }


    /**
    * {@inheritdoc}
    */
    public function filtered($filter, $operator)
    {

        return ArtistPorfolio::select('artist_porfolio.*')
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
        ->whereNull('artist_porfolio.deleted_at');
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
