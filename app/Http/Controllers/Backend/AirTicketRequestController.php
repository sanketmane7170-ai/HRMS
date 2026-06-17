<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use App\Models\AirTicketRequest;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Carbon\Carbon;
use App\Models\AirTicketDetail;
use App\Notifications\ServiceRequest\GenerateNotification;

class AirTicketRequestController extends Controller
{
    public function getAirTicketRequest(Request $request)
    {
        view()->share('activeLink', 'Air-Ticket-Request');
        if ($request->ajax()) {
            $airticket = AirTicketRequest::with('user')->latest()->get();
            return DataTables::of($airticket)
                ->addIndexColumn()
                ->addColumn('employee', function ($row) {
                    return User::where('id', $row->user_id)->value('name');
                })
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
                ->editColumn('request_type', function ($row) {
                    return ucfirst($row->request_type);
                })
                ->editColumn('payment_mode', function ($row) {
                    return ucfirst($row->payment_mode);
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    switch ($row->status) {
                        case ('pending'):
                                $btn .= createActionButton(route('backend.air-ticket.requestDetails', $row->id), 'Update status', 'btn-info edit-button', 'fa fa-edit');
                            break;
                        case ('approved'):
                            $btn .= createActionButton(route('backend.employee.air-ticket.info', $row->id),'', 'btn-info edit-button', 'fa fa-info');
                            break;
                        case ('rejected'):
                            $btn .= createActionButton(route('backend.employee.air-ticket.info', $row->id),'', 'btn-info edit-button', 'fa fa-info');
                            $btn .= createActionButton(route('backend.air-ticket.requestDelete', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                            break;
                    }
                    return $btn;
                })
                ->rawColumns(['employee','action', 'status'])
                ->make(true);
        }
        return view('airticketsetting::admin.index');
    }

    public function requestDetails($id)
    {
        $airticketRequest = AirTicketRequest::findOrFail($id);

        $user = User::with('profile.country','workDetail')->where('id',$airticketRequest->user_id)->first();
        $country = $user->profile->country->id;
        $userPolicyid = $user->workDetail?->air_ticket_setting_id;
        if(!$userPolicyid ==null){
            $getPolicy = AirTicketSetting::where([['status', 1],['id',$userPolicyid]])->first();
        } else {
            $getPolicy = AirTicketSetting::where([['status', 1],['country',$country]])->first();
        }
        if(!$getPolicy){
            $response = getErrorResponse('Air Ticket Request Policy is Not Generated.');
            return response()->json($response);
        }
        $html = view('airticketsetting::admin.requestDetails',compact('airticketRequest','getPolicy'))->render();
        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function requestApprove(Request $request,$id)
    {
        $airTicket = AirTicketRequest::find($id);
        if($airTicket->request_type == 'reimbursement'){
            if($request->status == 'rejected'){
                 $request->validate([
                    'journey_date'      => 'required|date',
                    'return_date'       => 'required|date|after_or_equal:journey_date',
                    'requested_amount'  => 'required',
                    'status' => 'required',
                ]);
            } else {
                $request->validate([
                    'journey_date'      => 'required|date',
                    'return_date'       => 'required|date|after_or_equal:journey_date',
                    'requested_amount'  => 'required',
                    'status' => 'required',
                    'payment_mode' => 'required',
                ]);
            }
        }
        if($airTicket->request_type == 'booking'){
            if($request->status == 'rejected'){
                $request->validate([
                    'journey_date'  => 'required|date',
                    'return_date'   => 'required|date|after_or_equal:journey_date',
                    'location_from' => 'required',
                    'location_to'   => 'required',
                    'status' => 'required',
                ]);
            } else {
                $request->validate([
                    'journey_date'  => 'required|date',
                    'return_date'   => 'required|date|after_or_equal:journey_date',
                    'location_from' => 'required',
                    'location_to'   => 'required',
                    'status' => 'required',
                    'ticket_proof'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ]);
            }
        }
        if($airTicket->request_type == 'earlybooking'){
            if($request->status == 'rejected'){
                 $request->validate([
                    'journey_date'      => 'required|date',
                    'return_date'       => 'required|date|after_or_equal:journey_date',
                    'requested_amount'  => 'required',
                    'status' => 'required',
                ]);
            } else {
                $request->validate([
                    'journey_date'      => 'required|date',
                    'return_date'       => 'required|date|after_or_equal:journey_date',
                    'requested_amount'  => 'required',
                    'status' => 'required',
                    'payment_mode' => 'required',
                ]);
            }
        }
        $response = getErrorResponse();
        try {
            $poofpath = null;
            $user = User::with('profile.country','workDetail')->where('id',$airTicket->user_id)->first();
            $country = $user->profile->country->id;
            $userPolicyid = $user->workDetail?->air_ticket_setting_id;
            if(!$userPolicyid ==null){
                $getPolicy = AirTicketSetting::where([['status', 1],['id',$userPolicyid]])->first();
            } else {
                $getPolicy = AirTicketSetting::where([['status', 1],['country',$country]])->first();
            }
            if(!$getPolicy){
                $response = getErrorResponse('Air Ticket Request Policy is Not Generated.');
                return response()->json($response);
            }
            if ($request->hasFile('ticket_proof')) {
                $file = $request->ticket_proof;
                $fileName =  time() . '.' . $file->extension();
                $path = public_path('uploads/users/'.$user->id.'/air_tickets');
                if (!file_exists($path)) {
                    mkdir($path, 0775, true);
                }
                $ret = $file->move($path, $fileName);
                $poofpath = 'uploads/users/'.$user->id.'/air_tickets/'. $fileName;
            } else {
                $poofpath = $airTicket->ticket_proof;
            }
            if($request->status == 'approved'){
                $approvedDate = Carbon::now()->toDateString();
            } else {
                $approvedDate = null;
            }
            $totalAmount = $request->requested_amount;
            // Gather previous ticket breakdowns (if any)
            $allowanceAmount = $request->requested_amount;
            $detailsGrouped = AirTicketDetail::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');
            $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
            $detailsStr = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
                $calculatedAmount = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
                $totalAmount += $calculatedAmount;
                return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
            })->implode(', ');
            $remark = $airTicketDetails->isNotEmpty() ? "Amount Breakdown: {$detailsStr}<br/>" . $request->admin_remark : $request->admin_remark;

            $airTicket->status = $request->status;
            $airTicket->payment_mode = $request->payment_mode;
            $airTicket->requested_amount = $totalAmount;
            $airTicket->ticket_proof = $poofpath;
            $airTicket->location_from = $request->location_from;
            $airTicket->location_to = $request->location_to;
            $airTicket->admin_remark = $remark;
            $airTicket->approved_date = $approvedDate;
            $airTicket->save();
            
            $userData = [
                'id' => auth()->id(),
                'name' => $user->name,
                'email' => $user->email,
                'message' => 'Air Ticket request updated successfully.',
                'route' => route('backend.employee.air-ticket.index'),
            ];
            $user->notify(new GenerateNotification($userData, $user->id));
            $response = getSuccessResponse('Air Ticket request updated successfully.');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function policyDetails($userId){
        $user = User::with('profile.country','workDetail')->where('id',$userId)->first();
        $country = $user->profile->country->id;
        $userPolicyid = $user->workDetail?->air_ticket_setting_id;
        if(!$userPolicyid ==null){
            $getPolicy = AirTicketSetting::where([['status', 1],['id',$userPolicyid]])->first();
        } else {
            $getPolicy = AirTicketSetting::where([['status', 1],['country',$country]])->first();
        }
        if(!$getPolicy){
            return response()->json(['success' => 'Air Ticket Request Policy is Not Generated.','policyAmount' => 0]);
        }
        return response()->json(['success' => 'get policy amount successfully.','policyAmount' => $getPolicy->allowance_amount]);
    }

    public function requestDelete($id)
    {
        try {
            $airTicket = AirTicketRequest::find($id);
            $airTicket->delete();
            $response = getSuccessResponse(createFlashMessage('Air Ticket', 'Deleted'));
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "This service is already associated, cannot be removed.";
                $response['error'] = "This service is already associated, cannot be removed.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }
}
