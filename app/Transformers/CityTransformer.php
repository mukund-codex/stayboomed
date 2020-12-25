<?php 

namespace App\Transformers;

use App\Models\City;
use League\Fractal\TransformerAbstract;

class CityTransformer extends TransformerAbstract
{
    public function transform(City $city)
    {   
        $formatterCity = [
            'id' =>  $city->id,
            'name' => $city->name,
            'state_id' => $city->state->id,
            'state_name' => $city->state->name
        ];

        return $formatterCity;
    }
    
}

?>