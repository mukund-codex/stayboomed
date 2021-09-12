<?php 

namespace App\Transformers;

use App\Models\OtherCategories;
use League\Fractal\TransformerAbstract;

class OtherCategoryTransformer extends TransformerAbstract
{
    public function transform(OtherCategories $otherCategory)
    {   
        $formatterOtherCategory = [
            'id' =>  $otherCategory->id,
            'name' => $otherCategory->name,
        ];

        return $formatterOtherCategory;
    }
    
}

?>