<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Refer;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\ReferRepository;
use App\Transformers\ReferTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ReferController extends Controller
{   

    public function __construct(ReferRepository $referRespository, ReferTransformer $referTransformer)
    {
        
        $this->referRespository = $referRespository;
        $this->referTransformer = $referTransformer;
        
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

        $data = $this->referRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','referee_id', 'referral_code', 'user_id']),
            ["id" => "referrals.id", "referee_id"=>"referrals.referee_id", "referral_code" => "referrals.referral_code", "user_id" => "referrals.user_id"]
            ),[ "referrals.id" => "=", "referrals.referee_id" => "ILIKE", "referrals.referral_code" => "ILIKE", "referrals.user_id" => "="]
            )->paginate();

        $uploadParameters['fields'] = [];

        return $this->respondWithCollection($data, $this->referTransformer, true, HttpResponse::HTTP_OK, 'Referral List', $uploadParameters);
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
            'referee_id' => 'required|exists:users,id',
            'referral_code' => 'required',
            'user_id' => 'required'
            
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse != 'true') {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $refer = $this->referRespository->save($request->all()); 
        
        return $this->respondWithItem($refer, $this->referTransformer, true, HttpResponse::HTTP_CREATED, 'Referral Created');
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
