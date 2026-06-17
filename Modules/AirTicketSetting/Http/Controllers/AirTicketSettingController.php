<?php

namespace Modules\AirTicketSetting\Http\Controllers;

use App\Exports\AirTicketSettingExport;
use App\Traits\File;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Models\User;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Modules\AirTicketSetting\Exports\AirTicketSettingReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use App\Models\UserWorkDetail;
use App\Models\EMPAirTicket;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Payroll\Entities\UserPaySlip;
use Modules\Payroll\Entities\UserSalaryAllowance;

class AirTicketSettingController extends Controller
{
    use File;
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'airticketsetting');
        $this->fcmService = $fcmService;
    }

    /**
     * Display a listing of the airticketsetting.
     * @return Renderable
     */
    public function index(Request $request)
    {

        // canPerform('Manage AirTicketSetting');
        if ($request->ajax()) {
            $data = AirTicketSetting::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    $checked = $row->status == 1 ? "checked" : '';
                    $status = $row->status == 1 ? 0 : 1;
                    $action = route('backend.airticketsetting.update-status', [$row, $status]);
                    return createToggleButton('status', $action, $checked, __trans('are_you_sure_want_to_update_status?'));
                })
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(route('backend.settings.air-ticket-setting.show', $row), 'View', 'btn-success', 'fa fa-eye');

                    if (hasPermission('Edit AirTicketSetting')) {
                        $btn .= createActionButton(route('backend.settings.air-ticket-setting.toMultipleUser', $row->id), 'Assign To User', 'btn-success edit-button', 'fa fa-plus');
                        $btn .= createActionButton(route('backend.settings.air-ticket-setting.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete AirTicketSetting')) {
                        $btn .= createActionButton(route('backend.settings.air-ticket-setting.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('airticketsetting::index');
    }
    /**
     * show  the lisitng airticketsetting from storage.
     */
    public function show(AirTicketSetting $airticketsetting, $id)
    {
        $airticketsetting = AirTicketSetting::findOrFail($id);
        $currencies = config('currencies');

        return view('airticketsetting::show', compact('airticketsetting', 'currencies'));
    }



    public function create()
    {
        // canPerform('Create AirTicketSetting');
        $currencies = config('currencies');

        $html = view('airticketsetting::create', compact('currencies'))->render();
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function store(Request $request)
    {
        // canPerform('Create AirTicketSetting');

        $data =  $request->validate([
            'policy_name' => ['required'],
            'allowance_currency' => ['required'],
            'allowance_amount' => ['required'],
            'request_after_months' => ['required'],
            'country' => ['required'],
            // 'request_after_months_date' => ['required'],
            'policy_renewal_months' => ['required'],
            'request_limit_per_cycle' => ['required'],
            'allow_reimbursement' => ['nullable'],
            'allow_encashment' => ['nullable'],
            'allow_ticket_booking' => ['nullable'],
            'early_allow_ticket' => ['nullable'],
            'early_month' => ['nullable'],
            'encashment_amount' => ['nullable'],
        ]);


        $response = getErrorResponse();
        try {
            $data['request_after_from'] = $request->request_after_from;
            $airticketsetting = AirTicketSetting::create($data);
            $response = getSuccessResponse(createFlashMessage('Air Ticket Setting', 'created'));
        } catch (Exception $e) {
            dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AirTicketSetting $airticketsetting, $id)
    {
        $airticketsetting = AirTicketSetting::findOrFail($id);
        // canPerform('Edit AirTicketSetting');
        $currencies = config('currencies');
        $html = view('airticketsetting::edit', compact('airticketsetting', 'currencies'))->render();
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, AirTicketSetting $airticketsetting)
    {

        // canPerform('Create AirTicketSetting');
        $airticketsetting = AirTicketSetting::findOrFail($id);
        $data =  $request->validate([
            'policy_name' => ['required'],
            'allowance_currency' => ['required'],
            'allowance_amount' => ['required'],
            'request_after_months' => ['required'],
            'country' => ['required'],
            // 'request_after_months_date' => ['required'],
            'policy_renewal_months' => ['required'],
            'request_limit_per_cycle' => ['required'],
            'allow_reimbursement' => ['nullable'],
            'allow_encashment' => ['nullable'],
            'allow_ticket_booking' => ['nullable'],
            'early_allow_ticket' => ['nullable'],
            'early_month' => ['nullable'],
            'encashment_amount' => ['nullable'],
        ]);

        $response = getErrorResponse();
        try {
            $data['request_after_from'] = $request->request_after_from;
            $airticketsetting->update($data);
            $response = getSuccessResponse(createFlashMessage('Air Ticket Setting Request', 'updated'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    /**
     * Remove the specified airticketsetting from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(AirTicketSetting $airticketsetting, $id)
    {
        $airticketsetting = AirTicketSetting::findOrFail($id);
        $airticketsetting->delete();
        $response = getSuccessResponse(createFlashMessage('Air Ticket Setting Request', 'deleted'));
        return response()->json($response);
    }

    public function updateStatus(AirTicketSetting $airticketsetting, $status)
    {
        $response = getErrorResponse();
        try {
            $airticketsetting->status = $status;
            $airticketsetting->save();

            $response = getSuccessResponse(createFlashMessage('Status', 'updated'));
        } catch (Exception $e) {
        }

        return response()->json($response);
    }

    public function airTicketReport(Request $request)
    {
        // canPerform('Manage AirTicketSetting');

        if ($request->ajax()) {
            $data = EMPAirTicket::with('user')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return isset($row->user) ? $row->user->name : 'N/A';
                })
                // ->editColumn('status', function ($row) {
                //     $class = match ($row->status) {
                //         'Approved' => 'badge-success',
                //         'Reject' => 'badge-danger',
                //         default => 'badge-warning',
                //     };
                //     return "<span class='badge {$class}'>{$row->status}</span>";
                // })
                ->editColumn('status', fn($row) => $row->status ?? 'Pending')


                ->rawColumns(['user_name', 'status'])
                ->make(true);
        }

        return view('airticketsetting::airticketReport');
    }

    public function exportAirTicketReport()
    {

        $data = EMPAirTicket::with('user')->get();

        $pdf = Pdf::loadView('airticketsetting::exportPDF', [
            'data' => $data,
        ])->setPaper('tabloid', 'landscape');
        return $pdf->download('airticketReport' . date('Y-m-d') . '.pdf');
    }

    public function assign_to_multiple_user(Request $request, $id)
    {

        if ($request->post()) {
            try {

                foreach ($request->employee_ids as $userId) {
                    $result[] =  UserWorkDetail::updateOrCreate(
                        ['user_id' => $userId],
                        ['air_ticket_setting_id' => $id]
                    );
                }

                $response = getSuccessResponse(createFlashMessage('policy', 'Assigned to user'));
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }
            return response()->json($response);
        }
        // $policies = AirTicketSetting::findOrFail($id);
        $policies = AirTicketSetting::all();
        $employees  = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
            })->get();
        $currencies = config('currencies');
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
            $departments = Department::get();
        } else {

            $departments = DB::table('departments')
                ->join('users', 'departments.id', '=', 'users.department_id')
                ->where('users.id', auth()->user()->id)
                ->select('departments.*')
                ->get();
        }

        $html = view('airticketsetting::policy-assignment', compact('employees', 'id', 'departments'))->render();
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function getEmployees($departmentId)
    {
        $employees = User::with("workDetail")->where('department_id', $departmentId)->get();
        // dd($employees); 
        return response()->json($employees); // Return JSON response
    }
    // public function airTicketupdateStatus(Request $request)
    // {
    //     $request->validate([
    //         'id' => 'required|exists:e_m_p_air_tickets,id',
    //         'status' => 'required|in:Pending,Approved,Reject',
    //     ]);

    //     $ticket = EMPAirTicket::with('user')->findOrFail($request->id);
    //     $ticket->status = $request->status;
    //     $ticket->save();

    //     // If Approved → create allowance record
    //     if ($request->status === 'Approved') {
    //         $user = $ticket->user;
    //         $amount = $ticket->amount;
    //         $quantity = $ticket->quantity;
    //         $total = $amount * $quantity;

    //         $detailsGrouped = \App\Models\AirTicketDetail::where('user_id', $user->id)
    //             ->orderBy('created_at', 'desc')
    //             ->get()
    //             ->groupBy('user_id');


    //         $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
    //         $allowanceAmount = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0;

    //         $totalAmount = $allowanceAmount;
    //         $detailsStr = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
    //             $calculatedAmount = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
    //             $totalAmount += $calculatedAmount;
    //             return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
    //         })->implode(', ');

    //         $ticketDate = \Carbon\Carbon::parse($ticket->date);

    //         $payroll = UserPaySlip::where([
    //             'month_code' => $ticketDate->format('m'),
    //             'year'       => $ticketDate->format('Y'),
    //             'user_id'    => $user->id,
    //         ])->first();

    //         if ($payroll) {
    //             if ($payroll->is_close == 1) {
    //                 $nextMonthTimestamp = strtotime('+1 month');
    //                 UserSalaryAllowance::create([
    //                     'title' => 'Air ticket allowance',
    //                     'amount' => $totalAmount,
    //                     'user_id' => $user->id,
    //                     'allowance_type' => 'fixed',
    //                     'salary_id' => 0,
    //                     'percentage_amount' => 0.00,
    //                     'date' => now()->toDateString(),
    //                     'month_code' => date('m', $nextMonthTimestamp),
    //                     'year' => date('Y', $nextMonthTimestamp),
    //                     'is_fixed_for_current_month' => 1,
    //                 ]);
    //             } else {
    //                 UserSalaryAllowance::create([
    //                     'title' => 'Air ticket allowance',
    //                     'amount' => $totalAmount,
    //                     'user_id' => $user->id,
    //                     'allowance_type' => 'fixed',
    //                     'salary_id' => $payroll->id,
    //                     'percentage_amount' => 0.00,
    //                     'date' => now()->toDateString(),
    //                     'month_code' => date('m'),
    //                     'year' => date('Y'),
    //                     'is_fixed_for_current_month' => 1,
    //                 ]);
    //             }
    //         } else {
    //             UserSalaryAllowance::create([
    //                 'title' => 'Air ticket allowance',
    //                 'amount' => $totalAmount,
    //                 'user_id' => $user->id,
    //                 'allowance_type' => 'fixed',
    //                 'salary_id' => 0,
    //                 'percentage_amount' => 0.00,
    //                 'date' => now()->toDateString(),
    //                 'month_code' => date('m'),
    //                 'year' => date('Y'),
    //                 'is_fixed_for_current_month' => 1,
    //             ]);
    //         }
    //     }

    //     return response()->json(['message' => 'Status updated successfully!']);
    // }
    public function airTicketupdateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:e_m_p_air_tickets,id',
            'status' => 'required|in:Pending,Approved,Rejected',
        ]);

        $ticket = EMPAirTicket::with('user')->findOrFail($request->id);

        // Prevent changing Approved/Reject back to something else
        if (in_array($ticket->status, ['Approved', 'Rejected'])) {
            return response()->json(['message' => 'Cannot change status once approved or rejected'], 400);
        }

        $ticket->status = $request->status;
        // $ticket->approve_date = $request->status === 'Approved' ? now() : null;
        $ticket->approve_date = now();
        $ticket->save();

        // If Approved → create allowance
        if ($request->status === 'Approved') {
            $user = $ticket->user;

            // Fetch user country settings
            $profile = $user->profile()->first();
            $countryId = $profile?->country_id ?? 0;

            $airtickeSetting = \Modules\AirTicketSetting\Entities\AirTicketSetting::where('country', 0)->first();
            $airtickeCountrySetting = \Modules\AirTicketSetting\Entities\AirTicketSetting::where('country', $countryId)->first();

            $allowanceAmount = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting?->allowance_amount ?? 0;

            // Get ticket details
            $detailsGrouped = \App\Models\AirTicketDetail::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');

            $airTicketDetails = $detailsGrouped[$user->id] ?? collect();

            $totalAmount = $allowanceAmount;
            $detailsStr = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
                $calculatedAmount = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
                $totalAmount += $calculatedAmount;
                return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
            })->implode(', ');

            $ticketDate = \Carbon\Carbon::parse($ticket->date);

            $payroll = UserPaySlip::where([
                'month_code' => $ticketDate->format('m'),
                'year'       => $ticketDate->format('Y'),
                'user_id'    => $user->id,
            ])->first();

            $allowanceData = [
                'title' => 'Air ticket allowance',
                'amount' => round($totalAmount, 2), 
                'user_id' => $user->id,
                'allowance_type' => 'fixed',
                'percentage_amount' => 0.00,
                'date' => now()->toDateString(),
                'is_fixed_for_current_month' => 1,
            ];

            if ($payroll) {
                $allowanceData['salary_id'] = $payroll->is_close ? 0 : $payroll->id;
                $allowanceData['month_code'] = $payroll->is_close ? date('m', strtotime('+1 month')) : date('m');
                $allowanceData['year'] = $payroll->is_close ? date('Y', strtotime('+1 month')) : date('Y');
            } else {
                $allowanceData['salary_id'] = 0;
                $allowanceData['month_code'] = date('m');
                $allowanceData['year'] = date('Y');
            }

            \Modules\Payroll\Entities\UserSalaryAllowance::create($allowanceData);
        }

        return response()->json(['message' => 'Status updated successfully!']);
    }
}
