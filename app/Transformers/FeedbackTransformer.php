<?php 

namespace App\Transformers;

use App\Models\Feedback;
use League\Fractal\TransformerAbstract;

class FeedbackTransformer extends TransformerAbstract
{
    public function transform(Feedback $feedback)
    {   
        return [
            'id'    =>  $feedback->id,
            'user_id' => $feedback->user_id,
            'user_details' => $feedback->users,
            'ratings' => $feedback->ratings,
            'comments' => $feedback->comments,
            'created_at' => $feedback->created_at,
            'updated_at' => $feedback->updated_at
     ];

    }
    
}

?>