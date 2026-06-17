<?php
namespace Modules\Api\Http\Controllers\ApparelRequest;

use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Apparel\Entities\Apparel;
use Modules\Apparel\Entities\ApparelRequest;

class ApparelRequestController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    /**
     * LIST – Employee uniform requests
     */
    public function index()
    {
        $requests = ApparelRequest::with('apparel')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Uniform request list',
            'data'    => $requests,
        ], 200);
    }

    /**
     * CREATE
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apparel_id'        => 'required|exists:apparels,id',
            'number_of_apparel' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $apparel = Apparel::findOrFail($request->apparel_id);

        $approvedQty = ApparelRequest::where('apparel_id', $apparel->id)
            ->where('status', 1)
            ->sum('number_of_apparel');

        $remaining = $apparel->number_of_given - $approvedQty;

        if ($request->number_of_apparel > $remaining) {
            return response()->json([
                'success'         => false,
                'message'         => 'Requested quantity exceeds remaining uniform limit',
                'remaining_limit' => $remaining,
            ], 422);
        }

        $uniformRequest = ApparelRequest::create([
            'user_id'           => auth()->id(),
            'apparel_id'        => $request->apparel_id,
            'number_of_apparel' => $request->number_of_apparel,
            'status'            => 0, // pending
        ]);
        $user = User::withoutGlobalScopes()->find(auth()->id());

        $userData = [
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'message' => 'Generated a Uniform Request',
            'route'   => route('backend.apparel-request'),
            // Add any other user data you want to pass...
        ];
        // $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();
        // $admin = User::withoutGlobalScopes()
        //     ->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
        //     ->first();
        // if ($admin) {
        //     $admin->notify(new GenerateNotification($userData, $admin->id));
        // }
         $admins = User::withoutGlobalScopes()
                        ->whereHas('roles', function ($q) {
                            $q->whereIn('name', [
                                User::ROLE_ADMIN,
                                User::ROLE_SUPER_ADMIN,
                            ]);
                        })
                        ->get();

        foreach ($admins as $admin) {
            $admin->notify(new GenerateNotification($userData, $admin->id));
        }
        if (auth()->user()->ftoken != null) {

            $get = $this->fcmService->sendFcmMessage(auth()->user()->ftoken, 'Information', "Uniform request submitted successfully", 21);
        }

        return response()->json([
            'success' => true,
            'message' => 'Uniform request submitted successfully',
            'data'    => $uniformRequest,
        ], 201);
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $requestData = ApparelRequest::with('apparel')
            ->where('user_id', auth()->id())
            ->find($id);
        if (empty($requestData)) {
            return response()->json([
                'success' => true,
                'message' => 'Uniform request not found',
                'data'    => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Uniform request details',
            'data'    => $requestData,
        ], 200);
    }

    /**
     * UPDATE (pending only)
     */
    public function update(Request $request, $id)
    {
        $uniformRequest = ApparelRequest::where('user_id', auth()->id())
            ->find($id);

        if (empty($uniformRequest)) {
            return response()->json([
                'success' => true,
                'message' => 'Uniform request not found',
                'data'    => null,
            ], 200);
        }

        if ($uniformRequest->status != 0) {
            return response()->json([
                'success' => false,
                'message' => 'Approved/Rejected request cannot be updated',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'number_of_apparel' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $uniformRequest->update([
            'number_of_apparel' => $request->number_of_apparel,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Uniform request updated successfully',
            'data'    => $uniformRequest,
        ], 200);
    }

    /**
     * DELETE (pending only)
     */
    public function destroy($id)
    {
        $uniformRequest = ApparelRequest::where('user_id', auth()->id())
            ->find($id);
        if (empty($uniformRequest)) {
            return response()->json([
                'success' => true,
                'message' => 'Uniform request not found',
                'data'    => null,
            ], 200);
        }

        if ($uniformRequest->status != 0) {
            return response()->json([
                'success' => false,
                'message' => 'Approved/Rejected request cannot be deleted',
            ], 403);
        }

        $uniformRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Uniform request deleted successfully',
        ], 200);
    }
}
