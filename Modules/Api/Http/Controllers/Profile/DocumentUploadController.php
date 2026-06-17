<?php

namespace Modules\Api\Http\Controllers\Profile;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Enums\Document;
use Modules\Api\Transformers\UploadDocumentTypeRes;
use Exception;
use App\Models\UserDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UserDocumentStoreRequest;
use App\Services\UserDocumentService;
use Illuminate\Validation\Rules\Enum;

class DocumentUploadController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $user_id = auth()->id();
        try {
            $list = UserDocument::where('user_id', $user_id)->get()->each(function($item){
                $item->makeHidden(['created_at', 'updated_at', 'user_id']);
                $item->path = asset($item->path);
            });
            return response()->success(__trans('user_document_list_fetched_successfully'), $list);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->error($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('api::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function store(Request $request, UserDocumentService $userDocumentService)
    {
        //$response = getErrorResponse();
        $validator = Validator::make($request->all(), [
            'file' => ['nullable', 'mimes:jpg,jpeg,png'],
            'type' => [
                'required', new Enum(Document::class)
            ],
            'expiry_date' => [
                'nullable',
                'date',
            ],
            'serial_number' => [
                'nullable',
                'string',
            ]
        ]);

        if ($validator->fails()) {
            return response()->error(__trans('validation_failed'), $validator->errors());
        }
        try {
            $userDocumentService->addDocument($request, auth()->user());
            return response()->success(__trans('user_document_added_successfully'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('api::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('api::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function getDocumentTypes()
    {
        try {
            $all_documents = UploadDocumentTypeRes::collection(Document::cases());
            return response()->success(__trans('document_types_fetched_successfully'), $all_documents);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->error($e->getMessage());
        }
    }
}
