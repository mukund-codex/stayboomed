<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\System\Controller;
use Illuminate\Http\Request;
use App\Models\UserPermission;
use App\Models\UserRole;
use App\Transformers\UserPermissionTransformer;

class PermissionUserController extends Controller
{
       /**
    * Instanceof UserPermissionTransformer
    *
    * @var UserPermissionTransformer
    */
    private $permissionTransformer;

    /**
     * Role instance.
     *
     * @return void
     */
    public function __construct(UserPermissionTransformer $permissionTransformer)
    {
        $this->permissionTransformer = $permissionTransformer;
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = UserPermission::where(['guard_name' => 'user'])->paginate();
        return $this->respondWithCollection($data, $this->permissionTransformer, true, 200, 'Permission List');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name'=>'required|unique:permissions,name|min:3|max:40',
            'roles' => 'array',
            'roles.*' => 'uuid|exists:roles,id,guard_name,user'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, 400, 'Error', $validatorResponse);
        }

        $permission = new UserPermission();
        $permission->name = $request->name;
        $permission->guard_name = 'user';
        $permission->save();

        if ($request->roles <> '') { 
            foreach ($request->roles as $key => $value) {
                $role = UserRole::find($value); 
                $role->permissions()->attach($permission);
            }
        }

        return $this->respondWithItem($permission, $this->permissionTransformer, true, 200, 'Permission Added');
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $this->validate($request, [
            'name'=>'required',
        ]);

        $permission->name=$request->name;
        $permission->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();
    }

    /**
     * Export a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        if (\array_key_exists('id', $request->all())) {
            if(! Uuid::isValid($request->get('id'))){
                return $this->responseJson(false, 404, 'Record Not Found', ['id' => 'Invalid data']);
            }
        }
        
        $data = UserPermission::where(['guard_name' => 'user'])->get();
        return response()->json(['status' => true, 'message' => 'Permission Export', 'data' => $data]);
    }
}
