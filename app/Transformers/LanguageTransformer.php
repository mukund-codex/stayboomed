<?php 

namespace App\Transformers;

use App\Models\Language;
use League\Fractal\TransformerAbstract;

class LanguageTransformer extends TransformerAbstract
{
    public function transform(Language $language)
    {   
        $formatterLanguage = [
            'id' =>  $language->id,
            'name' => $language->name,
        ];

        return $formatterLanguage;
    }
    
}

?>