<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expertise;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\ExpertiseRepository;
use App\Transformers\ExpertiseTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ExpertiseController extends Controller
{
    public function __construct(ExpertiseRepository $expertiseRespository, ExpertiseTransformer $expertiseTransformer)
    {
        
        $this->expertiseRespository = $expertiseRespository;
        $this->expertiseTransformer = $expertiseTransformer;
        
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

        $data = $this->expertiseRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','name']),
            ["id" => "expertises.id", "name"=>"expertises.name"]
            ),[ "expertises.id" => "=", "expertises.name" => "ILIKE"]
            )->paginate();

        $uploadParameters['fields'] = ['name'];

        return $this->respondWithCollection($data, $this->expertiseTransformer, true, HttpResponse::HTTP_OK, 'Expertise List', $uploadParameters);
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
            'name' => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:100|unique:expertises,name,NULL,id,deleted_at,NULL'
        ];     

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $expertise = $this->expertiseRespository->save($request->all()); 
        
        return $this->respondWithItem($expertise, $this->expertiseTransformer, true, HttpResponse::HTTP_CREATED, 'Expertise Created');
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
        $state = $this->stateRespository->find($id);
        return $this->respondWithItem($state, $this->stateTransformer, true, 200, 'State Data');
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
        $state = $this->stateRespository->find($id);

        if(! $state) {
            return $this->responseJson(false, 400, 'State Not Found', []);
        }
        
        $rules = [
            'name' => 'required|unique:state_master,name,'.$id.',id,deleted_at,NULL|regex:/^[\pL\s\.-]+$/u|min:3|max:100'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $isStateUpdated = $this->stateRespository->update($id, $request->all());

        if(!$isStateUpdated) {
            return $this->responseJson(false, 400, 'Error: State Update Failed', []);
        }

        $updatedState = $this->stateRespository->find($id);
        return $this->respondWithItem($updatedState, $this->stateTransformer, true, 201, 'State Updated');  
 
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
