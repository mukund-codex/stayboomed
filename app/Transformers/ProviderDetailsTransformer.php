<?php 

namespace App\Transformers;

use App\Models\ProviderDetails;
use League\Fractal\TransformerAbstract;

class ProviderDetailsTransformer extends TransformerAbstract
{
    public function transform(ProviderDetails $details)
    {   
        $formatterDetails = [
            'id' =>  $details->id,
            'country' => $details->country,
            'zip_code' => $details->zip_code,
            'description' => $details->description,
            'user_data' => $details->users
        ];

        return $formatterDetails;
    }
    
}

?>