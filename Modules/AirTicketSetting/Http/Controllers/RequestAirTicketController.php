<?php
namespace Modules\AirTicketSetting\Http\Controllers;

use App\Models\AirTicketDetail;
use App\Models\AirTicketRequest;
use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use App\Traits\File;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use Yajra\DataTables\Facades\DataTables;

class RequestAirTicketController extends Controller
{
    use File;
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'Air-Ticket-Request');
        $this->fcmService = $fcmService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $airticket = AirTicketRequest::with('user')->where('user_id', auth()->id())->latest()->get();
            return DataTables::of($airticket)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    switch ($row->status) {
                        case ('pending'):
                            $class = "<span class='badge badge-warning'>Pending</span>";
                            break;
                        case ('approved'):
                            $class = "<span class='badge badge-success'>Approved</span>";
                            break;
                        case ('rejected'):
                            $class = "<span class='badge badge-danger'>Rejected</span>";
                            break;
                    }
                    return $class;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    switch ($row->status) {
                        case ('pending'):
                            $btn .= createActionButton(route('backend.employee.air-ticket.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                            $btn .= createActionButton(route('backend.employee.air-ticket.delete', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                            break;
                        case ('approved'):
                            $btn .= createActionButton(route('backend.employee.air-ticket.info', $row->id), '', 'btn-info edit-button', 'fa fa-info');
                            break;
                        case ('rejected'):
                            $btn .= createActionButton(route('backend.employee.air-ticket.info', $row->id), '', 'btn-info edit-button', 'fa fa-info');
                            break;
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('airticketsetting::employee.index');
    }

    public function create()
    {
        $user         = User::with('profile.country', 'workDetail')->where('id', auth()->id())->first();
        $country      = $user->profile?->country->id;
        $userPolicyid = $user->workDetail?->air_ticket_setting_id;
        if (! $userPolicyid == null) {
            $getPolicy = AirTicketSetting::where([['status', 1], ['id', $userPolicyid]])->first();
        } else {
            $getPolicy = AirTicketSetting::where([['status', 1], ['country', $country]])->first();
        }
        if (! $getPolicy) {
            return response()->json([
                'success' => false,
                'message' => 'Air Ticket Request Policy is Not Generated. Please contact to Admin.',
            ]);
        }
        if($getPolicy->allow_reimbursement==0 && $getPolicy->allow_ticket_booking==0 && $getPolicy->early_allow_ticket==0){
            return response()->json([
                'success' => false,
                'message' => 'Air Ticket Policy Not Generated. Please contact to Admin.',
            ]);
        }
        $notEligible = 1;
        $currentDate = Carbon::now()->format('Y-m-d');
        $fromMonth = $getPolicy->request_after_from;
        $renewalMonth = $getPolicy->policy_renewal_months;
        $earlyMonth = $getPolicy->early_allow_ticket==1 ? $getPolicy->early_month : 0;
        $userRenewalMonth = $user->workDetail?->renewal_air_ticket;
        if ($userRenewalMonth != null) {
            if ($userRenewalMonth == "1_year") {
                $renewalMonth = 12;
            }
            if ($userRenewalMonth == "2_year") {
                $renewalMonth = 24;
            }
        }
        if($fromMonth == 'hiring_date'){
            $afterMonth = $getPolicy->request_after_months;
            if($userRenewalMonth != null){
                if($userRenewalMonth=="1_year"){
                    $afterMonth = 12;
                }
                if($userRenewalMonth=="2_year"){
                    $afterMonth = 24;
                }
            }
            $afterMonth = (int) $afterMonth - $earlyMonth;
            $userHireDate = Carbon::parse($user->workDetail?->joining_date);
            $eligibleDate = $userHireDate->copy()->addMonths($afterMonth)->format('Y-m-d');
            if ($currentDate < $eligibleDate) {
                $notEligible = 0;
                $showError = 'You are not eligible to request air ticket before hiring date, eligible date was: '.Carbon::parse($eligibleDate)->format('d-m-Y');
            }
        } else {
            $afterMonth = $getPolicy->request_after_months;
            if($userRenewalMonth != null){
                if($userRenewalMonth=="1_year"){
                    $afterMonth = 12;
                }
                if($userRenewalMonth=="2_year"){
                    $afterMonth = 24;
                }
            }
            $afterMonth = (int) $afterMonth - $earlyMonth;
            $probationDate = Carbon::parse($user->workDetail?->probation_end_date);
            $prEligibleDate = $probationDate->copy()->addMonths($afterMonth)->format('Y-m-d');
            if ($currentDate < $prEligibleDate) {
                $notEligible = 0;
                $showError = 'You are not eligible to request air ticket before probation date ,eligible date was: '.Carbon::parse($prEligibleDate)->format('d-m-Y');
            }
        }

        $isaddrequest = AirTicketRequest::where('user_id', auth()->id())->where('status', '!=', 'rejected')->latest()->first();
        if ($isaddrequest) {
            $isaddtomore      = 0;
            $lastRequestDate  = Carbon::parse($isaddrequest->journey_date);
            $nextEligibleDate = $lastRequestDate->copy()->addMonths($renewalMonth)->format('Y-m-d');

            $addedRequest = AirTicketRequest::where('user_id', auth()->id())
                ->whereBetween('journey_date', [$lastRequestDate, $nextEligibleDate])
                ->count();
            $requestLimit     = $getPolicy->request_limit_per_cycle;
            $userRequestLimit = $user->workDetail?->air_ticket_count;
            if ($userRequestLimit > 0) {
                $requestLimit = $userRequestLimit;
            }
            if ($addedRequest >= $requestLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Air Ticket limit was over. Please contact to Admin.',
                ]);
            } else {
                $isaddtomore = 1;
            }
            if ($isaddtomore == 0) {
                if ($currentDate < $nextEligibleDate) {
                    $notEligible = 0;
                    $showError   = 'You are not eligible to request air ticket add more than limit';
                }
            }
        }
        if ($notEligible == 0) {
            return response()->json([
                'success' => false,
                'message' => $showError, //'Air Ticket not eligible for your policy. Please contact to Admin.',
            ]);
        }
        $html = view('airticketsetting::employee.create', compact('getPolicy'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->request_type == 'reimbursement') {
            $request->validate([
                'journey_date'     => 'required|date',
                'return_date'      => 'required|date|after_or_equal:journey_date',
                'requested_amount' => 'required',
                'ticket_proof'     => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);
        }
        if ($request->request_type == 'booking') {
            $request->validate([
                'journey_date'  => 'required|date',
                'return_date'   => 'required|date|after_or_equal:journey_date',
                'location_from' => 'required',
                'location_to'   => 'required',
            ]);
        }

        $response = getErrorResponse();
        try {
            $poofpath = null;
            if ($request->hasFile('ticket_proof')) {
                $file     = $request->ticket_proof;
                $fileName = time() . '.' . $file->extension();
                $path     = public_path('uploads/users/' . auth()->id() . '/air_tickets');
                if (! file_exists($path)) {
                    mkdir($path, 0775, true);
                }
                $ret      = $file->move($path, $fileName);
                $poofpath = 'uploads/users/' . auth()->id() . '/air_tickets/' . $fileName;
            }
            $user         = User::with('profile.country', 'workDetail')->where('id', auth()->id())->first();
            $country      = $user->profile->country->id;
            $userPolicyid = $user->workDetail?->air_ticket_setting_id;
            if (! $userPolicyid == null) {
                $getPolicy = AirTicketSetting::where([['status', 1], ['id', $userPolicyid]])->first();
            } else {
                $getPolicy = AirTicketSetting::where([['status', 1], ['country', $country]])->first();
            }
            if (! $getPolicy) {
                $response = getErrorResponse('Air Ticket Request Policy is Not Generated. Please contact to Admin.');
                return response()->json($response);
            }
            $airTicketDetails = AirTicketDetail::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');
            if ($airTicketDetails->isEmpty()) {
                if ($request->requested_amount > $getPolicy->allowance_amount) {
                    $response = getErrorResponse('Requested amount exceeds the policy limit of ' . $getPolicy->allowance_amount);
                    return response()->json($response);
                }
            }

            $addrequest = AirTicketRequest::create([
                'user_id'          => auth()->id(),
                'journey_date'     => $request->journey_date,
                'return_date'      => $request->return_date,
                'location_from'    => $request->location_from,
                'location_to'      => $request->location_to,
                'requested_amount' => $request->requested_amount,
                'ticket_proof'     => $poofpath,
                'admin_remark'     => $request->admin_remark,
                'request_type'     => $request->request_type,
            ]);
            $userData = [
                'id'      => auth()->id(),
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Air Ticket request has been created by ' . $user->name,
                'route'   => route('backend.air-ticket.request'),
            ];
            $admin = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
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
            $response = getSuccessResponse('Air Ticket request created successfully.');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function edit($id)
    {
        $airTicket = AirTicketRequest::find($id);
        $html      = view('airticketsetting::employee.edit', compact('airTicket'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($request->request_type == 'reimbursement') {
            $request->validate([
                'journey_date'     => 'required|date',
                'return_date'      => 'required|date|after_or_equal:journey_date',
                'requested_amount' => 'required',
            ]);
        }
        if ($request->request_type == 'booking') {
            $request->validate([
                'journey_date'  => 'required|date',
                'return_date'   => 'required|date|after_or_equal:journey_date',
                'location_from' => 'required',
                'location_to'   => 'required',
            ]);
        }
        $response = getErrorResponse();
        try {
            $airTicket = AirTicketRequest::find($id);
            $poofpath  = null;
            if ($request->hasFile('ticket_proof')) {
                $file     = $request->ticket_proof;
                $fileName = time() . '.' . $file->extension();
                $path     = public_path('uploads/users/' . auth()->id() . '/air_tickets');
                if (! file_exists($path)) {
                    mkdir($path, 0775, true);
                }
                $ret                     = $file->move($path, $fileName);
                $poofpath                = 'uploads/users/' . auth()->id() . '/air_tickets/' . $fileName;
                $airTicket->ticket_proof = $poofpath;
            }
            $user         = User::with('profile.country', 'workDetail')->where('id', auth()->id())->first();
            $country      = $user->profile->country->id;
            $userPolicyid = $user->workDetail?->air_ticket_setting_id;
            if (! $userPolicyid == null) {
                $getPolicy = AirTicketSetting::where([['status', 1], ['id', $userPolicyid]])->first();
            } else {
                $getPolicy = AirTicketSetting::where([['status', 1], ['country', $country]])->first();
            }
            if (! $getPolicy) {
                $response = getErrorResponse('Air Ticket Request Policy is Not Generated. Please contact to Admin.');
                return response()->json($response);
            }
            $airTicketDetails = AirTicketDetail::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');
            if ($airTicketDetails->isEmpty()) {
                if ($request->requested_amount > $getPolicy->allowance_amount) {
                    $response = getErrorResponse('Requested amount exceeds the policy limit of ' . $getPolicy->allowance_amount);
                    return response()->json($response);
                }
            }

            $airTicket->journey_date     = $request->journey_date;
            $airTicket->return_date      = $request->return_date;
            $airTicket->location_from    = $request->location_from;
            $airTicket->location_to      = $request->location_to;
            $airTicket->requested_amount = $request->requested_amount;
            $airTicket->save();

            $response = getSuccessResponse('Air Ticket request updated successfully.');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function requestDetails(Request $request, $id)
    {
        $airTicket = AirTicketRequest::with('user')->find($id);
        $html      = view('airticketsetting::employee.request_details', compact('airTicket'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function getPolicy()
    {
        $user         = User::with('profile.country', 'workDetail')->where('id', auth()->id())->first();
        $country      = $user->profile->country->id;
        $userPolicyid = $user->workDetail?->air_ticket_setting_id;
        if (! $userPolicyid == null) {
            $getPolicy = AirTicketSetting::where([['status', 1], ['id', $userPolicyid]])->first();
        } else {
            $getPolicy = AirTicketSetting::where([['status', 1], ['country', $country]])->first();
        }
        $airTicketDetails = AirTicketDetail::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');
        if ($airTicketDetails->isEmpty()) {
            $allowance_amount = $getPolicy->allowance_amount;
        } else {
            $allowance_amount = '';
        }
        if (! $getPolicy) {
            return response()->json(['success' => 'Air Ticket Request Policy is Not Generated. Please contact to Admin.', 'policyAmount' => 0]);
        }
        return response()->json(['success' => 'get policy amount successfully.', 'policyAmount' => $allowance_amount]);
    }

    public function destroy($id)
    {
        try {
            $airTicket = AirTicketRequest::find($id);
            $airTicket->delete();
            $response = getSuccessResponse(createFlashMessage('Air Ticket', 'Deleted'));
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "This service is already associated, cannot be removed.";
                $response['error']   = "This service is already associated, cannot be removed.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }
}
