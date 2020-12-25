<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\StateRepository;
use App\Transformers\StateTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StateController extends Controller
{
    public function __construct(StateRepository $stateRespository, StateTransformer $stateTransformer)
    {
        
        $this->stateRespository = $stateRespository;
        $this->stateTransformer = $stateTransformer;
        
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

        $data = $this->stateRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','name']),
            ["id" => "state_master.id", "name"=>"state_master.name"]
            ),[ "state_master.id" => "=", "state_master.name" => "ILIKE"]
            )->paginate();

        $uploadParameters['fields'] = ['name'];

        return $this->respondWithCollection($data, $this->stateTransformer, true, HttpResponse::HTTP_OK, 'Course List', $uploadParameters);
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
            'name' => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:100|unique:state_master,name,NULL,id,deleted_at,NULL'
        ];     

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $state = $this->stateRespository->save($request->all()); 
        
        return $this->respondWithItem($state, $this->stateTransformer, true, HttpResponse::HTTP_CREATED, 'State Created');
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
