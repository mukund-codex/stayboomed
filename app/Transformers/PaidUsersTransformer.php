<?php 

namespace App\Transformers;

use App\Models\PaidUsers;
use League\Fractal\TransformerAbstract;

class PaidUsersTransformer extends TransformerAbstract
{
    public function transform(PaidUsers $paidUsers)
    {   
        $formatterDetails = [
            'id' =>  $paidUsers->id,
            'user_id' => $paidUsers->user_id,
            'provider_id' => $paidUsers->provider_id,
            'user_data' => $paidUsers->users
        ];

        return $formatterDetails;
    }
    
}

?>