<?php 

namespace App\Transformers;

use App\Models\ArtistDetails;
use League\Fractal\TransformerAbstract;

class ArtistDetailsTransformer extends TransformerAbstract
{
    public function transform(ArtistDetails $details)
    {   
        $formatterDetails = [
            'id' =>  $details->id,
            'country' => $details->country,
            'zip_code' => $details->zip_code,
            'corresponding_address' => $details->corresponding_address,
            'permanent_address' => $details->permanent_address,
            'profile_picture' => $details->profile_picture,
            'cover_picture' => $details->cover_picture,
            'user_data' => $details->users
        ];

        return $formatterDetails;
    }
    
}

?>