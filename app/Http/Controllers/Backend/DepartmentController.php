<?php
namespace App\Http\Controllers\Backend;

use App\Exports\DepartmentExport;
use App\Http\Controllers\Controller;
use App\Imports\DepartmentImport;
use App\Models\Department;
use App\Models\DepartmentAllowance;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{

    public function __construct()
    {
        view()->share('activeLink', 'branches');
    }
    /**
     * Display a listing of the department.
     */
    public function index(Request $request)
    {
        canPerform('Manage Department');
        if ($request->ajax()) {
            $data = Department::with('manager')
                ->withCount('users');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('manager', function ($row) {
                    if (! $row->manager_id) {
                        return '-';
                    }

                    $ids      = explode(',', $row->manager_id);
                    $managers = \App\Models\User::whereIn('id', $ids)->pluck('name')->toArray();
                    return implode(', ', $managers);
                })

                ->addColumn('action', function ($row) {
                    $btn     = '';
                    $actions = [];
                    if (hasPermission('Edit Department')) {
                        $actions[] = [route('backend.departments.edit', $row), 'Edit', 'fa fa-edit', 'edit-button'];
                    }
                    if (hasPermission('Assign Manager Department')) {
                        $actions[] = [route('backend.departments.user.add.form', $row), 'Assign Manager', 'fa fa-plus', 'edit-button'];
                    }
                    if (hasPermission('Manager Department')) {
                        $actions[] = [route('backend.departments.allowanceslist', $row), 'Allowances', 'fa fa-plus'];
                    }
                    if (hasPermission('Delete Department')) {
                        $actions[] = [route('backend.departments.destroy', $row), 'Delete', 'fa fa-trash', 'action-button', 'datatable'];
                    }

                    return createActionDropdownList($actions);
                })
                ->rawColumns(['action', 'role_id'])
                ->make(true);
        }

        return view('backend.department.index');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        canPerform('Create Department');
        $data = $request->validate([
            'name'              => 'required|unique:departments,name',
            'short_name'        => 'nullable',
            'start_number'      => 'nullable',
            'code'              => 'required|unique:departments,code',
            'address'           => 'required',
            'login_radius'      => 'required',
            'cancel_off_credit' => 'nullable',
            'cancel_off_amount' => 'nullable|numeric|min:0',
            'over_time'       => 'nullable|boolean',
            'logo'              => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'small_logo'        => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'sign'              => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'header'            => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'footer'            => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);
        $data['slug']   = $request->name;
        $data['budget'] = $request->budget;
        $response       = getErrorResponse();
        try {
            $address = $request->address;

            $apiKey      = env('GOOGLE_MAPS_API_KEY'); //config('app.google_maps_api_key');
            $client      = new Client();
            $getResponse = $client->get("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey");
            $getResult   = json_decode($getResponse->getBody());

            if ($getResult->status === 'OK') {
                $lat               = $getResult->results[0]->geometry->location->lat;
                $lng               = $getResult->results[0]->geometry->location->lng;
                $data['latitude']  = $lat;
                $data['longitude'] = $lng;
            } else {
                // list($lat, $lng) = explode(',', $address);
                $parts = explode(',', $address);
                if (count($parts) === 2) {
                    list($lat, $lng) = $parts;
                } else {
                    $lat = null;
                    $lng = null;
                }

                // Set the data array
                $data['latitude']  = $lat;
                $data['longitude'] = $lng;
            }
            // else {
            //     $response = getErrorResponse($message = "Please enter google verified address", $error = null);
            //     return response()->json($response);
            // }
            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('uploads/logos', 'public');
            }

            if ($request->hasFile('small_logo')) {
                $data['small_logo'] = $request->file('small_logo')->store('uploads/small_logos', 'public');
            }

            if ($request->hasFile('sign')) {
                $data['sign'] = $request->file('sign')->store('uploads/signs', 'public');
            }

            if ($request->hasFile('header')) {
                $data['header'] = $request->file('header')->store('uploads/headers', 'public');
            }
            if ($request->hasFile('footer')) {
                $data['footer'] = $request->file('footer')->store('uploads/footers', 'public');
            }
            Department::create($data);
            $response = getSuccessResponse(createFlashMessage('Department', 'Created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the department resource.
     */
    public function edit(Department $department)
    {
        canPerform('Edit Department');

        return response()->json([
            'success' => true,
            'html'    => view('backend.department.edit', compact('department'))->render(),
        ]);
    }

    /**
     * Update the department resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        canPerform('Edit Department');
        $data = $request->validate([
            'name'              => 'required|unique:departments,name,' . $department->id,
            'short_name'        => 'nullable',
            'start_number'      => 'nullable',
            'code'              => 'required|unique:departments,code,' . $department->id,
            'address'           => 'required',
            'login_radius'      => 'required',
            'cancel_off_credit' => 'nullable',
            'cancel_off_amount' => 'nullable|numeric|min:0',
            'over_time'       => 'nullable|boolean',
            'logo'              => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'small_logo'        => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'sign'              => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'header'            => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'footer'            => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data['slug']   = $request->name;
        $data['budget'] = $request->budget;
        $response       = getErrorResponse();
        try {
            $address = $request->address;

            $apiKey      = env('GOOGLE_MAPS_API_KEY');
            $client      = new Client();
            $getResponse = $client->get("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey");
            $getResult   = json_decode($getResponse->getBody());

            if ($getResult->status === 'OK') {
                $lat               = $getResult->results[0]->geometry->location->lat;
                $lng               = $getResult->results[0]->geometry->location->lng;
                $data['latitude']  = $lat;
                $data['longitude'] = $lng;
            } else {
                // list($lat, $lng) = explode(',', $address);
                $parts = explode(',', $address);
                if (count($parts) === 2) {
                    list($lat, $lng) = $parts;
                } else {
                    $lat = null;
                    $lng = null;
                }
                // Set the data array
                $data['latitude']  = $lat;
                $data['longitude'] = $lng;
            }
            // else {
            //     $response = getErrorResponse($message = "Please enter google verified address", $error = null);
            //     return response()->json($response);
            // }
            if ($request->hasFile('logo')) {
                if ($department->logo) {
                    Storage::disk('public')->delete($department->logo);
                }
                $data['logo'] = $request->file('logo')->store('uploads/logos', 'public');
            }

            // Handle small logo upload
            if ($request->hasFile('small_logo')) {
                if ($department->small_logo) {
                    Storage::disk('public')->delete($department->small_logo);
                }
                $data['small_logo'] = $request->file('small_logo')->store('uploads/small_logos', 'public');
            }

            // Handle sign upload
            if ($request->hasFile('sign')) {
                if ($department->sign) {
                    Storage::disk('public')->delete($department->sign);
                }
                $data['sign'] = $request->file('sign')->store('uploads/signs', 'public');
            }
            if ($request->hasFile('header')) {
                if ($department->header) {
                    Storage::disk('public')->delete($department->header);
                }
                $data['header'] = $request->file('header')->store('uploads/headers', 'public');
            }
            if ($request->hasFile('footer')) {
                if ($department->footer) {
                    Storage::disk('public')->delete($department->footer);
                }
                $data['footer'] = $request->file('footer')->store('uploads/footers', 'public');
            }
            $department->update($data);
            $response = getSuccessResponse(createFlashMessage('Department', 'Updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for adding User to the department resource.
     */
    public function addUserForm(Department $department)
    {
        canPerform('Assign Manager Department');
        $users = $department->users()
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'success' => true,
            'html'    => view('backend.department.add-user-form', compact('department', 'users'))->render(),
        ]);
    }

    /**
     * Add Users to particular department
     */
    public function addUser(Department $department, Request $request)
    {
        canPerform('Assign Manager Department');
        $response = getErrorResponse();
        try {
            // $department->manager_id = $request->users;
            if (! empty($request->users)) {
                $department->manager_id = implode(',', $request->users);
                $reportToIds            = array_map('strval', $request->users); // ["8","34"]

                $users = \App\Models\User::where('department_id', $department->id)->get();

                foreach ($users as $user) {
                    $userWorkDetails = $user->workDetail ?? new \App\Models\UserWorkDetail(['user_id' => $user->id]);

                    // store as JSON array
                    $userWorkDetails->report_to_ids = $reportToIds;
                    $userWorkDetails->save();
                }
            } else {
                $department->manager_id = null;
            }
            $department->save();
            $response = getSuccessResponse(__trans('user_successfully_added_to_department'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the department resource from storage.
     */
    public function destroy(Department $department)
    {
        canPerform('Delete Department');
        $response = getErrorResponse();
        try {
            $department->delete();
            $response = getSuccessResponse(createFlashMessage('Department', 'Deleted'));
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "Cannot delete this branch because it is used by other records.";
                $response['error']   = "Cannot delete this designation because it is referenced by other records.";
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

        // return Excel::download(new DepartmentExport, 'department_list_' . time() . '.xlsx');
        return Excel::download(new DepartmentExport, 'branch_list_' . time() . '.xlsx');
    }

    /**
     * Import Department List from excel to database
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import department'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new DepartmentImport();
            $import->import($request->file);
            $response = getSuccessResponse(createFlashMessage('File', 'imported'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function createallowances(Request $request, Department $department)
    {
        $html = view('backend.department.create-allowances', compact('department'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function allowanceslist(Request $request, Department $department)
    {
        if ($request->ajax()) {
            $data = $department->allowances()->get(); // department_allowances relation
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('type', fn($row) => ucfirst($row->type))
                ->addColumn('allowance_type', fn($row) => ucfirst($row->allowance_type))
                ->addColumn('amount', fn($row) => number_format($row->amount, 2))
                ->addColumn('action', function ($row) use ($department) {
                    $actions = [
                        [route('backend.departments.editallowances', [$department->id, $row->id]), 'Edit', 'fa fa-edit', 'edit-button'],
                        [route('backend.departments.deleteallowances', [$department->id, $row->id]), 'Delete', 'fa fa-trash', 'action-button', 'datatable'],
                    ];
                    return createActionDropdownList($actions);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.department.allowances-list', compact('department'));
    }

    public function storeallowances(Request $request, Department $department)
    {
        // POST - Store
        $request->validate([
            'allowance_name' => 'required|string|max:255',
            'type'           => 'required|in:monthly,yearly,one_time',
            'allowance_type' => 'required|in:fixed,percentage',
            'amount'         => 'required|numeric|min:0',
        ]);
        $response = getErrorResponse();
        try {
            DepartmentAllowance::create([
                'department_id'  => $department->id,
                'allowance_name' => $request->allowance_name,
                'type'           => $request->type,
                'allowance_type' => $request->allowance_type,
                'amount'         => $request->amount,
            ]);

            $response = getSuccessResponse(createFlashMessage('Department Allowance', 'Created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function editallowances(Department $department, DepartmentAllowance $allowance)
    {
        return response()->json([
            'success' => true,
            'html'    => view('backend.department.edit-allowances', compact('department', 'allowance'))->render(),
        ]);
    }
    public function updateallowances(Request $request, Department $department, DepartmentAllowance $allowance)
    {
        $request->validate([
            'allowance_name' => 'required|string|max:255',
            'type'           => 'required|in:monthly,yearly,one_time',
            'allowance_type' => 'required|in:fixed,percentage',
            'amount'         => 'required|numeric|min:0',
        ]);

        $allowance->update([
            'allowance_name' => $request->allowance_name,
            'type'           => $request->type,
            'allowance_type' => $request->allowance_type,
            'amount'         => $request->amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Allowance updated successfully.',
        ]);
    }
    public function deleteallowances(Department $department, DepartmentAllowance $allowance)
    {
        canPerform('Delete Department');
        $response = getErrorResponse();
        try {
            $allowance->delete();
            $response = getSuccessResponse(createFlashMessage('Allowance', 'Deleted'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
