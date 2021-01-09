<?php 

namespace App\Transformers;

use App\Models\Refer;
use League\Fractal\TransformerAbstract;

class ReferTransformer extends TransformerAbstract
{
    public function transform(Refer $refer)
    {   
        $formatterRefer = [
            'id' =>  $refer->id,
            'referee_id' => $refer->referee_id,
            'referral-code' => $refer->referral_code,
            'user_id' => $refer->user_id,
            'user_data' => $refer->users
        ];

        return $formatterRefer;
    }
    
}

?>