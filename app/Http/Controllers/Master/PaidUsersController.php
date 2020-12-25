<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaidUsers;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\PaidUsersRepository;
use App\Transformers\PaidUsersTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PaidUsersController extends Controller
{
    //
    public function __construct(PaidUsersRepository $paidUsersRespository, PaidUsersTransformer $paidUsersTransformer)
    {
        
        $this->paidUsersRespository = $paidUsersRespository;
        $this->paidUsersTransformer = $paidUsersTransformer;
        
    }

    public function index(Request $request)
    {
        //
        if (\array_key_exists('id', $request->all())) {
            return $this->responseJson(false, 404, 'Record Not Found', ['id' => 'Invalid data']);
        }

        $data = $this->paidUsersRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','user_id', 'provider_id']),
            ["id" => "paid_users.id", "user_id"=>"paid_users.user_id", "provider_id" => "paid_users.provider_id"]
            ),[ "paid_users.id" => "=", "paid_users.user_id" => "=", "paid_users.provider_id" => "="]
            )->paginate();

        $uploadParameters['fields'] = [];
        
        return $this->respondWithCollection($data, $this->paidUsersTransformer, true, HttpResponse::HTTP_OK, 'Paid Users List', $uploadParameters);
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
            'provider_id' => 'required|exists:users,id'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse != 'true') {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $user_id = $request->input('user_id');
        $provider_id = $request->input('provider_id');
        $paidDetails = $this->paidUsersRespository->getDetails(['user_id' => $user_id, 'provider_id' => $provider_id]);
        
        if(!empty($paidDetails['user_id']) && !empty($paidDetails['provider_id'])) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', ['error' => 'User is already a Paid User']);
        }

        $paidUsers = $this->paidUsersRespository->save($request->all()); 
        
        return $this->respondWithItem($paidUsers, $this->paidUsersTransformer, true, HttpResponse::HTTP_CREATED, 'Paid User Created');

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
        $paidUsers = $this->paidUsersRespository->find($id);
        return $this->respondWithItem($paidUsers, $this->paidUsersTransformer, true, 200, 'Paid Users Data');
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
        $paidUsers = $this->paidUsersRespository->find($id);
        
        
        if (!$paidUsers ) {
            return $this->responseJson(false, 400, 'Paid Users Not Found', []);
        }

        $paidUsersDelete = $this->paidUsersRespository->delete($id);

        if(! $paidUsersDelete) {
            return $this->responseJson(false, 200, 'Error: Paid User Delete Failed', []);
        }

        return $this->responseJson(true, 200, 'Paid User Deleted', []);
    }
}
