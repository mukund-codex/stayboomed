<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profession;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\ProfessionRepository;
use App\Transformers\ProfessionTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProfessionController extends Controller
{   
    public function __construct(ProfessionRepository $professionRespository, ProfessionTransformer $professionTransformer)
    {
        
        $this->professionRespository = $professionRespository;
        $this->professionTransformer = $professionTransformer;
        
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

        $data = $this->professionRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','name']),
            ["id" => "profession_master.id", "name"=>"profession_master.name"]
            ),[ "profession_master.id" => "=", "profession_master.name" => "ILIKE"]
            )->paginate();

        $uploadParameters['fields'] = ['name'];

        return $this->respondWithCollection($data, $this->professionTransformer, true, HttpResponse::HTTP_OK, 'Profession List', $uploadParameters);
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
        
        $profession = $this->professionRespository->save($request->all()); 
        
        return $this->respondWithItem($profession, $this->professionTransformer, true, HttpResponse::HTTP_CREATED, 'Profession Created');
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
        $profession = $this->professionRespository->find($id);
        return $this->respondWithItem($profession, $this->professionTransformer, true, 200, 'State Data');
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
        $profession = $this->professionRespository->find($id);

        if(! $profession) {
            return $this->responseJson(false, 400, 'profession Not Found', []);
        }
        
        $rules = [
            'name' => 'required|unique:profession_master,name,'.$id.',id,deleted_at,NULL|regex:/^[\pL\s\.-]+$/u|min:3|max:100'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $isProfessionUpdated = $this->professionRespository->update($id, $request->all());

        if(!$isProfessionUpdated) {
            return $this->responseJson(false, 400, 'Error: Profession Update Failed', []);
        }

        $updatedProfession = $this->professionRespository->find($id);
        return $this->respondWithItem($updatedProfession, $this->professionTransformer, true, 201, 'Profession Updated');  
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
        $profession = $this->professionRespository->find($id);
        
        
        if (!$profession ) {
            return $this->responseJson(false, 400, 'Profession Not Found', []);
        }

        $professionDelete = $this->professionRespository->delete($id);

        if(! $professionDelete) {
            return $this->responseJson(false, 200, 'Error: Profession Delete Failed', []);
        }

        return $this->responseJson(true, 200, 'Profession Deleted', []);
    }
}
