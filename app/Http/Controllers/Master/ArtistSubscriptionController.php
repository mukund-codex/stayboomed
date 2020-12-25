<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArtistSubscription;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\ArtistSubscriptionRepository;
use App\Transformers\ArtistSubscriptionTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DB;

class ArtistSubscriptionController extends Controller
{

    public function __construct(ArtistSubscriptionRepository $artistSubscriptionRespository, ArtistSubscriptionTransformer $artistSubscriptionTransformer)
    {
        
        $this->artistSubscriptionRespository = $artistSubscriptionRespository;
        $this->artistSubscriptionTransformer = $artistSubscriptionTransformer;
        
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

        $data = $this->artistSubscriptionRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','user_id', 'subscription_id']),
            ["id" => "applied_jobs.id", "user_id"=>"applied_jobs.user_id", 'subscription_id' => 'applied_jobs.subscription_id']
            ),[ "applied_jobs.id" => "=", "applied_jobs.user_id" => "=", 'applied_jobs.subscription_id' => "="]
            )->paginate();

        $uploadParameters['fields'] = ['User name', 'jon name'];

        return $this->respondWithCollection($data, $this->artistSubscriptionTransformer, true, HttpResponse::HTTP_OK, 'Subscription List', $uploadParameters);
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
            'subscription_id' => 'required|exists:subscription,id'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $user_id = $request->input('user_id');
        $subscription_id = $request->input('subscription_id');

        $alreadyApplied = DB::table('artist_subscription')
                    ->where('user_id', '=', $user_id)
                    ->where('subscription_id', '=', $subscription_id)->get()->toArray();

        if($alreadyApplied) 
        {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Already Subscribed.');
        }

        $subscriptions = $this->artistSubscriptionRespository->save($request->all()); 
        
        return $this->respondWithItem($subscriptions, $this->artistSubscriptionTransformer, true, HttpResponse::HTTP_CREATED, 'Subscribed Successfully.');
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
