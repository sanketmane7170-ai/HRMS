<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Entities\AssetManufacturer;
use Yajra\DataTables\Facades\DataTables;

class AssetManufacturerController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'asset-manufacturers');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        canPerform('Manage Asset Manufacturer');
        if ($request->ajax()) {
            $data = AssetManufacturer::query();
            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Asset Manufacturer')) {
                        $btn = createActionButton(route('backend.asset-manufacturers.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Asset Manufacturer')) {
                        $btn .= createActionButton(route('backend.asset-manufacturers.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('asset::manufacturer.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        canPerform('Create Asset Manufacturer');
        $html = view('asset::manufacturer.create')->render();

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
        canPerform('Create Asset Manufacturer');
        $request->validate([
            'name' => ['required', 'string', 'unique:asset_manufacturers,name']
        ]);
        $response = getErrorResponse();
        $assetManufacturer = new AssetManufacturer();
        $assetManufacturer->name = $request->name;
        if ($assetManufacturer->save()) {
            $response = getSuccessResponse(createFlashMessage('Asset manufacturer', 'created'));
            // added for get autofilled data when we create manufacture on add asset modal | Gagan 02-08-2023
            $response['results'] = [ 'id' => $assetManufacturer->id, 'name' => $assetManufacturer->name ]; 
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(AssetManufacturer $assetManufacturer)
    {
        canPerform('Edit Asset Manufacturer');
        $html = view('asset::manufacturer.edit', compact('assetManufacturer'))->render();

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
    public function update(Request $request, AssetManufacturer $assetManufacturer)
    {
        canPerform('Edit Asset Manufacturer');
        $request->validate([
            'name' => ['required', 'string', 'unique:asset_manufacturers,name,' . $assetManufacturer->id]
        ]);
        $response = getErrorResponse();
        $assetManufacturer->name = $request->name;
        if ($assetManufacturer->save()) {
            $response = getSuccessResponse(createFlashMessage('Asset manufacturer', 'updated'));
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(AssetManufacturer $assetManufacturer)
    {
        canPerform('Delete Asset Manufacturer');
        $response = getErrorResponse();
        if (!$assetManufacturer->assets()->exists()) {
            $assetManufacturer->delete();
            $response = getSuccessResponse(createFlashMessage('Asset manufacturer', 'deleted'));
        } else {
            $response['message'] = __trans('asset_type_has_linked_data');
        }

        return response()->json($response);
    }
}
