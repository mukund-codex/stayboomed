<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobType;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\JobTypeRepository;
use App\Transformers\JobTypeTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class JobTypeController extends Controller
{
    public function __construct(JobTypeRepository $jobTypeRespository, JobTypeTransformer $jobTypeTransformer)
    {
        
        $this->jobTypeRespository = $jobTypeRespository;
        $this->jobTypeTransformer = $jobTypeTransformer;
        
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

        $data = $this->jobTypeRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','name']),
            ["id" => "job_types.id", "name"=>"job_types.name"]
            ),[ "job_types.id" => "=", "job_types.name" => "ILIKE"]
            )->paginate();

        $uploadParameters['fields'] = ['name'];

        return $this->respondWithCollection($data, $this->jobTypeTransformer, true, HttpResponse::HTTP_OK, 'Job Type List', $uploadParameters);
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
            'name' => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:100|unique:job_types,name,NULL,id,deleted_at,NULL',
        ];     

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $jobType = $this->jobTypeRespository->save($request->all()); 
        
        return $this->respondWithItem($jobType, $this->jobTypeTransformer, true, HttpResponse::HTTP_CREATED, 'Job Type Created');
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
        $jobType = $this->jobTypeRespository->find($id);
        return $this->respondWithItem($jobType, $this->jobTypeTransformer, true, 200, 'Job Type Data');
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
        $category = $this->categoryRespository->find($id);

        if(! $category) {
            return $this->responseJson(false, 400, 'Category Not Found', []);
        }
        
        $rules = [
            'name' => 'required|unique:categories,name,'.$id.',id,deleted_at,NULL|regex:/^[\pL\s\.-]+$/u|min:3|max:100'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $isCateoryUpdated = $this->categoryRespository->update($id, $request->all());

        if(!$isCategoryUpdated) {
            return $this->responseJson(false, 400, 'Error: Catgeory Update Failed', []);
        }

        $updatedCategory = $this->categoryRespository->find($id);
        return $this->respondWithItem($updatedCategory, $this->CategoryTransformer, true, 201, 'Catgeory Updated');  
 
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
        $state = $this->stateRespository->find($id);
        
        
        if (!$state ) {
            return $this->responseJson(false, 400, 'State Not Found', []);
        }

        $stateDelete = $this->stateRespository->delete($id);

        if(! $stateDelete) {
            return $this->responseJson(false, 200, 'Error: State Delete Failed', []);
        }

        return $this->responseJson(true, 200, 'State Deleted', []);
    }
}
