<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserDocumentStoreRequest;
use App\Models\UserDocument;
use App\Services\UserDocumentService;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class DocumentController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'documents');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = auth()->user()->documents();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('path', function ($row) {
                    $path = asset($row->path);
                    $html = '<a href="' . route('backend.employee.documents.show', $row) . '" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="' . $path . '" alt="' . $row->original_name . '" target="_blank"></a>';
                    return $html;
                })
                ->editColumn('type', function ($row) {
                    return $row->type->name;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn = createActionButton(route('backend.employee.documents.destroy', $row), 'download', 'btn-success', 'fa fa-download', 'target=_blank');

                    if (hasPermission('Delete User Document')) {
                        $btn = createActionButton(route('backend.employee.documents.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'path'])
                ->make(true);
        }
        return view('employee.document.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $route = route('backend.employee.documents.store', auth()->user());
        $html = view('common.modals.document.create', compact('route'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserDocumentStoreRequest $request, UserDocumentService $userDocumentService)
    {
        $response = getErrorResponse();
        try {
            $userDocumentService->addDocument($request, auth()->user());
            $response = getSuccessResponse(createFlashMessage('User Document', 'added'));
            $response['html'] = view('backend.users.partials.document-details', compact('user'))->render();
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): BinaryFileResponse
    {
        $userDocument = UserDocument::where(['user_id' => auth()->id()])->findOrFail($id);

        return response()->download(public_path($userDocument->path), $userDocument->original_name);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, UserDocumentService $userDocumentService)
    {
        canPerform('Delete User Document');
        $userDocument = auth()->user()->documents()->findOrFail($id);
        $userDocumentService->destroy($userDocument);
        $response = getSuccessResponse(createFlashMessage('User Document', 'Deleted'));

        return response()->json($response);
    }
}
