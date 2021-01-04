<?php 

namespace App\Transformers;

use App\Models\AppliedJobs;
use League\Fractal\TransformerAbstract;

class AppliedJobsTransformer extends TransformerAbstract
{
    public function transform(AppliedJobs $appliedJobs)
    {   
        $formatterDetails = [
            'id' =>  $appliedJobs->id,
            'user_id' => $appliedJobs->user_id,
            'job_id' => $appliedJobs->job_id,
            'user_data' => $appliedJobs->user,
            'job_data' => $appliedJobs->job
        ];

        return $formatterDetails;
    }
    
}

?>