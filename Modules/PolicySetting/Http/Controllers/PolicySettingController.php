<?php
namespace Modules\PolicySetting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\endOfServicePolicy;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Leave\Entities\LeaveType;
use Modules\PolicySetting\Entities\PolicySettings;
use Yajra\DataTables\Facades\DataTables;

class PolicySettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $view = 'policysetting::index';
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            $view = 'policysetting::index';
        }
        view()->share('activeLink', 'Policy Setting');
        return view($view);
    }

    public function leaves_policy(Request $request)
    {
        // canPerform('leaves_policy');
        if ($request->isMethod('post')) {
            try {
                // PolicySettings::where("type", "leave")->update(["status" => 0]);
                // foreach ($request->all() as $name => $status) {
                //     if ($name !== "_token") {
                //         PolicySettings::updateOrCreate(
                //             [
                //                 'type' => 'leave',
                //                 'name' => $name,
                //             ],
                //             [
                //                 'status' => $status,
                //             ]
                //         );
                //     }
                // }
                $leavepolicy = PolicySettings::where("type", "leave")
                    ->where("name", $request->name)
                    ->first();
                if ($leavepolicy) {
                    // $response = getFailureResponse(createFlashMessage('Already Created', 'failed'));
                    $response          = getFailureResponse('The Policy name is already created.');
                    $response['error'] = "The Policy name is already created.";
                    return response()->json($response);
                }

                $data["name"]        = $request->name;
                $data["type"]        = "leave";
                $data["description"] = $request->description;
                $data["policy"]      = json_encode($request->policy);

                PolicySettings::Create($data);

                $response = getSuccessResponse(createFlashMessage('Leaves Policy', 'created'));
            } catch (Exception $e) {
                dd($e->getMessage());
                $response['error'] = $e->getMessage();
            }

            return response()->json($response);
        }
        $leavepolicy = PolicySettings::where("type", "leave")->get();

        $html = view('policysetting::leaves_policy', compact('leavepolicy'))->render();
        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function edit_leaves_policy(Request $request, $id)
    {
        // canPerform('leaves_policy');
        if ($request->isMethod('post')) {
            try {

                $policy              = PolicySettings::findOrFail($id);
                $policy->name        = $request->name;
                $policy->type        = "leave"; // Assuming 'leave' is a fixed value
                $policy->description = $request->description;
                $policy->policy      = json_encode($request->policy);
                $policy->save();

                $response = getSuccessResponse(createFlashMessage('Leaves Policy', 'created'));
            } catch (Exception $e) {
                dd($e->getMessage());
                $response['error'] = $e->getMessage();
            }

            return response()->json($response);
        }
        // $leavepolicy = PolicySettings::where("id", $id)->get();
        $leavepolicy = PolicySettings::findOrFail($id);
        // dd($leavepolicy);
        $html = view('policysetting::edit_leaves_policy', compact('leavepolicy'))->render();
        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function attendance_policy(Request $request)
    {
        // canPerform('leaves_policy');
        if ($request->isMethod('post')) {

            try {
                PolicySettings::where("type", "attendance")->update(["status" => 0]);
                foreach ($request->all() as $name => $status) {
                    if ($name !== "_token") {
                        PolicySettings::updateOrCreate(
                            [
                                'type' => 'attendance',
                                'name' => $name,
                            ],
                            [
                                'status' => $status,
                            ]
                        );
                    }
                }
                $response = getSuccessResponse(createFlashMessage('Attendance Policy', 'updated'));
            } catch (Exception $e) {
                dd($e->getMessage());
                $response['error'] = $e->getMessage();
            }

            return response()->json($response);
        }
        $attendancepolicy = PolicySettings::where("type", "attendance")->get();
        $html             = view('policysetting::attendance_policy', compact('attendancepolicy'))->render();
        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }
    public function overtime_policy(Request $request)
    {
        // canPerform('leaves_policy');
        if ($request->isMethod('post')) {
            try {
                $overtime_hours = Setting::updateOrCreate(
                    [
                        'key' => 'overtime_hours',
                    ],
                    [
                        'value' => $request->overtime_hours,
                    ]
                );

                $response = getSuccessResponse(createFlashMessage('Overtime Policy', 'updated'));
            } catch (Exception $e) {
                dd($e->getMessage());
                $response['error'] = $e->getMessage();
            }
            return response()->json($response);
        }
        $overtime_hours   = Setting::where("key", "overtime_hours")->first();
        $autoaddextraWork = Setting::where('key', 'auto_add_extra_work')->first();
        $html             = view('policysetting::overtime_policy', compact('overtime_hours', 'autoaddextraWork'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function endOfServicePolicy(Request $request)
    {

        if ($request->ajax()) {
            $data = endOfServicePolicy::with('type')->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn  = '';
                    $btn .= createActionButton(route('backend.settings.editendOfServicePolicy', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('backend.settings.removeservicepolicy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('policysetting::showendservicepolicy');
    }

    public function addendOfServicePolicy(Request $request)
    {

        if ($request->isMethod('post')) {
            try {
                endOfServicePolicy::updateOrCreate(
                    [
                        'leave_type_id' => $request->leave_type_id,
                    ],
                    [
                        'salary_type' => $request->salary_type,
                        'month_day'   => $request->month_day,
                    ]
                );
                $response = getSuccessResponse(createFlashMessage('End Of Service Policy', 'created'));
            } catch (Exception $e) {
                dd($e->getMessage());
                $response['error'] = $e->getMessage();
            }
            return response()->json($response);
        }
        $leaveTypes = LeaveType::get(['id', 'name']);
        $html       = view('policysetting::addendofservicepolicy', compact('leaveTypes'))->render();
        $response   = [
            'success' => true,
            'html'    => $html,
        ];
        return response()->json($response);
    }

    public function editendOfServicePolicy(Request $request, $id)
    {

        if ($request->isMethod('post')) {
            try {
                endOfServicePolicy::updateOrCreate(
                    [
                        'leave_type_id' => $request->leave_type_id,
                    ],
                    [
                        'salary_type' => $request->salary_type,
                        'month_day'   => $request->month_day,
                    ]
                );
                $response = getSuccessResponse(createFlashMessage('End Of Service Policy', 'created'));
            } catch (Exception $e) {
                dd($e->getMessage());
                $response['error'] = $e->getMessage();
            }
            return response()->json($response);
        }
        $spolicy    = endOfServicePolicy::where('id', $id)->first();
        $leaveTypes = LeaveType::get(['id', 'name']);
        $html       = view('policysetting::editendofservicepolicy', compact('leaveTypes', 'spolicy'))->render();
        $response   = [
            'success' => true,
            'html'    => $html,
        ];
        return response()->json($response);
    }

    public function removeservicepolicy($id)
    {
        try {
            endOfServicePolicy::where('id', $id)->delete();
            $response = getSuccessResponse(createFlashMessage('End Of Service Policy', 'deleted'));
        } catch (Exception $e) {
            dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function addshiftPolicy(Request $request)
    {
        if ($request->isMethod('post')) {
            try {

                $isAllowed = $request->input('allow');

                if ($isAllowed) {
                    $overtime_hours = Setting::updateOrCreate(
                        [
                            'key' => 'is_admin_shift_show_to_manager',
                        ],
                        [
                            'value' => true,
                        ]
                    );
                    $overtime_hours = Setting::updateOrCreate(
                        [
                            'key' => 'shift_show_to_all',
                        ],
                        [
                            'value' => false,
                        ]
                    );
                } else {
                    $overtime_hours = Setting::updateOrCreate(
                        [
                            'key' => 'is_admin_shift_show_to_manager',
                        ],
                        [
                            'value' => false,
                        ]
                    );
                }
                $response = getSuccessResponse(createFlashMessage('Shift Policy', 'updated'));
            } catch (Exception $e) {
                dd($e->getMessage());
                $response['error'] = $e->getMessage();
            }
            return response()->json($response);
        }
        $shift_show_to_all              = Setting::where("key", "shift_show_to_all")->first();
        $is_admin_shift_show_to_manager = Setting::where("key", "is_admin_shift_show_to_manager")->first();
        $html                           = view('policysetting::shift_policy', compact('is_admin_shift_show_to_manager', 'shift_show_to_all'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function viewToAllShiftPolicy(Request $request)
    {
        if ($request->isMethod('post')) {
            try {

                $isAllowed = $request->input('allow');

                if ($isAllowed) {
                    $overtime_hours = Setting::updateOrCreate(
                        [
                            'key' => 'shift_show_to_all',
                        ],
                        [
                            'value' => true,
                        ]
                    );
                    $overtime_hours = Setting::updateOrCreate(
                        [
                            'key' => 'is_admin_shift_show_to_manager',
                        ],
                        [
                            'value' => false,
                        ]
                    );
                } else {
                    $overtime_hours = Setting::updateOrCreate(
                        [
                            'key' => 'shift_show_to_all',
                        ],
                        [
                            'value' => false,
                        ]
                    );
                }
                $response = getSuccessResponse(createFlashMessage('Shift Policy', 'updated'));
            } catch (Exception $e) {
                dd($e->getMessage());
                $response['error'] = $e->getMessage();
            }
            return response()->json($response);
        }
        $shift_show_to_all = Setting::where("key", "shift_show_to_all")->first();
        $html              = view('policysetting::shift_policy', compact('shift_show_to_all'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function leavesPolicy(Request $request)
    {
        $daily_leave_policy        = Setting::where("key", "daily_leave_policy")->first();
        $monthwiseLeaveSetting     = Setting::where('key', 'is_month_wise_show_leave')->first();
        $annual_leave_policy       = Setting::where('key', 'annual_leave_policy')->first();
        $monthwise2leave           = Setting::where('key', 'is_month_wise_2_leave')->first();
        $yearGiven2Leave           = Setting::where('key', 'is_year_given_2_leave')->first();
        $allowNegativeLeave        = Setting::where('key', 'allow_negative_leave')->first();
        $leaveAllowInProbation     = Setting::where('key', 'leave_probation_module')->first();
        $leaveRecurringPolicy      = Setting::where('key', 'leave_recurring_policy')->first();
        $newUserDailyLeavePolicy   = Setting::where('key', 'new_user_daily_leave_policy')->first();
        $newUserMonthlyLeavePolicy = Setting::where('key', 'new_user_monthly_leave_policy')->first();
        $html                      = view('policysetting::leavePolicy', compact('daily_leave_policy', 'allowNegativeLeave', 'monthwiseLeaveSetting', 'monthwise2leave', 'yearGiven2Leave', 'annual_leave_policy', 'leaveAllowInProbation', 'leaveRecurringPolicy', 'newUserDailyLeavePolicy', 'newUserMonthlyLeavePolicy'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function early_comers_policy()
    {
        $view = 'policysetting::index';
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            $view = 'policysetting::index';
        }
        return view($view);
    }
    public function late_comers_policy()
    {
        $view = 'policysetting::index';
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            $view = 'policysetting::index';
        }
        return view($view);
    }

    // public function payrollPolicy(Request $request)
    // {

    //     if ($request->isMethod('post')) {

    //         try {

    //             $payrollpolicy = PolicySettings::where("type", "payroll")
    //                 ->where("name", $request->name)
    //                 ->first();

    //             if ($payrollpolicy) {

    //                 $response          = getFailureResponse('The Policy name is already created.');
    //                 $response['error'] = "The Policy name is already created.";

    //                 return response()->json($response);

    //             }

    //             $data["name"]        = $request->name;
    //             $data["type"]        = "payroll";
    //             $data["description"] = $request->description;
    //             $data["policy"]      = json_encode($request->policy);

    //             PolicySettings::create($data);

    //             $response = getSuccessResponse(createFlashMessage('Payroll Policy', 'created'));

    //         } catch (Exception $e) {

    //             $response['error'] = $e->getMessage();

    //         }

    //         return response()->json($response);

    //     }

    //     $payrollpolicy = PolicySettings::where("type", "payroll")->get();

    //     $html = view('policysetting::payroll_policy', compact('payrollpolicy'))->render();

    //     return response()->json([
    //         'success' => true,
    //         'html'    => $html,
    //     ]);

    // }
    public function payrollPolicy(Request $request)
    {

        if ($request->isMethod('post')) {

            // dd($request->all());        dd(count($request->policy) );

            try {

                if (count($request->policy) < 3) {
                    $response = getFailureResponse('Please select at least 3 policy options.');
                    return response()->json($response);
                }

                $data = [
                    "description" => $request->description,
                    "policy"      => json_encode($request->policy),
                ];

                PolicySettings::updateOrCreate(
                    [
                        "type" => "payroll",
                        "name" => $request->name,
                    ],
                    $data
                );

                $response = getSuccessResponse(createFlashMessage('Payroll Policy', 'saved'));

            } catch (Exception $e) {

                $response['error'] = $e->getMessage();

            }

            return response()->json($response);
        }

        $payrollpolicy = PolicySettings::where("type", "payroll")->get();

        $html = view('policysetting::payroll_policy', compact('payrollpolicy'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }
    public function deletePayrollPolicy($id)
    {
        try {

            $policy = PolicySettings::findOrFail($id);
            $policy->delete();

            return response()->json(getSuccessResponse('Payroll Policy deleted successfully'));

        } catch (\Exception $e) {

            return response()->json(getFailureResponse($e->getMessage()));
        }
    }
}
