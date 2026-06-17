<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserDependentRequest;
use App\Models\UserDependent;
use App\Services\UserDependentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\UserDependentDocument;


class DependentController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'dependents');
    }
    /**
     * Display a listing of the UserDependents.
     */
    public function index(Request $request)
    {
        canPerform('Manage Dependent');
        if ($request->ajax()) {
            $data = auth()->user()->dependents();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('first_name', function ($row) {
                    return $row->name;
                })
                ->editColumn('relation', function ($row) {
                    return $row->relation->name;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Dependent')) {
                        $btn = createActionButton(route('backend.employee.dependents.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                        $btn .= createActionButton(route('backend.employee.dependents.show', $row), 'Show', 'btn-primary edit-button', 'fa fa-eye');
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'role_id'])
                ->make(true);
        }
        return view('employee.dependent.index');
    }

    /**
     * Show the form for creating a new UserDependent.
     */
    public function create(): JsonResponse
    {
        canPerform('Create Dependent');
        $route = route('backend.employee.dependents.store');
        $html = view('common.modals.add-dependent', compact('route'))->render();

        return response()->json([
            'html' => $html,
            'success' => true,
        ]);
    }

    /**
     * Store a newly created UserDependent in storage.
     */
    public function store(UserDependentRequest $request, UserDependentService $userDependentService)
    {
        canPerform('Create Dependent');
        $response = getErrorResponse();
        try {
            $data = $request->except(['_token']);
            $data['user_id'] = auth()->id();
            $userDependent = $userDependentService->add($data);
            if ($request->hasfile('document')) {
                foreach ($request->file('document') as $index => $dependentdocument) {

                    $documentName = $request->input('document_name')[$index] ?? 'Untitled';

                    $location = "uploads/user_dependent_document/".auth()->id();
                    if (!file_exists(public_path($location))) {
                        mkdir(public_path($location), 0755, true);
                    }
                    $fileName = date('mdYHis') . uniqid() . time() . '_' . $dependentdocument->getClientOriginalName();
                    $ret = $dependentdocument->move($location, $fileName);

                   $depended_document= UserDependentDocument::create([
                        'document_name' => $documentName,
                        'document' => $fileName,
                        'user_dependent_id' => $userDependent->id,
                    ]);

                }
            }
            $response = getSuccessResponse(createFlashMessage('User Dependent', 'created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified UserDependent.
     */
    public function edit(int $id)
    {
        canPerform('Edit Dependent');
        $response = getErrorResponse();
        try {
            $userDependent = UserDependent::where(['user_id' => auth()->id()])->findOrFail($id);
            $route = route('backend.employee.dependents.update', $userDependent);
            $html = view('common.modals.update-dependent', compact('userDependent', 'route'))->render();
            $response = [
                'html' => $html,
                'success' => true,
            ];
        } catch (Exception $e) {
        }
        return response()->json($response);
    }

    /**
     * Update the specified UserDependent.
     */
    public function update(int $id, UserDependentRequest $request, UserDependentService $userDependentService)
    {
        canPerform('Edit Dependent');
        $response = getErrorResponse();
        try {
            $userDependent = UserDependent::where(['user_id' => auth()->id()])->findOrFail($id);
            $data = $request->except(['_token']);
            $data['user_id'] = auth()->id();
            $userDependent = $userDependentService->update($userDependent, $data);
            if ($request->hasfile('document')) {
                foreach ($request->file('document') as $index => $dependentdocument) {

                    $documentName = $request->input('document_name')[$index] ?? 'Untitled';

                    $location = "uploads/user_dependent_document/$userDependent->user_id";
                    if (!file_exists(public_path($location))) {
                        mkdir(public_path($location), 0755, true);
                    }
                    $fileName = date('mdYHis') . uniqid() . time() . '_' . $dependentdocument->getClientOriginalName();
                    $ret = $dependentdocument->move($location, $fileName);

                   $depended_document= UserDependentDocument::create([
                        'document_name' => $documentName,
                        'document' => $fileName,
                        'user_dependent_id' => $userDependent->id,
                    ]);


                }
            }

            $response = getSuccessResponse(createFlashMessage('User Dependent', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function show(int $id)
    {
        canPerform('Edit Dependent');
        $response = getErrorResponse();
        try {
            $userDependent = UserDependent::where(['user_id' => auth()->id()])->findOrFail($id);
            $html = view('common.modals.show-dependent', compact('userDependent'))->render();
            $response = [
                'html' => $html,
                'success' => true,
            ];
        } catch (Exception $e) {
        }
        return response()->json($response);
    }
}
