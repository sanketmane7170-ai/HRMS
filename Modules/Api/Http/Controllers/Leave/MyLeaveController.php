<?php
namespace Modules\Api\Http\Controllers\Leave;

use App\Models\User;
use App\Notifications\Leave\GenerateNotification;
use App\Services\FirebaseService;
use App\Traits\File;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
// Trait added for fix Add leave issue on APP | Gagan 02-08-2023
use Illuminate\Support\Facades\Validator;
use Modules\Api\Transformers\Leave\ListResource;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Rules\HalfDayLeave;
use Modules\Leave\Rules\LeaveAllowed;
use Modules\NotificationManager\Emails\LeaveRequestMail;
use Modules\NotificationManager\Entities\AlertRecipient;
use Modules\NotificationManager\Entities\EmailAlertLog;

/**
 * @group 4. Leave
 * @authenticated
 *
 */
class MyLeaveController extends Controller
{
    // Use Traits Functions
    use File;

    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    /**
     * Return the listing of the all leave applied by logged in user
     *
     */
    public function index()
    {
        $leaves = ListResource::collection(Leave::with('type')->my()->orderBy('id', 'DESC')->paginate(10));

        return response()->success(__trans('leave_listed_successfully'), $leaves);
    }

    /**
     * Create a new Leave request in the storage from logged in user
     * @bodyParam start_date date required The leave start_date. Example: 2023-08-10
     * @bodyParam end_date date required The leave end_date. Example: 2023-08-10
     * @bodyParam reason string required The reason for the leave application. Example: Having an appointment wih doctor
     * @bodyParam leave_type_id int required Id of the available leave type. Example: 1
     * @bodyParam is_half_day int optional Send 1 when the leave is applied for half day only. Example: 0
     * @bodyParam document file Attachment document for the particular leave request as doctor medical
     *
     * @response status=422 scenario="Validation Error"{
     *     "success": false,
     *     "message": "Validation Failed",
     *     "error": {
     *         "start_date": [
     *             "The start date field must be a date after or equal to today."
     *         ],
     *         "is_half_day": [
     *             "When half day is selected then start & end date must be same"
     *         ]
     *     }
     * }
     *
     * @response status=200 scenario="Leave submitted sucessfully"
     * {
     *     "success": true,
     *     "message": "Leave has been submitted successfully",
     * }
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'reason'        => ['required', 'string'],
            'start_date'    => 'required|date_format:Y-m-d',
            'end_date'      => 'required|date_format:Y-m-d|after_or_equal:start_at',
            'leave_type_id' => [
                'required', 'exists:leave_types,id', new LeaveAllowed(),
            ],
            'is_half_day'   => [new HalfDayLeave],
            'document'      => ['nullable', 'mimes:doc,docx,pdf,jpg,jpeg,png'],
        ]);

        if ($validator->fails()) {
            return response()->error(__trans('validation_failed'), $validator->errors());
        }
        try {
            $data            = $validator->validated();
            $data['user_id'] = auth()->id();
            if (userCanApplyLeave(auth()->user())) {
                if ($request->hasFile('document')) {
                    $data['file_path'] = $this->upload($request->document, 'uploads/leave-documents');
                }
                $leave = Leave::create($data);
                $user  = User::find(auth()->id());
                $admin = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                // $hr = User::where('id', 18)->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Generated a Leave Request for ' . $leave->start_date,
                    'route'   => route('backend.leaves.show', $leave->id),
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
                // $hr->notify(new GenerateNotification($userData, $hr->id));

                // Send Leave notification to users who can Manage Leave
                $usersWithPermission = User::permission('Manage Leave')->where('id', '!=', $user->id)->get();
                foreach ($usersWithPermission as $userManageLeave) {
                    $userManageLeave->notify(new GenerateNotification($userData, $userManageLeave->id));
                }
                /* Send Email Notifications which set by admin */
                // send notification manager
                $managers = User::permission('Leave Request Manager Access')->where('id', '!=', $user->id)->get();
                foreach ($managers as $manager) {
                    if ($manager->ftoken) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Leave Added', 'Leave created', 14);
                    }
                }
                //end
                $this->emailNotification($userData);

                //$response = $this->fcmService->sendFcmMessage($user->ftoken, 'Leave Request', $userData['message']);
                return response()->success(createFlashMessage('Leave', 'submitted'));
            } else {
                return response()->error(__trans('leaves_are_not_allowed_in_probation_period'));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->error(config('project.error_message'));
    }

    public function emailNotification($userData)
    {
        $alertRecipients = AlertRecipient::with('user')->where('alert_status', 1)->get();

        foreach ($alertRecipients as $alertRecipient) {
            $userEmail = $alertRecipient->user->email;
            //Mail::to($userEmail)->send(new LeaveRequestMail($userData));
            try {
                Mail::to($userEmail)->send(new LeaveRequestMail($userData));
                EmailAlertLog::create([
                    'email'      => $userEmail,
                    'status'     => 'success',
                    'alert_type' => 'Leave Request',
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
