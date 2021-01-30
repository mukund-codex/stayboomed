<?php 

namespace App\Transformers;

use App\Models\{ArtistUser};
use League\Fractal\TransformerAbstract;

class ArtistUserTransformer extends TransformerAbstract
{
    public function transform(ArtistUser $user)
    {   
        return [
            'id'    =>  $user->id,
            'fullname' => $user->fullname,
            'user_type'  =>  $user->user_type,
            'username' => $user->username,
            'email'  =>  $user->email,
            'number' => $user->number,
            'gender' => $user->gender,
            'address' =>  $user->address,
            'state'  =>  $user->state,   
            'city'  =>  $user->city, 
            'profession' => $user->artistProfession,
            'dob' => $user->dob,
            'address' => $user->address,
            'referral_code' => $user->referral_code,
            'access_token' => $user->accessToken
     ];

    }
    
}



?>