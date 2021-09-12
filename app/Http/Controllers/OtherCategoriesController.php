<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OtherCategories;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\OtherCategoryRepository;
use App\Transformers\OtherCategoryTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OtherCategoriesController extends Controller
{
    public function __construct(OtherCategoryRepository $otherCategoryRespository, OtherCategoryTransformer $otherCategoryTransformer)
    {
        
        $this->otherCategoryRespository = $otherCategoryRespository;
        $this->otherCategoryTransformer = $otherCategoryTransformer;
        
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

        $data = $this->otherCategoryRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','name']),
            ["id" => "other_categories.id", "name"=>"other_categories.name"]
            ),[ "other_categories.id" => "=", "other_categories.name" => "ILIKE"]
            )->paginate();

        $uploadParameters['fields'] = ['name'];

        return $this->respondWithCollection($data, $this->otherCategoryTransformer, true, HttpResponse::HTTP_OK, 'Other Category List', $uploadParameters);
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
            'name' => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:100|unique:other_categories,name,NULL,id,deleted_at,NULL',
        ];     

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $otherCategory = $this->otherCategoryRespository->save($request->all()); 
        
        return $this->respondWithItem($otherCategory, $this->otherCategoryTransformer, true, HttpResponse::HTTP_CREATED, 'Other Category Created');
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
        $otherCategory = $this->otherCategoryRespository->find($id);
        return $this->respondWithItem($otherCategory, $this->otherCategoryTransformer, true, 200, 'Job Type Data');
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
