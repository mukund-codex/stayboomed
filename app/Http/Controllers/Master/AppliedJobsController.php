<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppliedJobs;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\AppliedJobsRepository;
use App\Transformers\AppliedJobsTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DB;

class AppliedJobsController extends Controller
{
    public function __construct(AppliedJobsRepository $appliedJobsRespository, AppliedJobsTransformer $appliedJobsTransformer)
    {
        
        $this->appliedJobsRespository = $appliedJobsRespository;
        $this->appliedJobsTransformer = $appliedJobsTransformer;
        
    }

    /**A
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

        $data = $this->appliedJobsRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','user_id', 'job_id']),
            ["id" => "applied_jobs.id", "user_id"=>"applied_jobs.user_id", 'job_id' => 'applied_jobs.job_id']
            ),[ "applied_jobs.id" => "=", "applied_jobs.user_id" => "=", 'applied_jobs.job_id' => "="]
            )->paginate();

        $uploadParameters['fields'] = ['User name', 'jon name'];

        return $this->respondWithCollection($data, $this->appliedJobsTransformer, true, HttpResponse::HTTP_OK, 'Applied Job List', $uploadParameters);
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
            'job_id' => 'required|exists:new_jobs,id'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $user_id = $request->input('user_id');
        $job_id = $request->input('job_id');
        
        $paidUsers = DB::table('new_jobs')
                    ->where('id', '=', $job_id)->get()->toArray();

        $provider_id = $paidUsers[0]->user_id;

        $paidUsers = DB::table('paid_users')
                    ->where('user_id', '=', $user_id)
                    ->where('provider_id', '=', $provider_id)->get()->toArray();

        if(!empty($paidUsers)) {
            $appliedJobs = $this->appliedJobsRespository->save($request->all()); 
        
            return $this->respondWithItem($appliedJobs, $this->appliedJobsTransformer, true, HttpResponse::HTTP_CREATED, 'Applied for Job');
        }else {
            $alreadyApplied = DB::table('applied_jobs')
                    ->where('user_id', '=', $user_id)
                    ->where('job_id', '=', $job_id)->get()->toArray();

            if($alreadyApplied) 
            {
                return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Already Applied for this.');
            }

            $appliedJobs = $this->appliedJobsRespository->save($request->all()); 
        
            return $this->respondWithItem($appliedJobs, $this->appliedJobsTransformer, true, HttpResponse::HTTP_CREATED, 'Applied for Job');
        }   
        
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
    }
}
