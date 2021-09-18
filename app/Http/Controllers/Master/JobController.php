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
            'publish_date' => 'required|date',
            'end_date' => 'required|date',
            'job_location' => 'required',
            'job_description' => 'required',
            'job_tags' => 'required',
            'vacancies' => 'required|numeric',
            'job_duration' => 'required',
            'gender' => 'required|in:male,female,other',
            'age' => 'required|numeric',
            'city_leaving' => 'required|regex:/^[a-zA-Z0-9 _-]*$/',
            'physical_attribute' => 'required',
            'experience' => 'required',
            'education' => 'required',
            'profession_id' => 'required|exists:profession_master,id',
            'budget' => 'required|regex:/^[a-zA-Z0-9 _-]*$/',
            'budget_time' => 'required',
            'details' => 'required|regex:/^[a-zA-Z0-9 _-]*$/',
            'subscription_type' => 'required',
            'expertise' => 'required',
            'category' => 'required',
            'language' => 'required',
            'job_type' => 'required',
            'other_categories' => 'required'

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
