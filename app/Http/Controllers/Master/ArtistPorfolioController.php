<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArtistPorfolio;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\ArtistPorfolioRepository;
use App\Transformers\ArtistPorfolioTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ArtistPorfolioController extends Controller
{   

    public function __construct(ArtistPorfolioRepository $artistPorfolioRespository, ArtistPorfolioTransformer $artistPorfolioTransformer)
    {
        
        $this->artistPorfolioRespository = $artistPorfolioRespository;
        $this->artistPorfolioTransformer = $artistPorfolioTransformer;
        
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

        $data = $this->artistPorfolioRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','user_id']),
            ["id" => "artist_porfolio.id", "user_id"=>"artist_porfolio.user_id"]
            ),[ "artist_porfolio.id" => "=", "artist_porfolio.user_id" => "="]
            )->paginate();

        $uploadParameters['fields'] = [];

        return $this->respondWithCollection($data, $this->artistPorfolioTransformer, true, HttpResponse::HTTP_OK, 'Artist Portfolio List', $uploadParameters);
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
            'audio_title' => '',
            'audio_file' => 'mimes:mp3,wav',
            'video_title' => '',
            'video_file' => 'mimes:mp4,avi,mov,flv,avg',
            'picture_title' => '',
            'picture_file' => 'image|mimes:jpeg,jpg,png|max:5000'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $details = $this->artistPorfolioRespository->save($request->all()); 
        
        return $this->respondWithItem($details, $this->artistPorfolioTransformer, true, HttpResponse::HTTP_CREATED, 'Artist Details Created');
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
