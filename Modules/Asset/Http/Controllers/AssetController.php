<?php

namespace Modules\Asset\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Entities\Asset;
use Modules\Asset\Entities\AssetAssignment;
use Modules\Asset\Enums\AssetStatus;
use Modules\Asset\Http\Requests\AssetStoreRequest;
use Modules\Asset\Http\Requests\AssetUpdateRequest;
use Yajra\DataTables\Facades\DataTables;

class AssetController extends Controller
{

    public function __construct()
    {
        view()->share('activeLink', 'assets');
    }

    /**
     * Display a listing of the asset.
     * @return Renderable
     */
    public function index(Request $request): View|JsonResponse
    {
        canPerform('Manage Asset');
        if ($request->ajax()) {
            $message = __trans('are_you_sure_this_asset_is_no_more_assigned_to_user?');
            $data = Asset::with(['type', 'manufacturer', 'activeAssignment']);
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status->getHtml();
                })
                ->addColumn('assignUser', function ($row) use ($message) {
                    if ($row->activeAssignment) {
                        return $row->activeAssignment->user->name;
                    }
                    return null;
                })
                ->addColumn('action', function ($row) use ($message) {
                    $btn = '';
                    if (hasPermission('Edit Asset')) {
                        $btn = createActionButton(route('backend.asset.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    $btn .= createActionButton(route('backend.asset.show', $row), 'View', 'btn-success', 'fa fa-eye');
                    if ($row->activeAssignment) {
                        if (hasPermission('Edit Asset')) {
                            $btn .= createActionButton(route('backend.asset.un-assign-form', $row), 'Unassign', 'btn-danger edit-button', 'fa fa-times', 'datatable method=POST alert=' . $message);
                        }
                    } else {
                        if (hasPermission('Delete Asset') && !$row->has_active_assignment) {
                            $btn .= createActionButton(route('backend.asset.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                        }
                    }
                    return $btn;
                })
                ->rawColumns(['assignUser', 'action', 'status'])
                ->make(true);
        }
        return view('asset::asset.index');
    }

    /**
     * Show the form for creating a new asset.
     * @return Renderable
     */
    public function create(): JsonResponse
    {
        canPerform('Create Asset');
        $html = view('asset::asset.create')->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created asset in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(AssetStoreRequest $request): JsonResponse
    {
        canPerform('Create Asset');
        $response = getErrorResponse();
        try {
            $asset = Asset::create($request->validated());
            $response = getSuccessResponse(createFlashMessage('Asset', 'created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for creating a new asset.
     * @return Renderable
     */
    public function show(Asset $asset): Renderable
    {
        canPerform('Create Asset');

        return view('asset::asset.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified asset.
     * @param int $id
     * @return Renderable
     */
    public function edit(Asset $asset): JsonResponse
    {
        canPerform('Edit Asset');
        $html = view('asset::asset.edit', compact('asset'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified asset in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(AssetUpdateRequest $request, Asset $asset): JsonResponse
    {
        canPerform('Edit Asset');
        $response = getErrorResponse();
        try {
            $asset->update($request->validated());
            $response = getSuccessResponse(createFlashMessage('Asset', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified asset from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Asset $asset)
    {
        canPerform('Delete Asset');
        $response = getErrorResponse();
        //if (!$asset->assignments()->where('status', AssetStatus::Available)->exists()) {
        if ($asset->assignments()->where('status', AssetStatus::Available)) {
            $asset->assignments()->delete();
            $asset->delete();
            $response = getSuccessResponse(createFlashMessage('Asset', 'deleted'));
        } else {
            $response['message'] = __trans('asset_has_active_assignment');
        }

        return response()->json($response);
    }

    /**
     * show the asset of the asset assign form
     */
    public function assignUserForm(Asset $asset, User $user): JsonResponse
    {
        canPerform('Assign Asset');
        $html = view('asset::asset.assign-user', compact('user'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Assign Empty asset to selected User
     */
    public function assignUser(Request $request, User $user): JsonResponse
    {
        canPerform('Assign Asset');
        $request->validate(
            [
                'user_id' => ['required', 'exists:users,id'],
                'asset_id' => ['required', 'exists:assets,id'],
            ],
            [
                'asset_id.*' => 'Please select a valid asset',
                'user_id.*' => 'Please select a valid user'
            ]
        );
        $response = getErrorResponse();

        try {
            $assetAssignment = new AssetAssignment();
            $assetAssignment->asset_id = $request->asset_id;
            $assetAssignment->user_id = $request->user_id;
            $assetAssignment->issue_date = $request->start_date ?: now()->toDateString();
            $assetAssignment->save();
            $response = getSuccessResponse(createFlashMessage('User', 'assigned'));
            if ($user->id) {
                $response['redirect'] = route('backend.users.show', $user);
            }else{

                $response['redirect'] = route('backend.asset.index');

            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show form for unassign asset
     */
    public function unassignAssetForm(Asset $asset)
    {
        $html = view("asset::asset.un-assign-asset", compact('asset'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Unassign asset from user and make it available
     */
    public function unassignAsset(Asset $asset, Request $request)
    {
        canPerform('Edit Asset');
        $request->validate([
            'comment' => 'required|string'
        ]);

        $response = getErrorResponse();
        if ($asset->activeAssignment) {
            $assetAssignment = $asset->assignments()->whereNull('return_date')->first();
            $assetAssignment->return_date = now()->toDateString();
            $assetAssignment->comment = $request->comment;
            $assetAssignment->save();

            $response = getSuccessResponse(__trans('asset_unassigned_successfully'));
        } else {
            $response['message'] = __trans('there_is_no_active_assignment_for_this_asset');
        }

        return response()->json($response);
    }
}
