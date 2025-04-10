<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArtistDetails;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\ArtistDetailsRepository;
use App\Repositories\Contracts\UserRepository;
use App\Transformers\ArtistDetailsTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ArtistDetailsController extends Controller
{
    public function __construct(ArtistDetailsRepository $artistDetailsRespository, UserRepository $userRepository, ArtistDetailsTransformer $artistDetailsTransformer)
    {
        
        $this->artistDetailsRespository = $artistDetailsRespository;
        $this->userRepository = $userRepository;
        $this->artistDetailsTransformer = $artistDetailsTransformer;
        
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

        $data = $this->artistDetailsRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','user_id']),
            ["id" => "artist_details.id", "user_id"=>"artist_details.user_id"]
            ),[ "artist_details.id" => "=", "artist_details.user_id" => "="]
            )->paginate();

        $uploadParameters['fields'] = ['User name', 'jon name'];

        return $this->respondWithCollection($data, $this->artistDetailsTransformer, true, HttpResponse::HTTP_OK, 'Subscription List', $uploadParameters);
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
            'corresponding_address' => 'required',
            'permanent_address' => 'required',
            'profile_picture' => 'image|mimes:jpeg,jpg,png|max:5000',
            'cover_picture' => 'image|mimes:jpeg,jpg,png|max:5000'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $details = $this->artistDetailsRespository->save($request->all()); 
        
        return $this->respondWithItem($details, $this->artistDetailsTransformer, true, HttpResponse::HTTP_CREATED, 'Artist Details Created');
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

    public function updateArtistAlternateDetails(Request $request) {

        $rules = [

            'alternate_email' => 'email',
            'primary_email' => 'required|email',
            'alternate_number' => 'numeric',
            'primary_number' => 'required|numeric',
            'user_id' => 'required|exists:artist_details,user_id',
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        $requestData = $request->all();

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $isUserAlternateUpdated = $this->artistDetailsRespository->updateAlternateDetails($requestData['user_id'], $request->all());

        $isUserPrimaryUpdated = $this->userRepository->updatePrimaryDetails($requestData['user_id'], $request->all());

        if(!$isUserAlternateUpdated || !$isUserPrimaryUpdated ) {
            return $this->responseJson(false, 400, 'Error: User Update Failed', []);
        }

        $updatedUser = $this->artistDetailsRespository->findOneBy(['user_id' => $requestData['user_id']]);
        return $this->respondWithItem($updatedUser, $this->artistDetailsTransformer, true, 201, 'Artist Updated');

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


        /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getAlternateDetails($id)
    {
        
        $alternateDetails = $this->artistDetailsRespository->findOneBy(['user_id' => $id]);
        $primaryDetails = $this->userRepository->find($id);

        $alternateEmail = !empty($alternateDetails->alternate_email) ? $alternateDetails->alternate_email : '';  
        $alternateNumber = !empty($alternateDetails->alternate_number) ? $alternateDetails->alternate_number : '';  

        $data = ['alternate_email' => $alternateEmail, 
                 'alternate_number' => $alternateNumber, 
                 'primary_email' => $primaryDetails->email, 
                 'primary_number' => $primaryDetails->number];

        return $this->responseJson(true, 200, 'User Details', [], $data);
    }
}
