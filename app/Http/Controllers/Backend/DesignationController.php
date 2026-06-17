<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DesignationExport;
use App\Http\Controllers\Controller;
use App\Imports\DesignationImport;
use App\Models\Department;
use App\Models\Designation;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DesignationController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'designations');
    }

    /**
     * Display a listing of the designation.
     */
    public function index(Request $request)
    {
        canPerform('Manage Designation');

        if ($request->ajax()) {
            $search = $request->input('search.value');
            // dd( $search);
            $query = Designation::with('department'); // keep relationship loaded

            if (!empty($search)) {
                // dd($search);
                $query->where(function ($q) use ($search) {
                    $q->where('designations.id', 'like', "%{$search}%")
                        ->orWhere('designations.name', 'like', "%{$search}%")
                        ->orWhere('designations.code', 'like', "%{$search}%") // added code field
                        ->orWhere('designations.grade', 'like', "%{$search}%") // added grade field
                        ->orWhereHas('department', function ($d) use ($search) {
                            $d->whereRaw('LOWER(name) like ?', ['%' . strtolower($search) . '%']);
                        });
                });
            }
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Designation')) {
                        $btn = createActionButton(route('backend.designations.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Designation')) {
                        $btn .= createActionButton(route('backend.designations.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->addColumn('department', function ($row) {
                    return $row->department->name ?? 'All';
                })
                ->addColumn('grade', function ($row) {
                    return $row->grade ?? '-';
                })
                ->rawColumns(['department', 'grade', 'action', 'role_id'])
                ->make(true);
        }

        return view('backend.designations.index');
    }


    /**
     * Show the form for creating a new designation.
     */
    public function create()
    {
        canPerform('Create Designation');
        $departments  = Department::pluck('name', 'id');
        $html = view('backend.designations.create', compact('departments'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created designation in storage.
     */
    public function store(Request $request)
    {
        canPerform('Create Designation');
        $data = $request->validate([
            'name' => [
                'required',
                Rule::unique('designations', 'name')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                })
            ],
            // 'department_id' => [
            //     'required',
            //     'exists:departments,id'
            // ],
            'department_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !DB::table('departments')->where('id', $value)->exists()) {
                        $fail('The selected department is invalid.');
                    }
                }
            ],
            'code' => 'required|unique:designations,code',
            'grade' => 'nullable|string|max:255'
        ]);


        $response = getErrorResponse();
        try {
            Designation::create($data);
            $response = getSuccessResponse(createFlashMessage('Designation', 'Created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified designation.
     */
    public function edit(Designation $designation)
    {

        canPerform('Edit Designation');
        $html = view('backend.designations.edit', compact('designation'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified designation in storage.
     */
    public function update(Request $request, Designation $designation)
    {


        canPerform('Edit Designation');
        $data = $request->validate([
            'name' => [
                'required',
                Rule::unique('designations', 'name')->ignore($designation->id)->where(function ($query) use ($designation) {
                    return $query->where('department_id', $designation->department_id);
                }),
            ],
            'code' => 'required|unique:designations,code,' . $designation->id,
            'grade' => 'nullable|string|max:255'
        ]);
        $departmentId = $request->get('department_id');
        if (isset($departmentId) && $departmentId != null) {
            $data['department_id'] = (int)$departmentId;
        }

        // Attempt to update the designation
        $response = getErrorResponse();
        // return $data;
        try {
            $designation->update($data);
            $response = getSuccessResponse(createFlashMessage('Designation', 'Updated'));
        } catch (Exception $e) {
            $response = getFailureResponse('The Designation is already assigned with the department.');
            $response['error'] = "The designation is already assigned and cannot be modified.";
        }


        return response()->json($response);
    }


    /**
     * Remove the specified designation from storage.
     */
    public function destroy(Designation $designation)
    {
        canPerform('Delete Designation');
        $response = getErrorResponse();
        try {
            $designation->delete();
            $response = getSuccessResponse(createFlashMessage('Designation', 'Deleted'));
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "Cannot delete this designation because it is used by other records.";
                $response['error'] = "Cannot delete this designation because it is referenced by other records.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }

    /**
     * Export Department List in excel format
     */
    public function export(Request $request)
    {
        canPerform('Export Department');

        return Excel::download(new DesignationExport, 'designation_list_' . time() . '.xlsx');
    }

    /**
     * Import Department List from excel to database
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);
        $response = getErrorResponse();

        try {
            $import = new DesignationImport();
            $import->import($request->file);
            $response = getSuccessResponse(createFlashMessage('File', 'imported'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
