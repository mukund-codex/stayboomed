<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\CityRepository;
use App\Transformers\CityTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CityController extends Controller
{   
    public function __construct(CityRepository $cityRespository, CityTransformer $cityTransformer)
    {
        
        $this->cityRespository = $cityRespository;
        $this->cityTransformer = $cityTransformer;
        
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

        $data = $this->cityRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','name', 'state_id']),
            ["id" => "city_master.id", "name"=>"city_master.name", 'state_id' => 'state_master.id']
            ),[ "city_master.id" => "=", "city_master.name" => "ILIKE", 'state_master.id' => "id"]
            )->paginate();

        $uploadParameters['fields'] = ['state name', 'name'];

        return $this->respondWithCollection($data, $this->cityTransformer, true, HttpResponse::HTTP_OK, 'City List', $uploadParameters);
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
            'name' => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:100|unique:city_master,name,NULL,id,deleted_at,NULL',
            'state_id' => 'required|exists:state_master,id'
        ];     

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $city = $this->cityRespository->save($request->all()); 
        
        return $this->respondWithItem($city, $this->cityTransformer, true, HttpResponse::HTTP_CREATED, 'City Created');
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
        $city = $this->cityRespository->find($id);
        return $this->respondWithItem($city, $this->cityTransformer, true, 200, 'City Data');
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
        $city = $this->cityRespository->find($id);

        if(! $city) {
            return $this->responseJson(false, 400, 'City Not Found', []);
        }
        
        $rules = [
            'name' => 'required|unique:city_master,name,'.$id.',id,deleted_at,NULL|regex:/^[\pL\s\.-]+$/u|min:3|max:100',
            'state_id' => 'required|exists:state_master,id'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $isCityUpdated = $this->cityRespository->update($id, $request->all());

        if(!$isCityUpdated) {
            return $this->responseJson(false, 400, 'Error: City Update Failed', []);
        }

        $updatedCity = $this->cityRespository->find($id);
        return $this->respondWithItem($updatedCity, $this->cityTransformer, true, 201, 'City Updated');  
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
        $city = $this->cityRespository->find($id);
        
        
        if (!$city ) {
            return $this->responseJson(false, 400, 'City Not Found', []);
        }

        $cityDelete = $this->cityRespository->delete($id);

        if(! $cityDelete) {
            return $this->responseJson(false, 200, 'Error: City Delete Failed', []);
        }

        return $this->responseJson(true, 200, 'City Deleted', []);
    }
}
