<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\JobRepository;
use App\Transformers\JobTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class JobController extends Controller
{   
    public function __construct(JobRepository $jobRespository, JobTransformer $jobTransformer)
    {
        
        $this->jobRespository = $jobRespository;
        $this->jobTransformer = $jobTransformer;
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if (\array_key_exists('id', $request->all())) {
            if(! Uuid::isValid($request->get('id'))){
                return $this->responseJson(false, 404, 'Record Not Found', ['id' => 'Invalid data']);
            }
        }

        $data = $this->jobRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','title', 'job_location', 'age', 'gender', 'profession', 'budget']),
            ["id" => "new_jobs.id", "title"=>"new_jobs.title", "job_location" => "new_jobs.job_location", "age" => "new_jobs.age", "gender" => "new_jobs.gender", "profession" => "profession_master.name", "budget" => "new_jobs.budget"]
            ),[ "new_jobs.id" => "=", "new_jobs.title" => "ILIKE", "new_jobs.job_location" => "ILIKE", "new_jobs.age" => "=", "new_jobs.gender" => "ILIKE", "profession_master.name" => "ILIKE", "new_jobs.budget" => "="]
            )->paginate();

        $uploadParameters['fields'] = [];

        return $this->respondWithCollection($data, $this->jobTransformer, true, HttpResponse::HTTP_OK, 'Jobs List', $uploadParameters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|min:5',
            'publish_start_date' => 'date',
            'publish_end_date' => 'required|date',
            'job_location' => 'required',
            'job_description' => 'required',
            'job_tags' => 'required', //string
            'vacancies' => 'required|numeric', //numeric
            'job_duration' => 'required', //full_time or part_time 
            'gender' => 'required',
            'age_from' => 'required', //this will be range like [20,30]
            'age_to' => 'required', //this will be range like [20,30]
            'budget_from' => 'required', //budget will be from and to
            'budget_to' => 'required', //budget will be from and to
            'jobStartDate' => 'required', //new input
            'jobEndDate' => 'required', //new input
            'artist_based_in' => 'required', //new input
            'audition_required' => 'required', //new input
            'audition_script' => 'required', //new input based on the aution_required yes or no
            'subscription_type' => 'required', //string paid or free
            'expertise' => 'required', //multiselect
            'category' => 'required', //single select
            'language' => 'required', //multiselect
            'job_type' => 'required', //multiselect
            'other_categories' => 'required' //multiselect

        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse != 'true') {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $job = $this->jobRespository->save($request->all()); 
        
        return $this->respondWithItem($job, $this->jobTransformer, true, HttpResponse::HTTP_CREATED, 'Job Created');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $job = $this->jobRespository->find($id);
        return $this->respondWithItem($job, $this->jobTransformer, true, 200, 'Job Data');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $job = $this->jobRespository->find($id);
        
        
        if (!$job ) {
            return $this->responseJson(false, 400, 'Job Not Found', []);
        }

        $jobDelete = $this->jobRespository->delete($id);

        if(! $jobDelete) {
            return $this->responseJson(false, 200, 'Error: Job Delete Failed', []);
        }

        return $this->responseJson(true, 200, 'Job Deleted', []);
    }
}
