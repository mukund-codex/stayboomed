<?php 

namespace App\Transformers;

use App\Models\Subscription;
use League\Fractal\TransformerAbstract;

class SubscriptionTransformer extends TransformerAbstract
{
    public function transform(Subscription $subscription)
    {   
        return [
            'id'    =>  $subscription->id,
            'title' => $subscription->title,
            'subscription_type' => $subscription->subscription_type,
            'price' => $subscription->price,
            'job_apply' => $subscription->job_apply,
            'users' => $subscription->user
        ];

    }
    
}



?>