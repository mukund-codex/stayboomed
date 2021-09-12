<?php 

namespace App\Transformers;

use App\Models\Expertise;
use League\Fractal\TransformerAbstract;

class ExpertiseTransformer extends TransformerAbstract
{
    public function transform(Expertise $expertise)
    {   
        $formatterExpertise = [
            'id' =>  $expertise->id,
            'name' => $expertise->name
        ];

        return $formatterExpertise;
    }
    
}

?>