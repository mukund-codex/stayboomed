<?php 

namespace App\Transformers;

use App\Models\JobType;
use League\Fractal\TransformerAbstract;

class JobTypeTransformer extends TransformerAbstract
{
    public function transform(JobType $jobType)
    {   
        $formatterJobType = [
            'id' =>  $jobType->id,
            'name' => $jobType->name,
        ];

        return $formatterJobType;
    }
    
}

?>