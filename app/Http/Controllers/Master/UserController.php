<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, ArtistUser};
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repositories\Contracts\{UserRepository, ArtistUserRepository};
use App\Transformers\{UserTransformer, ArtistUserTransformer};
use Ramsey\Uuid\Uuid;
use App\Helpers\Common;
use League\Fractal;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserController extends Controller
{    
    public function __construct(UserRepository $userRespository, UserTransformer $userTransformer, ArtistUserRepository $artistUserRepository, ArtistUserTransformer $artistUserTransformers)
    {
        
        $this->userRespository = $userRespository;
        $this->userTransformer = $userTransformer;
        $this->artistUserRepository = $artistUserRepository;
        $this->artistUserTransformer = $artistUserTransformers;
        
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

        $data = $this->userRespository->filtered(
            $this->recursive_change_key(
            $this->filteredRequestParams($request, ['id','fullname', 'email', 'number', 'number', 'age', 'user_type']),
            ["id" => "users.id", "fullname"=>"users.fullname", 'email' => 'users.email', 'number' => 'users.number', 'age' => 'users.age', 'user_type' => 'users.user_type']
            ),[ "users.id" => "=", "users.fullname" => "ILIKE", "users.email" => "=", "users.number" => "=", "users.age" => "=", "users.user_type" => "="]
            )->paginate();

        $uploadParameters['fields'] = ['fullname', 'designation', 'organisation', 'email', 'number', 'password', 'state', 'city', 'dob', 'gender', 'profession', 'user_type'];

        return $this->respondWithCollection($data, $this->userTransformer, true, HttpResponse::HTTP_OK, 'User List', $uploadParameters);
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
            'fullname' => 'required|unique:users,fullname,NULL,id,deleted_at,NULL|regex:/^[\pL\s\-]+$/u|max:150|min:5',
            'designation' => 'alpha',
            'organisation' => 'alpha',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|min:6',
            'number' => 'required|numeric:unique:users,number,NULL,id,deleted_at,NULL',
            'state_id' => 'required|exists:state_master,id',
            'city_id' => 'required|exists:city_master,id',
            'gender' => 'required|in:male,female,other'
            
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $user = $this->userRespository->save($request->all()); 
        
        return $this->respondWithItem($user, $this->userTransformer, true, HttpResponse::HTTP_CREATED, 'User Created');
    }

    public function storeArtist(Request $request) {

        $rules = [

            'fullname' => 'required|unique:users,fullname,NULL,id,deleted_at,NULL|regex:/^[\pL\s\-]+$/u|max:150|min:5',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
            'number' => 'required|numeric:unique:users,number,NULL,id,deleted_at,NULL',
            'state_id' => 'required|exists:state_master,id',
            'city_id' => 'required|exists:city_master,id',
            'profession_id' => 'required|exists:profession_master,id',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required'
            
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $user = $this->artistUserRepository->save($request->all()); 
        
        return $this->respondWithItem($user, $this->artistUserTransformer, true, HttpResponse::HTTP_CREATED, 'User Created');

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
        $stuserate = $this->userRespository->find($id);
        return $this->respondWithItem($user, $this->userTransformer, true, 200, 'User Data');
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
        $user = $this->userRespository->find($id);

        if(! $user) {
            return $this->responseJson(false, 400, 'State Not Found', []);
        }

        $rules = [
            'fullname' => 'required||unique:users,fullname,'.$id.',id,deleted_at,NULL|regex:/^[\pL\s\-]+$/u|max:150|min:5',
            'designation' => 'alpha',
            'organisation' => 'alpha',
            'email' => 'required|email|unique:users,email,'.$id.',id,deleted_at,NULL',
            'password' => 'required|min:6',
            'number' => 'required|numeric:unique:users,number, '.$id.', id,deleted_at,NULL',
            'state_id' => 'required|exists:state_master,id',
            'city_id' => 'required|exists:city_master,id',
            'profession_id' => 'uuid|exists:profession_master,id',
            'dob' => 'date',
            'gender' => 'required|in:male,female,other'
            
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $isUserUpdated = $this->userRespository->update($id, $request->all());

        if(!$isUserUpdated) {
            return $this->responseJson(false, 400, 'Error: User Update Failed', []);
        }

        $updatedUser = $this->userRespository->find($id);
        return $this->respondWithItem($updatedUser, $this->userTransformer, true, 201, 'Provider Updated');  
    }

    public function updateArtist(Request $request, $id) {

        $rules = [

            'fullname' => 'required|unique:users,fullname,'.$id.',id,deleted_at,NULL|regex:/^[\pL\s\-]+$/u|max:150|min:5',
            'email' => 'required|email|unique:users,email,NULL,'.$id.',deleted_at,NULL',
            'password' => 'min:6',
            'confirm_password' => 'same:password',
            'number' => 'required|numeric:unique:users,number,'.$id.',id,deleted_at,NULL',
            'state_id' => 'required|exists:state_master,id',
            'city_id' => 'required|exists:city_master,id',
            'profession_id' => 'required|exists:profession_master,id',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required'
            
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if($validatorResponse !== true) {
            return $this->responseJson(false, HTTPResponse::HTTP_BAD_REQUEST, 'Error', $validatorResponse);
        }

        $isUserUpdated = $this->artistUserRepository->update($id, $request->all());

        if(!$isUserUpdated) {
            return $this->responseJson(false, 400, 'Error: User Update Failed', []);
        }

        $updatedUser = $this->artistUserRepository->find($id);
        return $this->respondWithItem($updatedUser, $this->artistUserTransformer, true, 201, 'Artist Updated');  

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
        $user = $this->userRespository->find($id);
        
        
        if (!$user ) {
            return $this->responseJson(false, 400, 'User Not Found', []);
        }

        $userDelete = $this->userRespository->delete($id);

        if(! $userDelete) {
            return $this->responseJson(false, 200, 'Error: User Delete Failed', []);
        }

        return $this->responseJson(true, 200, 'user Deleted', []);
    }

    public function login(Request $request)
    {
        
        $rules = [
            'username' => 'required|exists:users,username',
            'password' => 'required',
            'user_type' => 'required|in:artist, provider',
            'device_id' => 'required|max:200',
            'device_type' => 'required|in:android,ios',
            'device_name' => 'required|max:150',
            'os' => 'regex:/^[a-zA-Z0-9 _-]*$/',
            'app_version' => 'required|max:10'
        ];
        

        $messages = [
            'username.exists' => 'Incorrect Username or Password'
        ];

        $validatorResponse = $this->validateRequest($request, $rules, $messages);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, 400, 'Error', $validatorResponse);
        }
        
        $username = $request->input('username');
        $password = $request->input('password');
        $user_type = $request->input('user_type');
        $device_id = $request->input('device_id');
        $device_name = $request->input('device_name');
        $device_type = $request->input('device_type');
        $os = $request->input('os');
        $app_version = $request->input('app_version');
        
        $login = $this->userRepository->getUser(['username' => $username]);
        
        if(! $login) {
            return $this->responseJson(false, 400, 'Incorrect Username or Password');
        }
        
        if($this->userRepository->isLoginCheck($password, $login->password)) { 
            
            $attemptLogin = $this->proxy('password', [
                'username'  =>  $username,
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
  
        $v = VersionControl::where('is_active',true)->select('android_version','ios_version')->first();
            $attemptLogin = array_merge($v->toArray(),$attemptLogin, ['campaign_code' => $campaign_code,'login_count'=>$login_count], $login->toArray());
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
}
