<?php
namespace Modules\Document\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Document\Entities\DocumentRequest;
use Modules\Document\Entities\DocumentType;
use Modules\Document\Enums\DocumentRequestStatus;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class EmployeeDocumentRequestController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'document-requests');
        $this->fcmService = $fcmService;
    }

    /**
     * Display a listing of the document requests.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DocumentRequest::with(['type'])->my()->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($data) {
                    return $data->status->getHtml();
                })
                ->editColumn('created_at', function ($data) {
                    return formatDate($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    $btn = '';
                    if ($data->status == DocumentRequestStatus::Pending) {
                        $btn  = createActionButton(route('backend.employee.document-requests.edit', $data), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                        $btn .= createActionButton(route('backend.employee.document-requests.show', $data), 'View', 'btn-success', 'fa fa-eye');
                        $btn .= createActionButton(route('backend.employee.document-requests.destroy', $data), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    if ($data->status == DocumentRequestStatus::Completed) {
                        $btn = createActionButton(route('backend.employee.document-requests.download', $data), 'Download', 'btn-success', 'fa fa-download');
                    }
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('document::employee.request.index');
    }

    /**
     * Show the form for creating a new document requests.
     */
    public function create()
    {
        $types = DocumentType::where('user_visible', 1)->get(['id', 'name']);
        $user  = User::find(auth()->id());
        if ($user) {

            $setting       = Setting::whereIn('key', ['free_document_request', 'document_request_charge'])->get();
            $work          = $user->workDetail;
            $userFree      = $work->free_document_request ?? null;
            $userCharge    = $work->document_request_charge ?? null;
            $defaultFree   = Setting::where('key', 'free_document_request')->value('value') ?? 0;
            $defaultCharge = Setting::where('key', 'document_request_charge')->value('value') ?? 0;
            $freeLimit     = ($userFree > 0) ? $userFree : $defaultFree;
            $charge        = ($userCharge > 0) ? $userCharge : $defaultCharge;

            $generatedCount = DocumentRequest::where('user_id', $user->id)
                ->where('status', '!=', 'rejected') // adjust if your status is different
                ->count();

            $amount = ($generatedCount >= $freeLimit) ? $charge : 0;
        }
        $html = view('document::employee.request.create', compact('types', 'generatedCount', 'amount', 'freeLimit'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    /**
     * Store a newly created document requests in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'document_type_id'    => 'required|exists:document_types,id',
            'reason'              => 'required|string',
            'letter_addressed_to' => 'nullable|string',
        ]);

        $response = getErrorResponse();
        try {
            // $service = DocumentRequest::create($data);
            $user = User::find(auth()->id());
            if ($user) {
                //if ($user && $user->ftoken !== null) {
                $work       = $user->workDetail;
                $userFree   = $work->free_document_request ?? null;
                $userCharge = $work->document_request_charge ?? null;

                $defaultFree   = Setting::where('key', 'free_document_request')->value('value') ?? 0;
                $defaultCharge = Setting::where('key', 'document_request_charge')->value('value') ?? 0;

                $freeLimit = ($userFree > 0) ? $userFree : $defaultFree;
                $charge    = ($userCharge > 0) ? $userCharge : $defaultCharge;

                // Count for selected document type
                $generatedCount = DocumentRequest::where('user_id', $user->id)
                    ->where('document_type_id', $request->document_type_id)
                    ->where('status', '!=', 'rejected')
                    ->count();

                // Calculate charge amount
                $amount = ($generatedCount >= $freeLimit) ? $charge : 0;

                // ✅ Update amount column before generating PDF
                if ($amount > 0) {
                    $data['amount'] = $amount;
                }
                $service  = DocumentRequest::create($data);
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Raised a Document Request for reason "' . $service->reason . '"',
                    'route'   => route('backend.document-requests.show', $service->id),
                    // Add any other user data you want to pass...
                ];
                // $admin->notify(new GenerateNotification($userData, $admin->id));
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

                // Send document notification to users who can Manage Document Request
                $usersWithPermission = User::permission('Manage Document Request')->where('id', '!=', $user->id)->get();
                foreach ($usersWithPermission as $userManageDocument) {
                    $userManageDocument->notify(new GenerateNotification($userData, $userManageDocument->id));
                }

                if (! empty($user->ftoken)) {
                    $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Document Request', $userData['message'], 5);
                }
                // send notification manager
                $managers = User::permission('Document Request Manager Access')->where('id', '!=', $user->id)->get();
                foreach ($managers as $manager) {
                    if (! empty($manager->ftoken)) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Document Request was Generated', 'Document Request was Generated', 15);
                    }
                }
                //end
                $response = getSuccessResponse(createFlashMessage('Document Request', 'created'));
            } else {
                $response = getErrorResponse(__trans('user_is_not_available_currently'));
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the specified document request.
     */
    public function show($id)
    {
        $documentRequest = DocumentRequest::with(['user', 'type'])->my()->findOrFail($id);
        return view('document::employee.request.show', compact('documentRequest'));
    }

    /**
     * Show the form for editing the specified document types.
     */
    public function edit($id)
    {
        $documentRequest = DocumentRequest::my()->findOrFail($id);
        if ($documentRequest->status == DocumentRequestStatus::Pending) {
            $types    = DocumentType::where('user_visible', 1)->get(['id', 'name']);
            $html     = view('document::employee.request.edit', compact('documentRequest', 'types'))->render();
            $response = [
                'success' => true,
                'html'    => $html,
            ];
        } else {
            $response = [
                'success' => false,
                'message' => __trans('permission_denied_as_request_has_been_processed'),
            ];
        }

        return response()->json($response);
    }

    /**
     * Update the specified document types in storage.
     */
    public function update(Request $request, $id)
    {
        $documentRequest = DocumentRequest::my()->findOrFail($id);
        if ($documentRequest->status == DocumentRequestStatus::Pending) {
            $data = $request->validate([
                'document_type_id'    => 'required|exists:document_types,id',
                'reason'              => 'required|string',
                'letter_addressed_to' => 'nullable|string',
            ]);

            $response = getErrorResponse();
            try {
                $documentRequest->update($data);
                $response = getSuccessResponse(createFlashMessage('Document Request', 'updated'));
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }
        } else {
            $response = [
                'success' => false,
                'message' => __trans('permission_denied_as_request_has_been_processed'),
            ];
        }

        return response()->json($response);
    }

    /**
     * Remove the specified document types from storage.
     */
    public function destroy($id)
    {
        $documentRequest = DocumentRequest::my()->findOrFail($id);
        $response        = getErrorResponse();
        if ($documentRequest->status == DocumentRequestStatus::Pending) {
            try {
                $documentRequest->delete();
                $response = getSuccessResponse(createFlashMessage('Document Request', 'deleted'));
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }
        } else {
            $response = [
                'success' => false,
                'message' => __trans('permission_denied_as_request_has_been_processed'),
            ];
        }

        return response()->json($response);
    }

    /**
     * Download generate document for the request
     */
    public function download(DocumentRequest $documentRequest): BinaryFileResponse
    {
        if ($documentRequest->user_id != auth()->id()) {
            abort(404);
        }
        return response()->download(public_path($documentRequest->file_path), $documentRequest->getFileName());
    }
    public function getDocumentTypeCount(Request $request)
    {
        $request->validate([
            'document_type_id' => 'required',
            'user_id'          => 'required',
        ]);

        // Fetch user
        $user = User::find($request->user_id);

        // Get user-specific or default settings
        $work       = $user->workDetail;
        $userFree   = $work->free_document_request ?? null;
        $userCharge = $work->document_request_charge ?? null;

        $defaultFree   = Setting::where('key', 'free_document_request')->value('value') ?? 0;
        $defaultCharge = Setting::where('key', 'document_request_charge')->value('value') ?? 0;

        $freeLimit = ($userFree > 0) ? $userFree : $defaultFree;
        $charge    = ($userCharge > 0) ? $userCharge : $defaultCharge;

        // Count for selected document type
        $generatedCount = DocumentRequest::where('user_id', $user->id)
            ->where('document_type_id', $request->document_type_id)
            ->where('status', '!=', 'rejected')
            ->count();

        $pendingCount = DocumentRequest::where('user_id', $user->id)
            ->where('document_type_id', $request->document_type_id)
            ->where('status', 'pending')
            ->count();

        $complitedCount = DocumentRequest::where('user_id', $user->id)
            ->where('document_type_id', $request->document_type_id)
            ->where('status', 'completed')
            ->count();

        // Calculate charge amount
        $amount = ($generatedCount >= $freeLimit) ? $charge : 0;

        return response()->json([
            'count'          => $generatedCount,
            'complitedCount' => $complitedCount,
            'pendingCount'   => $pendingCount,
            'free_limit'     => $freeLimit,
            'charge'         => $charge,
            'amount'         => $amount,
        ]);
    }
}
