<?php 

namespace App\Transformers;

use App\Models\ArtistPorfolio;
use League\Fractal\TransformerAbstract;

class ArtistPorfolioTransformer extends TransformerAbstract
{
    public function transform(ArtistPorfolio $porfolio)
    {   
        $formatterPorfolio = [
            'id' =>  $porfolio->id,
            'audio_title' => $porfolio->audio_title,
            'audio_file' => $porfolio->audio_file,
            'video_title' => $porfolio->video_title,
            'video_file' => $porfolio->video_file,
            'picture_title' => $porfolio->picture_title,
            'picture_file' => $porfolio->picture_file,
            'user_data' => $porfolio->users
        ];

        return $formatterPorfolio;
    }
    
}

?>