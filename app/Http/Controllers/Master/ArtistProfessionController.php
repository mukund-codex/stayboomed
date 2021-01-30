<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArtistProfession;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\ArtistProfessionRepository;
use App\Transformers\ArtistProfessionTransformer;
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ArtistProfessionController extends Controller
{
    //
    public function __construct(ArtistProfessionRepository $artistProfessionRespository, ArtistProfessionTransformer $artistProfessionTransformer)
    {
        
        $this->artistProfessionRespository = $artistProfessionRespository;
        $this->artistProfessionTransformer = $artistProfessionTransformer;
        
    }
}
