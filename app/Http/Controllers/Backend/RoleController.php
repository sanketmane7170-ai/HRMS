<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'roles');
    }
    protected $defaultRole = [1, 2, 3];
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        canPerform('Manage Role');
        if ($request->ajax()) {
            // 'admin' is now an editable, permission-driven role so it shows in
            // the list. Only 'superadmin' (god-mode) stays hidden/uneditable.
            $data = Role::whereNotIn('name', [User::ROLE_SUPER_ADMIN]);
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return ucwords($row->name);
                })
                ->addColumn('action', function ($row) {
                    $url = route('backend.roles.edit', $row);
                    $deleteUrl = route('backend.roles.destroy', $row);
                    $btn = createActionButton($url, 'Edit', 'btn-warning', 'fa fa-edit');
                    if (!in_array($row->id, $this->defaultRole)) {
                        $btn .= createActionButton($deleteUrl, 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'role_id'])
                ->make(true);
        }
        return view('backend.roles.index');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        canPerform('Create Role');
        $data = $request->validate([
            'name' => 'required|unique:roles,name',
            'priority' => 'required|unique:roles,priority'
        ]);
        $response = getErrorResponse();
        try {
            $role = Role::create($data);
            $role->syncPermissions($this->validPermissions($request->permissions));
            $response = getSuccessResponse(createFlashMessage("Role", "Created"));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        canPerform('Edit Role');
        $permissions = Permission::pluck('name', 'name');
        // dd($permissions);die;
        return view('backend.roles.edit', compact('role', 'permissions'));
        // return response()->json([
        //     'success' => true,
        //     'html' => $html
        // ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        canPerform('Edit Role');
        $data =  $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'priority' => 'required|unique:roles,priority,' . $role->id
        ]);
        $response = getErrorResponse();

        try {
            // if (!in_array($role->id, $this->defaultRole)) {
            //     $role->update($data);
            // }
            $role->update($data);
            $role->syncPermissions($this->validPermissions($request->permissions));
            $response = getSuccessResponse(createFlashMessage("Role", "Updated"));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Keep only the submitted permission names that actually exist as
     * Permission records for the web guard. The module-grouped picker builds
     * names like "Manage User" from config; a few config labels have no
     * matching permission, and Spatie throws if any unknown name is synced.
     */
    protected function validPermissions($permissions): array
    {
        return Permission::whereIn('name', (array) $permissions)
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        canPerform('Delete Role');
        $response = getErrorResponse();
        if (!in_array($role->id, $this->defaultRole)) {
            try {
                $role->delete();
                $response = getSuccessResponse(createFlashMessage("Role", "Deleted"));
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }
        } else {
            $response['message'] = __trans('you_are_not_allowed_to_delete_default_roles');
        }

        return response()->json($response);
    }
}
