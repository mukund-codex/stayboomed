<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\FeedbackRepository;
use App\Transformers\FeedbackTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    public function __construct(FeedbackRepository $feedbackRespository, FeedbackTransformer $feedbackTransformer)
    {
        
        $this->feedbackRespository = $feedbackRespository;
        $this->feedbackTransformer = $feedbackTransformer;
        
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

        $data = $this->feedbackRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','user_id', 'ratings', 'comments']),
            ["id" => "feedbacks.id", "user_id"=>"feedbacks.user_id", "ratings" => "feedbacks.ratings", "comments" => "feedbacks.comments"]
            ),[ "feedbacks.id" => "=", "feedbacks.user_id" => "=", "feedbacks.ratings" => "ILIKE", "feedbacks.comments" => "ILIKE"]
            )->paginate();

        $uploadParameters['fields'] = [];
       
        return $this->respondWithCollection($data, $this->feedbackTransformer, true, HttpResponse::HTTP_OK, 'Feedback List', $uploadParameters);
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
            "user_id" => 'required|numeric|exists:users,id',
            "ratings" => 'required',
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse != true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $feedback = $this->feedbackRespository->save($request->all()); 
        
        return $this->respondWithItem($feedback, $this->feedbackTransformer, true, HttpResponse::HTTP_CREATED, 'Feedback Submitted');
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
