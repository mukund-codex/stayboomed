<?php

namespace App\Http\Controllers\User;

use Validator;

use App\Http\Controllers\System\Controller;
use Illuminate\Http\Request;
use App\Models\{User, VersionControl, Campaign, UserFeedback};
use App\Transformers\{UserTransformer, UserAppTransformer, ResetPasswordTransformer}; //, UserFeedbackTransformer
use App\Repositories\Contracts\UserRepository;
use DB;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response as HttpResponse;



class UserController extends Controller 
{
    const REFRESH_TOKEN = 'refreshToken';
    /**
    * Instance of UserRepository
    *
    * @var UserRepository
    */
    private $userRepository;
    
    /**
    * Instanceof UserTransformer
    *
    * @var UserTransformer
    */
    private $userTransformer;
    private $resetTransformer;
    private $userAppTransformer;
    // private $userFeedbackTransformer;

    /**
     * User instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, UserTransformer $userTransformer) //, UserFeedbackTransformer $userFeedbackTransformer
     {
        $this->userRepository = $userRepository;
        $this->userTransformer = $userTransformer;
        // $this->userFeedbackTransformer = $userFeedbackTransformer;
        parent::__construct();
    }

    /**
    * @group User
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->has('id') && ! Uuid::isValid($request->input('id'))) {
            return $this->responseJson(false, HttpResponse::HTTP_NOT_FOUND, 'Record Not Found', ['id' => 'Invalid data']);
        }
        
        $data = $this->userRepository->filtered(
            $this->filter_data(
                $request, 
                array('id','name', 'mobile', 'email_id', 'username', 'emp_id', 'campaign_id'),
                array('id' => '=', 'name' => 'ILIKE', 'mobile' => 'ILIKE', 'email_id' => 'ILIKE', 'username' => 'ILIKE', 'emp_id' => 'ILIKE', 'campaign_id' => 'ILIKE'),
                array('id' => 'id', 'name' => 'first_name', 'mobile' => 'mobile', 'email_id' => 'email_id', 'username' => 'username', 'emp_id' => 'empid', 'campaign_id' => 'campaign_id')
            ),
            $this->filter_data(
                $request, 
                array('geography_name','company_name','campaign_name'),
                array('geography_name' => 'ILIKE', 'company_name' => 'ILIKE', 'campaign_name' => 'ILIKE'),
                array('geography_name' => 'name', 'company_name' => 'name', 'campaign_name' => 'name'),
                array('geography' => ['geography_name'], 'campaign' => ['campaign_name'], 'campaign.company' => ['company_name'])
            ))->paginate();

        $upload_parameters['fields'] = ['company','campaign','type','geography','first_name','last_name','mobile','empid','email','username','password', 'designation'];
        
        return $this->respondWithCollection($data, $this->userTransformer, true, 200, 'User List', $upload_parameters);
    }
    
/**
* @group User
     * Register
     *
     
      * @bodyParam first_name string required Name Example:ABC
      * @bodyParam last_name string required  Email Example:test
      * @bodyParam mobile number optional Mobile Example:1234567890
      * @bodyParam username string required  Username Example:user1234
      * @bodyParam password string required  Password Example:12345678
      * @bodyParam role UUID required Role Example:71acf4d2-4ad7-46f1-b69e-83a26af9ecf
      * @bodyParam geography_id UUID required Geography ID Example:1045a7a4-18ce-468e-95e3-64a4a415a1db
      
      *
      *
      * @response
    *{
*    "success": true,
*    "status": 200,
*    "message": "User Registered",
*    "error": {},
*    "data": {
*        "id": "a03dcfc9-22fe-49d0-b960-ebe07297b559",
*        "first_name": "ABC",
*        "last_name": "test",
*        "mobile": null,
*        "username": "user1234",
*        "created_at": "2020-05-29T17:02:05.000000Z",
*        "updated_at": "2020-05-29T17:02:05.000000Z",
*        "roles": [
*            {
*                "id": "71acf4d2-4ad7-46f1-b69e-83a26af9ecf9",
*                "name": "nsm",
*                "guard_name": "user",
*                "created_at": "2020-05-22 08:22:21",
*                "updated_at": "2020-05-22 08:22:21",
*                "pivot": {
*                    "model_uuid": "a03dcfc9-22fe-49d0-b960-ebe07297b559",
*                    "role_id": "71acf4d2-4ad7-46f1-b69e-83a26af9ecf9",
*                    "model_type": "App\\Models\\User"
*                }
*            }
*        ],
*        "geography": [
*            {
*                "company": {
*                    "id": "055df16b-fd83-4b00-bfd6-12677071dee7",
*                    "name": "test company123"
*                },
*                "campaign": {
*                    "id": "05661e2c-8d31-41e8-a30f-8f0c2ef374f9",
*                    "name": "test campaign112134"
*                },
*                "geography_id": "1045a7a4-18ce-468e-95e3-64a4a415a1db",
*                "type": {
*                    "id": "87086486-ab3f-4e48-89a4-62be47c9c3a3",
*                    "name": "National Zone"
*                },
*                "national_zone": {
*                    "id": "1045a7a4-18ce-468e-95e3-64a4a415a1db",
*                    "name": "Test National"
*                },
*                "zone": null,
*                "region": null,
*                "area": null,
*                "city": null
*            }
*        ]
*    }
*}


     */
    public function register(Request $request)
    {
        $rules = [
            'first_name'   => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:50',
            'last_name'   => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:50',
            'empid'        => 'sometimes|alpha_num|min:3|max:20',
            'mobile'       => ['sometimes',Rule::unique('users',"mobile")->where(function($query) use($request){
                $query->where("campaign_id", $request->input('campaign_id'));
                $query->where("mobile", $request->input('mobile'));
                $query->whereNull('deleted_at');
            })],
            'email'        => ['sometimes','email', Rule::unique('users',"email_id")->where(function($query) use($request){
                $query->where("campaign_id", $request->input('campaign_id'));
                $query->where("email_id", $request->input('email'));
                $query->whereNull('deleted_at');
            })],
            'username'     => ['required',Rule::unique('users',"username")->where(function($query) use($request){
                $query->where("campaign_id", $request->input('campaign_id'));
                $query->where("username", $request->input('username'));
                $query->whereNull('deleted_at');
            }),'alpha_dash','min:1','max:25'],
            'password'     => 'required|min:4|max:25',
            // 'role'         => 'required|exists:roles,id,guard_name,user',
            'campaign_id'  => 'required|exists:campaign,id,deleted_at,NULL',
            'designation'  => 'uuid|exists:designation,id,deleted_at,NULL',
            'geography_id' => 'required|unique:user_geographies,geography_id|exists:geographies,id,deleted_at,NULL',
        ];

        $validatorResponse = $this->validateRequest($request, $rules);
        
        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $user = $this->userRepository->save($request->all());        
        return $this->respondWithItem($user, $this->userTransformer, true, HttpResponse::HTTP_OK, 'User Registered');
    }
       
/**
* @group User
     * Update
     *
      * @bodyParam first_name string required Name Example:ABC
      * @bodyParam last_name string required  Email Example:test
      * @bodyParam mobile number optional Mobile Example:1234567890
      * @bodyParam username string required  Username Example:user1234
      * @bodyParam password string required  Password Example:12345678
      * @bodyParam role UUID required Role Example:71acf4d2-4ad7-46f1-b69e-83a26af9ecf
      * @bodyParam geography_id UUID required Geography ID Example:1045a7a4-18ce-468e-95e3-64a4a415a1db
      *
      * @response
    *{
*    "success": true,
*    "status": 200,
*    "message": "User Updated",
*    "error": {},
*    "data": {
*        "id": "a03dcfc9-22fe-49d0-b960-ebe07297b559",
*        "first_name": "ABC",
*        "last_name": "test",
*        "mobile": null,
*        "username": "user1234",
*        "created_at": "2020-05-29T17:02:05.000000Z",
*        "updated_at": "2020-05-29T17:02:05.000000Z",
*        "roles": [
*            {
*                "id": "71acf4d2-4ad7-46f1-b69e-83a26af9ecf9",
*                "name": "nsm",
*                "guard_name": "user",
*                "created_at": "2020-05-22 08:22:21",
*                "updated_at": "2020-05-22 08:22:21",
*                "pivot": {
*                    "model_uuid": "a03dcfc9-22fe-49d0-b960-ebe07297b559",
*                    "role_id": "71acf4d2-4ad7-46f1-b69e-83a26af9ecf9",
*                    "model_type": "App\\Models\\User"
*                }
*            }
*        ],
*        "geography": [
*            {
*                "company": {
*                    "id": "055df16b-fd83-4b00-bfd6-12677071dee7",
*                    "name": "test company123"
*                },
*                "campaign": {
*                    "id": "05661e2c-8d31-41e8-a30f-8f0c2ef374f9",
*                    "name": "test campaign112134"
*                },
*                "geography_id": "1045a7a4-18ce-468e-95e3-64a4a415a1db",
*                "type": {
*                    "id": "87086486-ab3f-4e48-89a4-62be47c9c3a3",
*                    "name": "National Zone"
*                },
*                "national_zone": {
*                    "id": "1045a7a4-18ce-468e-95e3-64a4a415a1db",
*                    "name": "Test National"
*                },
*                "zone": null,
*                "region": null,
*                "area": null,
*                "city": null
*            }
*        ]
*    }
*}




     */
    public function update(Request $request, $id)
    {   
       /* if (!$request->user('users')) {
            return $this->responseJson(false, 403, 'Forbidden', []);
        }*/
        
        $user = $request->user('users');
        
       /* if ($request->input('campaign_id') != $user->campaign_id) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Invalid Campaign', ['campaign_id' => 'Invalid Campaign']);
        }*/

        $rules = [
            'first_name'   => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:50',
            'last_name'   => 'required|regex:/^[\pL\s\.-]+$/u|min:3|max:50',
            'empid'    => 'sometimes|alpha_num|min:3|max:20',
            'mobile'       => ['sometimes',Rule::unique('users',"mobile")->where(function($query) use($request){
                $query->where("campaign_id", $request->input('campaign_id'));
                $query->where("mobile", $request->input('mobile'));
                $query->whereNull('deleted_at');
            })->ignore($id)],
            'email'       => ['sometimes','email',Rule::unique('users',"email_id")->where(function($query) use($request){
                $query->where("campaign_id", $request->input('campaign_id'));
                $query->where("email_id", $request->input('email'));
                $query->whereNull('deleted_at');
            })->ignore($id)],
            'username'     => ['required',Rule::unique('users',"username")->where(function($query) use($request){
                $query->where("campaign_id", $request->input('campaign_id'));
                $query->where("username", $request->input('username'));
                $query->whereNull('deleted_at');
            })->ignore($id),'alpha_dash','min:1','max:50'],
            // 'role'         => 'required|exists:roles,id,guard_name,user',
            'designation'  => 'uuid|exists:designation,id,deleted_at,NULL',
            'campaign_id'  => 'required|exists:campaign,id,deleted_at,NULL',
            'geography_id' => 'required|exists:geographies,id,deleted_at,NULL',
            'new_password'     => 'sometimes|min:4|max:25',
        ];

        $validatorResponse = $this->validateRequest($request, $rules);
        
        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $user = $this->userRepository->update($id, $request->all());

        if(!$user) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error: User Update Failed', []);
        }

