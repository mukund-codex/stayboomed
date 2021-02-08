<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\SubscriptionRepository;
use App\Transformers\SubscriptionTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function __construct(SubscriptionRepository $subscriptionRepository, SubscriptionTransformer $subscriptionTransformer)
    {
        
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionTransformer = $subscriptionTransformer;
        
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

        $data = $this->subscriptionRepository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','title', 'price', 'job_apply', 'subscription_type']),
            ["id" => "subscription.id", "title"=>"subscription.title", "price" => "subscription.price", "job_apply" => "subscription.job_apply", "subscription_type" => "subscription.subscription_type", "profession" => "profession_master.name", "budget" => "subscription.budget"]
            ),[ "subscription.id" => "=", "subscription.title" => "ILIKE", "subscription.price" => "ILIKE", "subscription.job_apply" => "=", "subscription.subscription_type" => "ILIKE"]
            )->paginate();

        $uploadParameters['fields'] = [];

        return $this->respondWithCollection($data, $this->subscriptionTransformer, true, HttpResponse::HTTP_OK, 'Jobs List', $uploadParameters);
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
            'title' => 'required|min:5',
            'price' => 'required|numeric',
            'job_apply' => 'required|numeric',
            'subscription_type' => 'required',
            'expiry' => 'required|numeric'

        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse != 'true') {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $subscription = $this->subscriptionRepository->save($request->all()); 
        
        return $this->respondWithItem($subscription, $this->subscriptionTransformer, true, HttpResponse::HTTP_CREATED, 'Subscription Created');
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
        $subscription = $this->subscriptionRepository->find($id);
        return $this->respondWithItem($subscription, $this->subscriptionTransformer, true, 200, 'Subscription Data');
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
        $subscription = $this->subscriptionRespository->find($id);

        if(! $subscription) {
            return $this->responseJson(false, 400, 'Subscription Not Found', []);
        }
        
        $rules = [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|min:5',
            'price' => 'required|numeric',
            'job_apply' => 'required|numeric',
            'subscription_type' => 'required'

        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $isSubscriptionUpdated = $this->subscriptionRespository->update($id, $request->all());

        if(!$isSubscriptionUpdated) {
            return $this->responseJson(false, 400, 'Error: Subscription Update Failed', []);
        }

        $updatedSubscription = $this->subscriptionRespository->find($id);
        return $this->respondWithItem($updatedSubscription, $this->subscriptionTransformer, true, 201, 'Subscription Updated');  
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
        $subscription = $this->subscriptionRepository->find($id);
        
        
        if (!$subscription ) {
            return $this->responseJson(false, 400, 'Subscription Not Found', []);
        }

        $subscriptionDelete = $this->subscriptionRepository->delete($id);

        if(! $subscriptionDelete) {
            return $this->responseJson(false, 200, 'Error: Subscription Delete Failed', []);
        }

        return $this->responseJson(true, 200, 'Subscription Deleted', []);
    }
}
