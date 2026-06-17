<?php

namespace Modules\Document\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Document\Entities\DocumentType;
use Yajra\DataTables\Facades\DataTables;

class DocumentTypeController extends Controller
{

    public function __construct()
    {
        view()->share('activeLink', 'document-types');
    }

    /**
     * Display a listing of the document types.
     */
    public function index(Request $request)
    {
        canPerform('Manage Document Type');
        if ($request->ajax()) {
            $data = DocumentType::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Document Type')) {
                        $btn = createActionButton(route('backend.document-types.edit', $row), 'Edit', 'btn-warning', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Document Type')) {
                        $btn .= createActionButton(route('backend.document-types.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('document::type.index');
    }

    /**
     * Show the form for creating a new document types.
     */
    public function create()
    {
        canPerform('Create Document Type');
        $keywords = (new DocumentType())->getKeyWordList();
        return  view('document::type.create', compact('keywords'))->render();
    }

    /**
     * Store a newly created document types in storage.
     * @param Request $request
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:document_types,name',
            'template' => 'required',
            'user_visible' => 'nullable|boolean'

        ]);

        $response = getErrorResponse();
        try {
            $data['user_visible'] = $request->has('user_visible') ? 1 : 0;


            DocumentType::create($data);
            $response = getSuccessResponse(createFlashMessage('Document Type', 'created'));
            $response['redirect'] = route('backend.document-types.index');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified document types.
     * @param int $id
     */
    public function edit(DocumentType $documentType)
    {
        canPerform('Edit Document Type');
        $keywords = (new DocumentType())->getKeyWordList();

        return view('document::type.edit', compact('documentType', 'keywords'))->render();
    }

    /**
     * Update the specified document types in storage.
     */
    public function update(Request $request, DocumentType $documentType)
    {
        canPerform('Edit Document Type');

        $data = $request->validate([
            'name' => 'required|unique:document_types,name,' . $documentType->id,
            'template' => 'required',
            'user_visible' => 'nullable|boolean'

        ]);

        $response = getErrorResponse();
        try {
            $data['user_visible'] = $request->has('user_visible') ? 1 : 0;

            $documentType->update($data);
            $response = getSuccessResponse(createFlashMessage('Document Type', 'updated'));
            $response['redirect'] = route('backend.document-types.index');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified document types from storage.
     */
    public function destroy(DocumentType $documentType)
    {
        canPerform('Delete Document Type');
        $response = getErrorResponse();
        try {
            $documentType->delete();
            $response = getSuccessResponse(createFlashMessage('Document Type', 'Deleted'));
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "Cannot delete this document type because it is used by other records.";
                $response['error'] = "Cannot delete this document type it is used by other records.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }
}
