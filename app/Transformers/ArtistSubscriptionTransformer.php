<?php 

namespace App\Transformers;

use App\Models\ArtistSubscription;
use League\Fractal\TransformerAbstract;

class ArtistSubscriptionTransformer extends TransformerAbstract
{
    public function transform(ArtistSubscription $artistSubscription)
    {   
        $formatterDetails = [
            'id' =>  $artistSubscription->id,
            'user_id' => $artistSubscription->user_id,
            'subscription_id' => $artistSubscription->subscription_id,
            'user_data' => $artistSubscription->users,
            'job_data' => $artistSubscription->jobs
        ];

        return $formatterDetails;
    }
    
}

?>