        return $this->respondWithItem($user, $this->userTransformer, true, HttpResponse::HTTP_OK, 'User Registered');
    }

    /**
    * @group App
     * User Login
     *

      * @bodyParam username string required  Username Example:user1234
      * @bodyParam password string required  Password Example:12345678
      * @bodyParam campaign_code string required Campaign Code Example:123422
      * @bodyParam device_id string required  Device ID Example:123
      * @bodyParam device_type number required Device Type Example:android
      * @bodyParam device_name string required  Device Name Example:testdevice
      * @bodyParam os string required  Os Example:android
      * @bodyParam app_version steing required App Version Example:1.0.1
      *
      *@response
      *{
*    "success": true,
*    "status": 200,
*    "message": "Login Successfull",
*    "error": {},
*    "data": {
*  "android_version": "1.0.1",
*        "ios_version": "1",
*        "token_type": "Bearer",
*        "expires_in": 14999,
*        "access_token": "",
*        "refresh_token": "",
*        "campaign_code": "123422",
*         "login_count":"1",
*        "id": "74b9ee4d-6666-4d61-8adb-718beea00318",
*        "first_name": "ABC",
*        "last_name": "test new update",
*        "mobile": null,
*        "username": "user1234",
*        "avatar": null,
*        "deleted_at": null,
*        "roles": [
*            {
*                "id": "71acf4d2-4ad7-46f1-b69e-83a26af9ecf9",
*                "name": "nsm",
*                "guard_name": "user",
*                "created_at": "2020-05-22 08:22:21",
*                "updated_at": "2020-05-22 08:22:21",
*                "pivot": {
*                    "model_uuid": "74b9ee4d-6666-4d61-8adb-718beea00318",
*                    "role_id": "71acf4d2-4ad7-46f1-b69e-83a26af9ecf9",
*                    "model_type": "App\\Models\\User"
*                }
*            }
*        ]
*    }
*}
     */
    public function login(Request $request)
    {
      
        $rules = [
            'email' => 'required|exists:users,email',
            'password' => 'required',
            'device_id' => 'required|max:200',
            'device_type' => 'required|in:android,ios',
            'device_name' => 'required|max:150',
            'os' => 'regex:/^[a-zA-Z0-9 _-]*$/',
            'app_version' => 'required|max:10'
        ];
        

        $messages = [
            'email.exists' => 'Incorrect email or Password'
        ];

        $validatorResponse = $this->validateRequest($request, $rules, $messages);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, 400, 'Error', $validatorResponse);
        }
        
        $email = $request->input('email');
        $password = $request->input('password');
        $campaign_code = $request->input('campaign_code');
        $device_id = $request->input('device_id');
        $device_name = $request->input('device_name');
        $device_type = $request->input('device_type');
        $os = $request->input('os');
        $app_version = $request->input('app_version');
        
        $login = $this->userRepository->getUser(['email' => $email]);
        
        if(! $login) {
            return $this->responseJson(false, 400, 'Incorrect Email or Password');
        }
        
        if($this->userRepository->isLoginCheck($password, $login->password)) { 

            $attemptLogin = $this->proxy('password', [
                'email'  =>  $email,
                'password'  =>  $password,
                'provider' => 'users'
            ]);
            
            if(array_key_exists('error', $attemptLogin)) {
                return $this->responseJson(false, 401, 'Error', $attemptLogin, []);
            }


            $login_count = count(\App\Models\User::find($login->id)->deviceInfo()->get());

            if($device_id) {
                $device_info = [];
                $device_info['user_id'] = $login->id;
                $device_info['device_id'] = $device_id;
                $device_info['device_info'] = [
                    'device_name' => $device_name,
                    'device_type' => $device_type,
                    'os' => $os,
                    'app_version' => $app_version
                ];

                $deviceData = \App\Models\DeviceInfo::create($device_info);
            }

            

        $v = VersionControl::where('is_active',true)->select('android_version','ios_version')->first()->toArray();
        

            $attemptLogin = array_merge($v,$attemptLogin, ['campaign_code' => $campaign_code,'login_count'=>$login_count], $login->toArray());
            
            return $this->responseJson(true, 200, 'Login Successfull', [], $attemptLogin);
        }

        return $this->responseJson(false, 400, 'Incorrect Username or Password', []);
    }

    /**
    * @group App
     * Logout User
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();

        $refreshToken = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke(); 
        return $this->responseJson(true, 200, 'Logout out Successfully');
    }


    /**
    * @group User
     * List
     *
     */

    public function list()
    {
        return \response()->json(['success' => true, 'status' => 200, 'message' => 'Login Success', 'error' => [], 'data' => []], 200);
    }


    /**
    * @group User
     * Refresh
     *
     */

    public function refresh(Request $request)
    {
        $rules = [
            'refresh_token' => 'required'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);
        
        if ($validatorResponse !== true) {
            return $this->responseJson(false, 400, 'Error', $validatorResponse);
        }
        
        $refresh_token = $request->input('refresh_token');

        $attemptRefresh = $this->proxy('refresh_token', [
            'refresh_token' => $refresh_token,
            'provider' => 'users'
        ]);

        if(array_key_exists('error', $attemptRefresh)) {
            return $this->responseJson(false, 401, 'Error', $attemptRefresh, []);
        }

        return $this->responseJson(true, 201, 'New Access Token', [], $attemptRefresh);
    }


    /**
    * @group User
     * Roles
     *
     */

    public function roles()
    {
        $data = $this->userRepository->roles();
        return $this->responseJson(true, 200, 'Roles List', [], ['data' => $data]);
    }

    /**
    * @group User
     * Upload CSV
     */
     public function upload(Request $request)
    {
        $rules = [
            'upload_file'  => 'required|file|mimes:txt,csv',
        ];
        
        $validatorResponse = $this->validateRequest($request, $rules);
        
        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $is_uploaded = $this->userRepository->import($request);

        return $this->responseJson(true, HttpResponse::HTTP_OK, 'User Uploaded', $is_uploaded['error'], $is_uploaded['data']);
    }
