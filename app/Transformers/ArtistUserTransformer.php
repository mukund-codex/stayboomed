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
            'email'  =>  $user->email,
            'number' => $user->number,
            'gender' => $user->gender,
            'address' =>  $user->address,
            'state_id'  =>  $user->state_id,   
            'city_id'  =>  $user->city_id, 
            'profession_id' => $user->profession_id,
            'dob' => $user->dob,
            'address' => $user->address
     ];

    }
    
}



?>