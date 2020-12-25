<?php 

namespace App\Transformers;

use App\Models\State;
use League\Fractal\TransformerAbstract;

class StateTransformer extends TransformerAbstract
{
    public function transform(State $state)
    {   
        $formatterState = [
            'id' =>  $state->id,
            'name' => $state->name
        ];

        return $formatterState;
    }
    
}

?>