/**
* @group App
     * User Profile
     *

      * @bodyParam campaign_code string required Campaign Code Example:122
      *
      *@response
  *{
*    "success": true,
*    "status": 200,
*    "message": "User Profile",
*    "error": {},
*    "data": {
*        "first_name": "test",
*        "last_name": "RSM",
*        "mobile": "1234567890",
*        "email_id": "testrsm@gmail.com",
*        "username": "testrsm",
*        "avatar": null,
*        "designation": "rsm",
*        "geography": "test region",
*        "completed": 0,
*        "in_progress": 0,
*        "not_started": 100,
*        "not_passed": 0,
*        "parent": {
*            "name": "test ZSM",
*            "role": "zsm"
*        }
*    }
*}
     */
    
    public function profile(Request $request,UserAppTransformer $userAppTransformer){

        $this->userAppTransformer = $userAppTransformer ; 
      
        if (!$request->user('users')) {
            return $this->responseJson(false, 403, 'Forbidden', []);
        }

        $username = $request->user('users')->username ; 
        $rules = [
        'campaign_code' => 'required|exists:campaign,cid|is_appuser_campaign_exists:'.$username,
        ];

        $validatorResponse = $this->validateRequest($request, $rules);
        
        if ($validatorResponse !== true) {
            return $this->responseJson(false, 400, 'Error', $validatorResponse);
        }

        $user_id = $request->user('users')->id;
        $campaign_code = $request->input('campaign_code');

        $campaign = \App\Models\Campaign::where('cid',$campaign_code)->first();
        
        if(!$campaign){
            return $this->responseJson(false, 400, 'Campaign Code not exists.');
        }


        $res = $this->userRepository->getProfile($user_id,$campaign->id);


        if(!$res) {
            return $this->responseJson(false, 400, 'No Record Found', []);
        }

        
        return $this->respondWithItem($res, $this->userAppTransformer, true, 200, 'User Profile');
        

/*
        if(!$res){
              
         return $this->responseJson(true, 400, 'Something went wrong');

            }
     
          return $this->responseJson(true, 200, 'User Profile',[],$res);*/    
     

    }

    public function resetpassword(Request $request,ResetPasswordTransformer $transformer){
        
      $this->resetTransformer = $transformer ; 
      
        if (!$request->user('users')) {
            return $this->responseJson(false, 403, 'Forbidden', []);
        }

      $rules = [
            'old_password' => 'required',
            'password' => 'required|min:6|max:20|confirmed',
            'password_confirmation' => 'required'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);
        
        if ($validatorResponse !== true) {
            return $this->responseJson(false, 400, 'Error', $validatorResponse);
        }

        $password = $request->input('password');
        $old_password = $request->input('old_password');
        $id = $request->user('users')->id;
        $userold_password = $request->user('users')->password;

        if(!$this->userRepository->isLoginCheck($old_password,$userold_password)){
             return $this->responseJson(false, 400, 'Error',['old_password'=>'Old Password Not Matched'], []);
       }


        $res = $this->userRepository->password_update($id, $request->all());

        if(!$res) {
            return $this->responseJson(false, 400, 'Error: Password Update Failed', []);
        }

        
        return $this->respondWithItem($res, $this->resetTransformer, true, 200, 'Password Updated');

    }


    public function download(Request $request)
    {
        if($request->has('id') && ! Uuid::isValid($request->input('id'))) {
            return $this->responseJson(false, HttpResponse::HTTP_NOT_FOUND, 'Record Not Found', ['id' => 'Invalid data']);
        }
        
         $filteredData = $this->userRepository->filtered(
            $this->filter_data(
                $request, 
                array('id','name', 'mobile', 'email_id', 'username', 'emp_id', 'campaign_id'),
                array('id' => '=', 'name' => 'ILIKE', 'mobile' => 'ILIKE', 'email_id' => 'ILIKE', 'username' => 'ILIKE', 'emp_id' => 'ILIKE', 'campaign_id' => 'ILIKE'),
                array('id' => 'id', 'name' => 'first_name', 'mobile' => 'mobile', 'email_id' => 'email_id', 'username' => 'username', 'emp_id' => 'empid', 'campaign_id' => 'campaign_id')
            ),
            $this->filter_data(
                $request, 
                array('geography_name','company_name','campaign_name'),
                array('geography_name' => 'ILIKE', 'company_name' => 'ILIKE', 'campaign_name' => 'ILIKE'),
                array('geography_name' => 'name', 'company_name' => 'name', 'campaign_name' => 'name'),
                array('geography' => ['geography_name'], 'campaign' => ['campaign_name'], 'campaign.company' => ['company_name'])
            ))->get();


        return $this->userRepository->export($filteredData);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user('admin')) {
            return $this->responseJson(false, HttpResponse::HTTP_FORBIDDEN, 'Forbidden', []);
        }

        $request_user = $this->userRepository->find($id);
        
        if (!$request_user) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'User Not Found', []);
        }
        
        /*if(
            $request_user->doctors()->exists() || 
            $request_user->groups()->exists()  
            ) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'User Delete Failed. Relationship Exists', []);
        }*/

        if($request_user->roles()->count() > 0) {
            $revokeRoles = $request_user->roles()->detach();
    
            if(! $revokeRoles) {
                return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error: User Delete Failed', []);
            }
        }

        if($request_user->geography()->exists()) {
            $request_user->geography()->detach();
        }

        $user_delete = $this->userRepository->delete($id);

        if(!$user_delete) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error: User Delete Failed', []);
        }

        return $this->responseJson(true, HttpResponse::HTTP_OK, 'User Deleted', []);
    }

     public function destroy_all(Request $request)
    {
        $rules = [
            'id'   => 'required|array',
            'id.*' => 'required|exists:users,id,deleted_at,NULL',
        ];
        
        $validatorResponse = $this->validateRequest($request, $rules);
        
        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $user_delete = $this->userRepository->delete_all($request->all());

        return $this->responseJson(true, HttpResponse::HTTP_OK, 'Users Deleted', [], ['data' => (int) $user_delete]);
    }

    /**
     * User Feedback.
     * 
     * This endpoint allows you to user feedback
     * 
     * @bodyParam rating integer required Rating.
     * @bodyParam category string required Category.
     * @bodyParam description string Description.
     * @bodyParam file file File.
     * @bodyParam campaign_id string Campaign Id
     *
     */
    public function feedback(Request $request)
    {
        if (!$request->user('users')) {
            return $this->responseJson(false, HttpResponse::HTTP_FORBIDDEN, 'Forbidden', []);
        }

        $user = $request->user('users');
        
        $rules = [
            'rating'       => 'required|integer|min:1|max:5',
            'category'     => 'required|in:suggestion,compliment,wrong',
            'description'  => 'sometimes|min:2|max:200',
            'file'         => 'sometimes|image|mimes:jpeg,jpg,png|max:15000',
            'campaign_code'  => 'required|exists:campaign,cid,deleted_at,NULL',
        ];

        $validatorResponse = $this->validateRequest($request, $rules);
                
        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }
        
        $feedback = $this->userRepository->feedback($request);        
        return $this->responseJson(true, HttpResponse::HTTP_OK, 'Feedback Saved', []);
    }

    /**
     * Feedback List.
     * 
     * This endpoint allows you to user feedback list
     * 
     * @bodyParam campaign_id string Campaign Id
     *
     */
    public function feedback_list(Request $request)
    {
        if (!$request->user('admin')) {
            return $this->responseJson(false, HttpResponse::HTTP_FORBIDDEN, 'Forbidden', []);
        }
        
        $rules = [
            'campaign_id'  => 'required|exists:campaign,id,deleted_at,NULL',
        ];

        $validatorResponse = $this->validateRequest($request, $rules);
                
        if ($validatorResponse !== true) {
            return $this->responseJson(false, HttpResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $feedback_list = UserFeedback::where(['campaign_id' => $request->input('campaign_id')])->paginate();        
        return $this->respondWithCollection($feedback_list, $this->userFeedbackTransformer, true, HttpResponse::HTTP_OK, 'Feedback List');
    }

}
