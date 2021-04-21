<?php 

namespace App\Transformers;

use App\Models\SaveJobs;
use League\Fractal\TransformerAbstract;

class SaveJobsTransformer extends TransformerAbstract
{
    public function transform(SaveJobs $saveJobs)
    {   
        $formatterDetails = [
            'id' =>  $saveJobs->id,
            'user_id' => $saveJobs->user_id,
            'job_id' => $saveJobs->job_id,
            'user_data' => $saveJobs->user,
            'job_data' => $saveJobs->job
        ];

        return $formatterDetails;
    }
    
}

?>