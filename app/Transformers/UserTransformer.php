<?php 

namespace App\Transformers;

use App\Models\{User};
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {   
        return [
            'id'    =>  $user->id,
            'fullname' => $user->fullname,
            'designation'  =>  $user->designation,
            'organisation'  =>  $user->organisation,
            'user_type'  =>  $user->user_type,
            'username' => $user->username,
            'email'  =>  $user->email,
            'number' => $user->number,
            'gender' => $user->gender,
            'address' =>  $user->address,
            'state'  =>  $user->state,   
            'city'  =>  $user->city, 
            'referral_code' => $user->referral_code,
            'access_token' => $user->accessToken
     ];

    }
    
}



?>