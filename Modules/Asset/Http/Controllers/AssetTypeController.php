<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Entities\AssetType;
use Yajra\DataTables\Facades\DataTables;

class AssetTypeController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'asset-types');
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        canPerform('Manage Asset Type');
        if ($request->ajax()) {
            $data = AssetType::query()->withCount('assets');
            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Asset Type')) {
                        $btn = createActionButton(route('backend.asset-types.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Asset Type')) {
                        $btn .= createActionButton(route('backend.asset-types.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('asset::type.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        canPerform('Create Asset Type');
        $html = view('asset::type.create')->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        canPerform('Create Asset Type');
        $request->validate([
            'name' => ['required', 'string', 'unique:asset_types,name']
        ]);
        $response = getErrorResponse();
        $assetType = new AssetType();
        $assetType->name = $request->name;
        if ($assetType->save()) {
            $response = getSuccessResponse(createFlashMessage('Asset Type', 'created'));
            // added for get autofilled data when we create asset type on add asset modal | Gagan 02-08-2023
            $response['results'] = [ 'id' => $assetType->id, 'name' => $assetType->name ]; 
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(AssetType $assetType)
    {
        canPerform('Edit Asset Type');
        $html = view('asset::type.edit', compact('assetType'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, AssetType $assetType)
    {
        canPerform('Create Asset Type');
        $request->validate([
            'name' => ['required', 'string', 'unique:asset_types,name,' . $assetType->id]
        ]);
        $response = getErrorResponse();
        $assetType->name = $request->name;
        if ($assetType->save()) {
            $response = getSuccessResponse(createFlashMessage('Asset Type', 'updated'));
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(AssetType $assetType)
    {
        canPerform('Delete Asset Type');
        $response = getErrorResponse();
        if (!$assetType->assets()->exists()) {
            $assetType->delete();
            $response = getSuccessResponse(createFlashMessage('Asset Type', 'deleted'));
        } else {
            $response['message'] = __trans('asset_type_has_linked_data');
        }

        return response()->json($response);
    }
}
