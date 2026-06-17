<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\UserPromotion;
use App\Models\User;
use App\Models\Designation;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserPromotionController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'user_promotions');
    }

    /**
     * Display a listing of user promotions.
     */
    public function index(Request $request)
    {

        canPerform('Manage User Promotion');

        if ($request->ajax()) {
            $data = UserPromotion::with(['user', 'oldDesignation', 'newDesignation'])
                ->latest();


            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user', fn($row) => $row->user->name ?? '-')
                ->addColumn('old_designation', fn($row) => $row->oldDesignation->name ?? '-')
                ->addColumn('new_designation', fn($row) => $row->newDesignation->name ?? '-')
                ->addColumn('promotion_date', fn($row) => formatDate($row->promotion_date))
                ->addColumn('remarks', fn($row) => $row->remarks ?? '-')
                ->addColumn('action', function ($row) {
                    $actions = [];
                    if (hasPermission('Edit User Promotion')) {
                        $actions[] = [route('backend.user-promotions.edit', $row->id), 'Edit', 'fa fa-edit', 'edit-button'];
                    }
                    if (hasPermission('Delete User Promotion')) {
                        $actions[] = [route('backend.user-promotions.destroy', $row->id), 'Delete', 'fa fa-trash', 'action-button', 'datatable'];
                    }
                    return createActionDropdownList($actions);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.user-promotions.index');
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create()
    {
        canPerform('Create User Promotion');
        // $users = User::select('id', 'name')->get();
        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('status', User::STATUS_ACTIVE)
            ->get();
        $designations = Designation::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'html' => view('backend.user-promotions.create', compact('users', 'designations'))->render()
        ]);
    }

    /**
     * Store a newly created user promotion in storage.
     */
    public function store(Request $request)
    {
        canPerform('Create User Promotion');

        $data = $request->validate([
            'user_id'            => 'required|exists:users,id',
            // 'old_designation_id' => 'required|exists:designations,id',
            'new_designation_id' => 'required|exists:designations,id',
            'promotion_date'     => 'required|date',
            'remarks'     => 'nullable',
        ]);
        // dd($data);

        $response = getErrorResponse();
        try {
            $user = User::find($data['user_id']);
            if ($user) {
                $data['old_designation_id'] = $user->designation_id;
                UserPromotion::create($data);
                $user->designation_id = $data['new_designation_id'];
                $user->save();
            }
            $response = getSuccessResponse(createFlashMessage('User Promotion', 'Created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing an existing promotion.
     */
    public function edit(UserPromotion $userPromotion)
    {
        canPerform('Edit User Promotion');
        // $users = User::select('id', 'name')->get();
        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('status', User::STATUS_ACTIVE)
            ->get();
        $designations = Designation::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'html' => view('backend.user-promotions.edit', compact('userPromotion', 'users', 'designations'))->render()
        ]);
    }

    /**
     * Update the specified promotion in storage.
     */
    public function update(Request $request, UserPromotion $userPromotion)
    {
        canPerform('Edit User Promotion');

        $data = $request->validate([
            'user_id'            => 'required|exists:users,id',
            'old_designation_id' => 'nullable|exists:designations,id',
            'new_designation_id' => 'required|exists:designations,id',
            'promotion_date'     => 'required|date',
            'remarks'     => 'nullable',
        ]);

        $response = getErrorResponse();
        try {
            $user = User::find($data['user_id']);
            if ($user) {
                $data['old_designation_id'] = $user->designation_id;
                $user->designation_id = $data['new_designation_id'];
                $userPromotion->update($data);

                $user->designation_id = $data['new_designation_id'];
                $user->save();
            }

            $response = getSuccessResponse(createFlashMessage('User Promotion', 'Updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified promotion from storage.
     */
    public function destroy(UserPromotion $userPromotion)
    {
        canPerform('Delete User Promotion');

        $response = getErrorResponse();
        try {
            $userPromotion->delete();
            $response = getSuccessResponse(createFlashMessage('User Promotion', 'Deleted'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
    public function getUserDesignation(User $user)
    {
        // Assuming the user has a 'designation_id' field or relation
        $designation = $user->designation; // or $user->designation->id

        return response()->json([
            'success' => true,
            'designation_id' => $designation->id ?? null,
        ]);
    }
}
