<?php
namespace Modules\Api\Http\Controllers\ServiceRequest;

use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Api\Transformers\DocumentTypeResource;
use Modules\Api\Transformers\ServiceRequestResource;
use Modules\Document\Entities\DocumentRequest;
use Modules\Document\Entities\DocumentType;
use Modules\NotificationManager\Emails\DocumentRequestMail;
use Modules\NotificationManager\Entities\AlertRecipient;
use Modules\NotificationManager\Entities\EmailAlertLog;

class ServiceRequestController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $requests = ServiceRequestResource::collection(DocumentRequest::with('type')->my()->orderBy('id', 'DESC')->paginate(10));
        return response()->success(__trans('service_request_listed_successfully'), $requests);
    }

    /**
     * Create a new Service request in the storage from logged in user
     * @bodyParam reason string required The reason for the request for Document. Example: Want to Get Salary Slip From HR
     * @bodyParam document_type_id int required Id of the available document type. Example: 1
     *
     * @response status=422 scenario="Validation Error"{
     *     "success": false,
     *     "message": "Validation Failed",
     *     "error": {
     *         "document_type_id": [
     *             "The Document type id must be fill out."
     *         ],
     *         "reason": [
     *             "Reason is required why you request for document"
     *         ]
     *     }
     * }
     *
     * @response status=200 scenario="Document Request submitted sucessfully"
     * {
     *     "success": true,
     *     "message": "Document Request has been created successfully",
     * }
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
            $data['user_id'] = auth()->id();
            $service         = DocumentRequest::create($data);
            $user            = User::find(auth()->id());
            $admin           = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
            $userData        = [
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
            /* Send Email Notifications which set by admin */
            $this->emailNotification($userData);

            //$response = $this->fcmService->sendFcmMessage($user->ftoken, 'Service Request', $userData['message']);
            return response()->success(createFlashMessage('Document request', 'submitted'));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->error(config('project.error_message'));
    }

    /**
     * Get all Document types.
     * @param int $id
     * @return Response
     */
    public function getDocumentTypes()
    {
        // Fetch only visible document types
        $types = DocumentTypeResource::collection(
            DocumentType::where('user_visible', 1)->get()
        );

        return response()->success(__trans('document_types_fetched_successfully'), $types);
    }

    public function emailNotification($userData)
    {
        $alertRecipients = AlertRecipient::with('user')->where('alert_status', 1)->get();

        foreach ($alertRecipients as $alertRecipient) {
            $userEmail = $alertRecipient->user->email;
            //Mail::to($userEmail)->send(new DocumentRequestMail($userData));
            try {
                Mail::to($userEmail)->send(new DocumentRequestMail($userData));
                EmailAlertLog::create([
                    'email'      => $userEmail,
                    'status'     => 'success',
                    'alert_type' => 'Document Request',
                    'message'    => 'Email sent successfully.',
                ]);
            } catch (Exception $e) {
                EmailAlertLog::create([
                    'email'   => $userEmail,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
