<?php 

namespace App\Transformers;

use App\Models\Categories;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    public function transform(Categories $category)
    {   
        $formatterCategory = [
            'id' =>  $category->id,
            'name' => $category->name,
            'icon' => $category->icon,
        ];

        return $formatterCategory;
    }
    
}

?>