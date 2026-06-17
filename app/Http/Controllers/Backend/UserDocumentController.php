<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserDocumentStoreRequest;
use App\Models\User;
use App\Models\UserDocument;
use App\Services\UserDocumentService;
use Exception;
use Illuminate\Http\JsonResponse;


class UserDocumentController extends Controller
{
    /**
     * return user document form html
     */
    public function create(User $user): JsonResponse
    {
        canPerform('Create User Document');
        $route = route('backend.user-document.store', $user);
        $action = "html=#document-details";
        $html = view('common.modals.document.create', compact('route', 'action'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(UserDocumentStoreRequest $request, User $user, UserDocumentService $userDocumentService)
    {
        canPerform('Create User Document');
        $response = getErrorResponse();
        try {
            $userDocumentService->addDocument($request, $user);
            $response = getSuccessResponse(createFlashMessage('User Document', 'added'));
            $response['html'] = view('backend.users.partials.document-details', compact('user'))->render();
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserDocument $userDocument, UserDocumentService $userDocumentService): JsonResponse
    {
        canPerform('Delete User Document');
        $userDocumentService->destroy($userDocument);
        $user = $userDocument->user;
        $response = getSuccessResponse(createFlashMessage('User Document', 'Deleted'));
        $response['html'] = view('backend.users.partials.document-details', compact('user'))->render();

        return response()->json($response);
    }

    public function download(UserDocument $userDocument)
    {
        abort_if(auth()->user()->hasRole('employee'), 404);

        return response()->download(public_path($userDocument->path), $userDocument->original_name);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(UserDocument $userdocument)
    {
        canPerform('Edit User Document');
        $route = route('backend.user-document.update', $userdocument->id);
        $action = "html=#document-details";
        $html = view('common.modals.document.edit', compact('route', 'action','userdocument'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * update the specified resource from storage.
     */
    public function update(UserDocumentStoreRequest $request, UserDocument $userdocument, UserDocumentService $userDocumentService)
    {
        // canPerform('Update User Document');
        $userDocumentService->updateDocument($request,$userdocument);
        // $user = $userDocument->user;
        $response = getSuccessResponse(createFlashMessage('User Document', 'Deleted'));
        //$response['html'] = view('backend.users.partials.document-details', compact('user'))->render();

        return response()->json($response);
    }

    public function changeStatus(Request $request, UserDocument $userdocument)
    {
        canPerform('Edit User Document');
        $request->validate(['status' => 'required|in:pending,verified,rejected']);
        $userdocument->update(['status' => $request->status]);
        $user = $userdocument->user;
        $response = getSuccessResponse(createFlashMessage('Document Status', 'updated'));
        $response['html'] = view('backend.users.partials.document-details', compact('user'))->render();
        return response()->json($response);
    }

    
}
