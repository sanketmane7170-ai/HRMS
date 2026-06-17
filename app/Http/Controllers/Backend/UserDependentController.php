<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserDependentRequest;
use App\Models\User;
use App\Models\UserDependent;
use App\Services\UserDependentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\UserDependentDocument;


class UserDependentController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(User $user)
    {
        canPerform('Create Dependent');
        $route = route('backend.user-dependent.store', compact('user'));
        $action = "html=#dependent-details";
        $html = view('common.modals.add-dependent', compact('route', 'action'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(User $user, UserDependentRequest $request, UserDependentService $userDependentService)
    {
        canPerform('Create Dependent');
        $response = getErrorResponse();
        try {
            $data = $request->validated();
            $data['user_id'] = $user->id;
           $document= $userDependentService->add($data);
            if ($request->hasfile('document')) {
                foreach ($request->file('document') as $index => $dependentdocument) {

                    $documentName = $request->input('document_name')[$index] ?? 'Untitled';

                    $location = "uploads/user_dependent_document/$user->id";
                    if (!file_exists(public_path($location))) {
                        mkdir(public_path($location), 0755, true);
                    }
                    $fileName = date('mdYHis') . uniqid() . time() . '_' . $dependentdocument->getClientOriginalName();
                    $ret = $dependentdocument->move($location, $fileName);

                   $depended_document= UserDependentDocument::create([
                        'document_name' => $documentName,
                        'document' => $fileName,
                        'user_dependent_id' => $document->id,
                    ]);

                }
            }

            
            $response = getSuccessResponse(createFlashMessage('User Dependent', 'created'));
            $response['html'] = view('backend.users.partials.dependent-details', compact('user'))->render();
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserDependent $userDependent)
    {
        canPerform('Edit Dependent');
        $route = route('backend.user-dependent.update', [$userDependent]);
        $action = "html=#dependent-details";
        $html = view('common.modals.update-dependent', compact('route', 'userDependent', 'action'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserDependentRequest $request, UserDependent $userDependent, UserDependentService $userDependentService)
    {
        canPerform('Edit Dependent');
        $response = getErrorResponse();
        try {
            $data = $request->validated();
            $userDependentService->update($userDependent, $data);
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
            $user = $userDependent->user;
            $response['html'] = view('backend.users.partials.dependent-details', compact('user'))->render();
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserDependent $userDependent): JsonResponse
    {
        $userDependent->delete();
        $response = getSuccessResponse(createFlashMessage('User Dependent', 'Deleted'));
        $user = $userDependent->user;
        $response['html'] = view('backend.users.partials.dependent-details', compact('user'))->render();

        return response()->json($response);
    }

    public function dependent_document_delete($id, $user_id)
    {

        $document = UserDependentDocument::findOrFail($id);

        $filePath = public_path('uploads/user_dependent_document/' . $user_id . '/' . $document->document);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $document->delete();

        return response()->json(['success' => true]);
    }

    public function show(UserDependent $userDependent)
    {
        canPerform('Edit Dependent');
        $route = route('backend.user-dependent.update', [$userDependent]);
        $action = "html=#dependent-details";
        $html = view('common.modals.show-dependent', compact('route', 'userDependent', 'action'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function download(UserDependentDocument $userDependentDocument,$user_id)
    {
        $location="uploads/user_dependent_document/".$user_id."/".$userDependentDocument->document;
        return response()->download($location, $userDependentDocument->original_name);
    }
}
