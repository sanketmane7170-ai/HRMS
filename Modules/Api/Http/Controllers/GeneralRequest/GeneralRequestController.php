<?php

namespace Modules\Api\Http\Controllers\GeneralRequest;

use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\GeneralRequest\Entities\GeneralRequest;
use Illuminate\Support\Facades\Validator;

class GeneralRequestController extends Controller
{
    /**
     * LIST – Employee Requests
     */
    public function index()
    {
        $requests = GeneralRequest::with('type')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'General request list',
            'data'    => $requests,
        ], 200);
    }

    /**
     * CREATE
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:general_request_types,id',
            'date'    => 'required|date',
            'note'    => 'nullable|string',
            'amount'  => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $requestData = GeneralRequest::create([
            'user_id' => auth()->id(),
            'type_id' => $request->type_id,
            'date'    => $request->date,
            'note'    => $request->note,
            'amount'  => $request->amount,
            'status'  => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'General request created successfully',
            'data'    => $requestData,
        ], 201);
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $requestData = GeneralRequest::with('type')
            ->where('user_id', auth()->id())
            ->find($id);
        if (empty($requestData)) {
            return response()->json([
                'success' => true,
                'message' => 'General request not found',
                'data'    => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'General request details',
            'data'    => $requestData,
        ], 200);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $generalRequest = GeneralRequest::where('user_id', auth()->id())
            ->find($id);
        if (empty($generalRequest)) {
            return response()->json([
                'success' => true,
                'message' => 'General request not found',
                'data'    => null,
            ], 200);
        }


        if ($generalRequest->status !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Approved/Rejected request cannot be updated',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:general_request_types,id',
            'date'    => 'required|date',
            'note'    => 'nullable|string',
            'amount'  => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $generalRequest->update($request->only([
            'type_id',
            'date',
            'note',
            'amount'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'General request updated successfully',
            'data'    => $generalRequest,
        ], 200);
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $generalRequest = GeneralRequest::where('user_id', auth()->id())
            ->find($id);
        if (empty($generalRequest)) {
            return response()->json([
                'success' => true,
                'message' => 'General request not found',
                'data'    => null,
            ], 200);
        }


        if ($generalRequest->status !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Approved/Rejected request cannot be deleted',
            ], 403);
        }

        $generalRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'General request deleted successfully',
        ], 200);
    }
}
