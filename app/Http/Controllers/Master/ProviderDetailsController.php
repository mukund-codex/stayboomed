<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProviderDetails;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\ProviderDetailsRepository;
use App\Transformers\ProviderDetailsTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProviderDetailsController extends Controller
{
    public function __construct(ProviderDetailsRepository $providerDetailsRespository, ProviderDetailsTransformer $providerDetailsTransformer)
    {
        
        $this->providerDetailsRespository = $providerDetailsRespository;
        $this->providerDetailsTransformer = $providerDetailsTransformer;
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'country' => 'required|alpha',
            'zip_code' => 'required|numeric',
            'description' => 'required'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $details = $this->providerDetailsRespository->save($request->all()); 
        
        return $this->respondWithItem($details, $this->providerDetailsTransformer, true, HttpResponse::HTTP_CREATED, 'Provider Details Created');

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
