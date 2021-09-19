<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaveJobs;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\SaveJobsRepository;
use App\Transformers\SaveJobsTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DB;

class SaveJobsController extends Controller
{

    public function __construct(SaveJobsRepository $saveJobsRespository, SaveJobsTransformer $saveJobsTransformer)
    {
        
        $this->saveJobsRespository = $saveJobsRespository;
        $this->saveJobsTransformer = $saveJobsTransformer;
        
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

        $data = $this->saveJobsRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','user_id', 'job_id']),
            ["id" => "save_jobs.id", "user_id"=>"save_jobs.user_id", 'job_id' => 'save_jobs.job_id']
            ),[ "save_jobs.id" => "=", "save_jobs.user_id" => "=", 'save_jobs.job_id' => "="]
            )->paginate();

        $uploadParameters['fields'] = ['User name', 'job name'];

        return $this->respondWithCollection($data, $this->saveJobsTransformer, true, HttpResponse::HTTP_OK, 'Saved Job List', $uploadParameters);
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

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $save = $this->saveJobsRespository->save($request->all()); 
        
        return $this->respondWithItem($save, $this->saveJobsTransformer, true, HttpResponse::HTTP_CREATED, 'Job Saved');

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
        $save = $this->saveJobsRespository->find($id);
        return $this->respondWithItem($save, $this->saveJobsTransformer, true, 200, 'City Data');
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
        $save = $this->saveJobsRespository->find($id);
        
        
        if (!$save ) {
            return $this->responseJson(false, 400, 'Saved Job Not Found', []);
        }

        $saveDelete = $this->saveJobsRespository->delete($id);

        if(! $saveDelete) {
            return $this->responseJson(false, 200, 'Error: Saved Job Failed', []);
        }

        return $this->responseJson(true, 200, 'Saved Job Deleted', []);
    }
}
