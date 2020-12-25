<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\System\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Transformers\UserRoleTransformer;
use App\Repositories\Contracts\RoleUserRepository;
use Ramsey\Uuid\Uuid;

class RoleUserController extends Controller
{
    /**
    * Instanceof UserRoleTransformer
    *
    * @var UserRoleTransformer
    */
    private $roleTransformer;

    /**
    * Instanceof RoleUserRepository
    *
    * @var RoleUserRepository
    */
    private $roleUserRepository;

    /**
     * Role instance.
     *
     * @return void
     */
    public function __construct(RoleUserRepository $roleUserRepository, UserRoleTransformer $roleTransformer)
    {
        $this->roleUserRepository = $roleUserRepository;
        $this->roleTransformer = $roleTransformer;
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
        
        $data = $this->roleUserRepository->filtered(
            $this->filteredRequestParams($request, ['id','name']),
            [ "id" => "=", "name" => "ILIKE"]
            )->paginate();

        return $this->respondWithCollection($data, $this->roleTransformer, true, 200, 'Role List');
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
            'name'=>'required|alpha_dash|unique:roles|max:15',
            'permissions' =>'array',
            'permissions.*' => 'uuid|exists:permissions,id,guard_name,user'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, 400, 'Error', $validatorResponse);
        }

        $role = $this->roleUserRepository->save($request->all());

        return $this->respondWithItem($role, $this->roleTransformer, true, 200, 'Role Added');
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (\array_key_exists('id', $request->all())) {
            if(! Uuid::isValid($request->get('id'))){
                return $this->responseJson(false, 404, 'Record Not Found', ['id' => 'Invalid data']);
            }
        }

        $role = Role::findOrFail($id); 

        if (!$role instanceof Role) {
            return $this->responseJson(false, 400, 'Role Not Found', []);
        }

        $rules = [
            'name'=>'required|alpha_dash|min:3|max:15|unique:roles,name,'.$id.',id',
            'permissions' =>'array',
            'permissions.*' => 'uuid|exists:permissions,id'
        ];

        $validatorResponse = $this->validateRequest($request, $rules);

        if ($validatorResponse !== true) {
            return $this->responseJson(false, 400, 'Error', $validatorResponse);
        }
        
        $isRoleUpdated = $this->roleUserRepository->update($id, $request->all());

        if(!$isRoleUpdated) {
            return $this->responseJson(false, 400, 'Error: Role Update Failed', []);
        }

        $updatedRole = $this->roleUserRepository->find($id);

        return $this->respondWithItem($updatedRole, $this->roleTransformer, true, 200, 'Role Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if (!$role instanceof Role) {
            return $this->responseJson(false, 400, 'Role Not Found', []);
        }

        $role->revokePermissionTo($role);
        $role->delete();

        return $this->respondWithItem($role, $this->roleTransformer, true, 200, 'Role Deleted');
    }

    public function export(Request $request)
    {
        if (\array_key_exists('id', $request->all())) {
            if(! Uuid::isValid($request->get('id'))){
                return $this->responseJson(false, 404, 'Record Not Found', ['id' => 'Invalid data']);
            }
        }
        
        $filtered_data = $this->roleUserRepository->filtered(
            $this->filteredRequestParams($request, ['id','name']),
            [ "id" => "=", "name" => "ILIKE"]
            )->get();

        return response()->json(['status' => true, 'message' => 'Role Export', 'data' => $filtered_data]);
    }
}
