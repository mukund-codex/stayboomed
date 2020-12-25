<?php 

namespace App\Transformers;

use App\Models\Profession;
use League\Fractal\TransformerAbstract;

class ProfessionTransformer extends TransformerAbstract
{
    public function transform(Profession $profession)
    {   
        $formatterProfession = [
            'id' =>  $profession->id,
            'name' => $profession->name
        ];

        return $formatterProfession;
    }
    
}

?>