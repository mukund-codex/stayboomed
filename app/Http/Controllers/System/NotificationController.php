<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\System\Controller;
use Illuminate\Http\Request;
use App\Transformers\NotificationTransformer;
use App\Repositories\Contracts\NotificationRepository;
use Ramsey\Uuid\Uuid;
use \App\Models\{NotificationLog};
use Log;

class NotificationController extends Controller
{
    /**
     * Instanceof NotificationRepository
     *
     * @var NotificationRepository
     */
     private $notificationRespository;

    /**
     * Instanceof NotificationTransformer
     *
     * @var NotificationTransformer
     */
    private $notificationTransformer;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(NotificationRepository $notificationRespository, NotificationTransformer $notificationTransformer)
    {
        $this->notificationRespository = $notificationRespository;
        $this->notificationTransformer = $notificationTransformer;
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (\array_key_exists('id', $request->all())) {
            if(! Uuid::isValid($request->get('id'))){
                return $this->responseJson(false, 404, 'Record Not Found', ['id' => 'Invalid data']);
            }
        }
        
        $data = $this->notificationRespository->filtered(
            $this->filteredRequestParams($request, ['id']),
            [ "id" => "="]
            )->paginate();
        return $this->respondWithCollection($data, $this->notificationTransformer, true, 200, 'Notification List');
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function notify_list(Request $request)
    {
        $user = $request->user('user');
        if (!$user) {
            return $this->responseJson(false, 403, 'Forbidden', []);
        }

        if(! $user->groups()->exists()) {
            return $this->responseJson(false, 400, 'User not associated with Camp Group', []);
        }

        $user_id = $user->id;

        $isUserExistInGroup = \App\Models\Group::with(['users' => function($q) use($user_id) {
            $q->where('user_id', '=', $user_id);
        }])
        ->first();

        $group_id = $isUserExistInGroup->id;
        
        if (\array_key_exists('id', $request->all())) {
            if(! Uuid::isValid($request->get('id'))){ 
                return $this->responseJson(false, 404, 'Record Not Found', ['id' => 'Invalid data']);
            }
        }

        $data = $this->notificationRespository->filtered(
            $this->filteredRequestParams($request, ['id']),
            [ "id" => "="]
            )->paginate();

        return $this->respondWithCollection($data, $this->notificationTransformer, true, 200, 'Doctor List');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $language = $this->notificationRespository->find($id);
        return $this->respondWithItem($language, $this->notificationTransformer, true, 200, 'Language Data');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function notificationCBService(Request $request)
    {
        $result = $request->input('data');
        $request_id = $request->input('request_id');

        Log::info('Request', [
            'request_id' => $request_id,
            'request' => $request->all()
        ]);

        if(! isset($result) ||  ! isset($request_id)) {
            Log::info('Notification Service Result Empty', [
                'request' => $request->all()
            ]);
            
            return 'not ok';
        }
        
        $data = [];
        $data['response'] = $request->all();
        $data['is_processed'] = true;
        $isServiceReq = NotificationLog::where(['request_id' =>  $request_id])->update($data);

        if($isServiceReq) {
            Log::info('Notification Request Success', [
                'request_id' => $request_id,
                'data' => $isServiceReq,
                'response' => (int) $isServiceReq
            ]);

            return 'ok';
        }
    }
}
