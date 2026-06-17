<?php

namespace App\Http\Controllers\Backend;

use App\Exports\FeatureExport;
use App\Http\Controllers\Controller;
use App\Imports\FeatureImport;
use App\Models\Feature;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class FeatureController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'features');
    }

    /**
     * Display a listing of the feature.
     */
    public function index(Request $request)
    {
       // canPerform('Manage Feature');
        if ($request->ajax()) {
            // $data = Feature::with('feature');
            $data = Feature::where('status', 1)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Feature')) {
                        $btn = createActionButton(route('backend.features.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Feature')) {
                        $btn .= createActionButton(route('backend.features.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }

        return view('backend.features.index');
    }

    /**
     * Show the form for creating a new feature.
     */
    public function create()
    {
       // canPerform('Create Feature');
        $features  = Feature::pluck('date','url','version','feature', 'id');
        $html = view('backend.features.create', compact('features'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created feature in storage.
     */
    public function store(Request $request)
    {
       // canPerform('Create Feature');
       $data = $request->validate([
        'date' => 'required',
        'version' => 'required',
        'feature' => 'required',
        'url' => 'required'
    ]);
       
        $response = getErrorResponse();
        try {
            Feature::create($data);
            $response = getSuccessResponse(createFlashMessage('Feature', 'Created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified feature.
     */
    public function edit(Feature $feature)
    {
        // dd($feature);
       // canPerform('Edit Feature');
        $html = view('backend.features.edit', compact('feature'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified feature in storage.
     */
    public function update(Request $request, Feature $feature)
    {


       // canPerform('Edit Feature');
       $data = $request->validate([
        'date' => 'required',
        'version' => 'required',
        'feature' => 'required',
        'url' => 'required',
       
    ]);

        // Attempt to update the feature
        $response = getErrorResponse();
        // return $data;
        try {
            $feature->update($data);
            $response = getSuccessResponse(createFlashMessage('Feature', 'Updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }


        return response()->json($response);
    }


    /**
     * Remove the specified feature from storage.
     */
    public function destroy(Feature $feature)
    {
       // canPerform('Delete Feature');
        $response = getErrorResponse();
        try {
            $feature->delete();
            $response = getSuccessResponse(createFlashMessage('Feature', 'Deleted'));
        } catch (Exception $e) {
            
                $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Export Feature List in excel format
     */
    // public function export(Request $request)
    // {
    //    // canPerform('Export Feature');

    //     return Excel::download(new FeatureExport, 'feature_list_' . time() . '.xlsx');
    // }

    /**
     * Import Feature List from excel to database
     */
    // public function import(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx'
    //     ]);
    //     $response = getErrorResponse();

    //     try {
    //         $import = new FeatureImport();
    //         $import->import($request->file);
    //         $response = getSuccessResponse(createFlashMessage('File', 'imported'));
    //     } catch (Exception $e) {
    //         $response['message'] = $e->getMessage();
    //     }

    //     return response()->json($response);
    // }
}
