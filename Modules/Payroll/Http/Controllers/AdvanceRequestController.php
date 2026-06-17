<?php
namespace Modules\Payroll\Http\Controllers;

use App\Exports\AdvanceRequestReportExport;
use App\Models\AdvanceRequestHistory;
use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Entities\AdvanceRequest;
use Yajra\DataTables\Facades\DataTables;

class AdvanceRequestController extends Controller
{

    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
        view()->share('activeLink', 'advance-request');
    }
    public function index(Request $request)
    {
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {

            if ($request->ajax()) {
                // Sanket - Added requested_date and approved_date to select query for displaying in DataTables
                $advanceRequest = AdvanceRequest::with('user')->select(['id', 'reference_number', 'type', 'reason', 'amount', 'instalments', 'start_month', 'status', 'approved_amount', 'loan_months', 'installment_amount', 'installments_paid', 'installments_pending', 'user_id', 'loan_mode', 'requested_date', 'approved_date', 'rejected_date', 'rejection_reason'])->orderBy('id', 'desc');

                return DataTables::of($advanceRequest)
                    ->addColumn('user_name', function ($advanceRequest) {
                        return $advanceRequest->user ? $advanceRequest->user->name : '';
                    })
                    ->filterColumn('user_name', function ($query, $keyword) {
                        $query->whereHas('user', function ($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                // Sanket - Add formatted start_month to match other date columns (DD/MM/YYYY format)
                    ->addColumn('formatted_start_month', function ($advanceRequest) {
                        return $advanceRequest->formatted_start_month;
                    })
                    ->addColumn('formatted_requested_date', function ($advanceRequest) {
                        return $advanceRequest->formatted_requested_date;
                    })
                    ->addColumn('formatted_approved_date', function ($advanceRequest) {
                        return $advanceRequest->formatted_approved_date;
                    })
                    ->addColumn('action_date', function ($advanceRequest) {
                        return $advanceRequest->action_date;
                    })
                    ->addColumn('approval', function ($advanceRequest) {
                        $btn = '';
                        if ($advanceRequest->status === 'pending') {
                            $btn .= createActionButton(route('backend.payroll.advance.advancepay-approval', [$advanceRequest->reference_number]), 'Approval', 'btn-success edit-button', '');
                        }
                        if ($advanceRequest->status === 'pending') {
                            $btn .= createActionButton(route('backend.payroll.advance.editRequest', [$advanceRequest->reference_number, $advanceRequest->id]), 'Edit', 'btn-warning edit-button', 'datatable');
                        }
                        if ($advanceRequest->status === 'pending') {
                            $btn .= createActionButton(route('backend.payroll.advance.deleteRequest', [$advanceRequest->reference_number, $advanceRequest->id]), 'Delete', 'btn-danger action-button', 'datatable');
                        }
                        if ($advanceRequest->status === 'approved') {
                            $btn .= createActionButton(route('backend.payroll.advance.deleteRequest', [$advanceRequest->reference_number]), 'Delete', 'btn-danger action-button', 'datatable');
                        }
                        if ($advanceRequest->status !== 'closed') {
                            //$btn .= createActionButton(route('backend.payroll.advance.advancepay-update', [$advanceRequest->reference_number]), 'Update', 'btn-warning edit-button', '');
                        }
                        $btn .= createActionButton(route('backend.payroll.advance.advancepay-details', [$advanceRequest->reference_number]), '', 'btn-primary edit-button', 'fa fa-info-circle');
                        return $btn;
                    })
                    ->rawColumns(['approval', 'id'])
                    ->make(true);
            }
        } else {

            if ($request->ajax()) {

                $userId = auth()->user()->id;
                // Sanket - Added requested_date and approved_date to employee section query as well
                $advanceRequest = AdvanceRequest::with('user')->select([
                    'id',
                    'reference_number',
                    'type',
                    'reason',
                    'amount',
                    'instalments',
                    'start_month',
                    'status',
                    'approved_amount',
                    'loan_months',
                    'installment_amount',
                    'installments_paid',
                    'installments_pending',
                    'user_id',
                    'loan_mode',
                    'requested_date',   // Sanket - Date when request was created
                    'approved_date',    // Sanket - Date when request was approved
                    'rejected_date',    // Sanket - Date when request was rejected
                    'rejection_reason', // Sanket - Reason when request was rejected
                ]);

                if (hasPermission('Manage Advance Salary Request')) {
                    $advanceRequest = $advanceRequest;
                } else {
                    $advanceRequest = $advanceRequest->where('user_id', $userId);
                }
                $advanceRequest = $advanceRequest->orderBy('id', 'desc');
                return DataTables::of($advanceRequest)
                    ->addColumn('user_name', function ($advanceRequest) {
                        return $advanceRequest->user ? $advanceRequest->user->name : '';
                    })
                    ->filterColumn('user_name', function ($query, $keyword) {
                        $query->whereHas('user', function ($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                // Sanket - Add formatted start_month for employee section to match other date columns
                    ->addColumn('formatted_start_month', function ($advanceRequest) {
                        return $advanceRequest->formatted_start_month;
                    })
                    ->addColumn('formatted_requested_date', function ($advanceRequest) {
                        return $advanceRequest->formatted_requested_date;
                    })
                    ->addColumn('formatted_approved_date', function ($advanceRequest) {
                        return $advanceRequest->formatted_approved_date;
                    })
                    ->addColumn('action_date', function ($advanceRequest) {
                        return $advanceRequest->action_date;
                    })
                    ->addColumn('approval', function ($advanceRequest) {
                        $btn = '';
                        if (hasPermission('Manage Advance Salary Request')) {
                            if ($advanceRequest->user_id != auth()->user()->id) {
                                if ($advanceRequest->status === 'pending') {
                                    $btn .= createActionButton(route('backend.payroll.advance.advancepay-approval', [$advanceRequest->reference_number]), 'Approval', 'btn-success edit-button', '');
                                }
                            }
                        }
                        if ($advanceRequest->status === 'pending') {
                            $btn .= createActionButton(route('backend.payroll.advance.editRequest', [$advanceRequest->reference_number, $advanceRequest->id]), 'Edit', 'btn-warning edit-button', 'datatable');
                        }
                        if ($advanceRequest->status === 'pending') {
                            $btn .= createActionButton(route('backend.payroll.advance.deleteRequest', [$advanceRequest->reference_number, $advanceRequest->id]), 'Delete', 'btn-danger action-button', 'datatable');
                        }
                        $btn .= createActionButton(route('backend.payroll.advance.advancepay-details', [$advanceRequest->reference_number]), '', 'btn-primary edit-button', 'fa fa-info-circle');
                        return $btn;
                    })
                    ->rawColumns(['approval', 'id'])
                    ->make(true);
            }
        }

        return view('payroll::taxes.advancepay');
    }
    //Loan Request
    public function createRequest($userid)
    {
        //$userid = auth()->user()->id;
        //$loan = AdvanceRequest::find($userid);
        $userList = User::query()->notAdmin()->get();

        $html     = view('payroll::taxes.advancepayrequest', compact('userid', 'userList'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];
        return $response;
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'amount'      => 'required|numeric',
            'loan_months' => 'required|integer|min:1',
            'start_month' => 'required|date_format:Y-m',
            'type'        => 'required|in:Loan,Salary Advance',
            'reason'      => 'required|string',
        ]);
        $userId = $request->user_id ? $request->user_id : auth()->user()->id;
        // Calculate installment amount
        $installmentAmount = $validatedData['amount'] / $validatedData['loan_months'];

        // Calculate end month
        //$endMonth = date('Y-m', strtotime($validatedData['start_month'] . " +{$validatedData['loan_span']} months"));

        // Calculate installments pending
        $installmentsPending = $validatedData['loan_months'];
        Log::info($installmentsPending);
        // Create loan request
        try {
            $loanRequest = AdvanceRequest::create([
                'reference_number'     => $this->generateReferenceNumber(),
                'type'                 => $validatedData['type'],
                'reason'               => $validatedData['reason'],
                'amount'               => $validatedData['amount'],
                'instalments'          => $validatedData['loan_months'],
                'start_month'          => Carbon::createFromFormat('Y-m', $validatedData['start_month'])->firstOfMonth()->format('Y-m-d'),
                'status'               => 'pending',
                'approved_amount'      => null,
                'loan_months'          => $validatedData['loan_months'],
                'installment_amount'   => $installmentAmount,
                'installments_paid'    => 0,
                'installments_pending' => $installmentsPending,
                'user_id'              => $userId,
                'requested_date'       => now()->toDateString(), // Sanket - Auto-fill with current date when user submits request
            ]);
            $user     = User::where('id', $userId)->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Generated a Advance Loan/Salary Request.',
                'route'   => route('backend.payroll.advance-request.index'),
                // Add any other user data you want to pass...
            ];

            if ($request->user_id) {
                $user->notify(new GenerateNotification($userData, $user->id));
            } else {
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
                // $hr = User::role('hr')->first();
                $hr = User::whereHas('roles', function ($query) {
                    $query->where('name', 'hr');
                })->first();
                if ($hr) {
                    $hr->notify(new GenerateNotification($userData, $hr->id));
                }
            }

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            return response()->json($response);
        }
        $response = getSuccessResponse(createFlashMessage('Loan Request', 'Received'));
        return response()->json($response);

        // You may want to redirect to a success page or return a response
    }

    public function editRequest($userid, $id)
    {
        $adRequest = AdvanceRequest::find($id);
        $userList  = User::query()->notAdmin()->get();

        $html     = view('payroll::taxes.editAdvancepayrequest', compact('userid', 'userList', 'adRequest'))->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];
        return $response;
    }

    public function updateAdvanceRequest(Request $request, $userid, $id)
    {
        $validatedData = $request->validate([
            'amount'      => 'required|numeric',
            'loan_months' => 'required|integer|min:1',
            'start_month' => 'required|date_format:Y-m',
            'type'        => 'required|in:Loan,Salary Advance',
            'reason'      => 'required|string',
        ]);
        $userId = $request->user_id ? $request->user_id : auth()->user()->id;

        $installmentAmount = $validatedData['amount'] / $validatedData['loan_months'];

        $updateRequest = AdvanceRequest::find($id);

        $installmentsPending = $validatedData['loan_months'];
        Log::info($installmentsPending);
        // Create loan request
        try {
            $updateRequest->update([
                'reference_number'     => $this->generateReferenceNumber(),
                'type'                 => $validatedData['type'],
                'reason'               => $validatedData['reason'],
                'amount'               => $validatedData['amount'],
                'instalments'          => $validatedData['loan_months'],
                'start_month'          => Carbon::createFromFormat('Y-m', $validatedData['start_month'])->firstOfMonth()->format('Y-m-d'),
                'status'               => 'pending',
                'approved_amount'      => null,
                'loan_months'          => $validatedData['loan_months'],
                'installment_amount'   => $installmentAmount,
                'installments_paid'    => 0,
                'installments_pending' => $installmentsPending,
                'user_id'              => $userId,
            ]);
            $user     = User::where('id', $userId)->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Generated a Advance Loan/Salary Request.',
                'route'   => route('backend.payroll.advance-request.index'),
            ];

            if ($request->user_id) {
                $user->notify(new GenerateNotification($userData, $user->id));
            } else {
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
                // $hr = User::role('hr')->first();
                $hr = User::whereHas('roles', function ($query) {
                    $query->where('name', 'hr');
                })->first();
                if ($hr) {
                    $hr->notify(new GenerateNotification($userData, $hr->id));
                }
            }

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            return response()->json($response);
        }
        $response = getSuccessResponse(createFlashMessage('Loan Request', 'Received'));
        return response()->json($response);

        // You may want to redirect to a success page or return a response
    }

    public function approveRequest(Request $request, $loanId)
    {
        //$userid = auth()->user()->id;
        $loan     = AdvanceRequest::where('reference_number', $loanId)->first();
        $html     = view('payroll::taxes.advancepay-approval', ['loan' => $loan])->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];
        return $response;
    }

    // Update approval fields
    public function updateApproval(Request $request, $loanId)
    {
        // Validate the request
        $validatedData = $request->validate([
            'approved_amount'         => 'required|numeric',
            'status'                  => 'required|in:approved,rejected',
            'loan_mode'               => 'required',
            'rejection_reason_choice' => 'nullable|in:yes,no',
            'rejection_reason'        => 'required_if:rejection_reason_choice,yes',
        ]);

        // Find the loan request
        $loanRequest = AdvanceRequest::where('reference_number', $loanId)->first();

        // Recalculate installment amount based on the approved amount
        $installmentAmount = $validatedData['approved_amount'] / $loanRequest->loan_months;

        // Update approval details
        try {
            $updateData = [
                'approved_amount'    => $validatedData['approved_amount'],
                'installment_amount' => $installmentAmount,
                'status'             => $validatedData['status'],
                'loan_mode'          => $validatedData['loan_mode'],
            ];

            // Only set date based on status
            if ($validatedData['status'] === 'approved') {
                $updateData['approved_date']    = now()->toDatestring();
                $updateData['rejected_date']    = null; // Clear any previous rejection
                $updateData['rejection_reason'] = null; // Clear any previous rejection reason
            } elseif ($validatedData['status'] === 'rejected') {
                $updateData['rejected_date']    = now()->toDatestring();
                $updateData['approved_date']    = null; // Clear any previous approval
                $updateData['rejection_reason'] = $validatedData['rejection_reason'] ?? null;
            }

            $loanRequest->update($updateData);
            $user     = User::where('id', $loanRequest->user_id)->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Advance Loan/Salary Request was ' . $validatedData['status'],
                'route'   => route('backend.payroll.advance-request.index'),
            ];
            $user->notify(new GenerateNotification($userData, $user->id));
            $notification = $this->fcmService->sendFcmMessage($user->ftoken, 'Advance Loan/Salary Request Notification', $userData['message'], 20);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            return response()->json($response);
        }
        $response = getSuccessResponse(createFlashMessage('Loan Request', 'Updated'));
        return response()->json($response);

        // You may want to redirect to a success page or return a response
    }

    public function updateRequest(Request $request, $loanId)
    {
        //$userid = auth()->user()->id;
        $loan     = AdvanceRequest::where('reference_number', $loanId)->first();
        $html     = view('payroll::taxes.advancepay-update', ['loan' => $loan])->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];
        return $response;
    }

    public function updateLoan(Request $request, $loanId)
    {
        $validatedData = $request->validate([
            //'status' => 'required|in:hold,cancelled,closed,approved',
        ]);
        // Find the loan request
        $loanRequest = AdvanceRequest::where('reference_number', $loanId)->first();

        // Update installment fields
        try {
            $loanRequest->update([
                'status' => $validatedData['status'],
            ]);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            return response()->json($response);
        }
        $response = getSuccessResponse(createFlashMessage('Loan Request', 'Received'));
        return response()->json($response);
        // You may want to redirect to a success page or return a response
    }

    // Update installment fields
    public function updateInstallments(Request $request, $loanId)
    {
        // Find the loan request
        $loanRequest = AdvanceRequest::where('reference_number', $loanId)->first();

        // Update installment fields
        try {
            $loanRequest->update([
                'installments_paid'    => $loanRequest->installments_paid + 1,
                'installments_pending' => $loanRequest->installments_pending - 1,
            ]);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            return response()->json($response);
        }
        $response = getSuccessResponse(createFlashMessage('Loan Request', 'Received'));
        return response()->json($response);
        // You may want to redirect to a success page or return a response
    }

    public function detailsRequest(Request $request, $loanId)
    {
        //$userid = auth()->user()->id;
        $loan     = AdvanceRequest::where('reference_number', $loanId)->first();
        $html     = view('payroll::taxes.advancepay-details', ['loan' => $loan])->render();
        $response = [
            'success' => true,
            'html'    => $html,
        ];
        return $response;
    }

    public function deleteRequest(Request $request, $loanId)
    {
        $loan     = AdvanceRequest::where('reference_number', $loanId)->delete();
        $response = getSuccessResponse(createFlashMessage('Loan Request delete', 'Received'));
        return response()->json($response);
    }

    // Helper function to generate a unique reference number
    private function generateReferenceNumber()
    {
        // Logic to generate a unique reference number with 8 digits
        return str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    }

    public function listAllLoans()
    {
        $loans = AdvanceRequest::where('type', 'Loan')->get();

        // You may want to return a view or response with the list of loans
        return response()->json($loans);
    }

    // List loans by user_id
    public function listLoansByUserId($userId)
    {
        $userLoans = AdvanceRequest::where('user_id', $userId)
            ->where('type', 'Loan')
            ->get();

        // You may want to return a view or response with the list of loans for the specified user
        return response()->json($userLoans);
    }

    public function advanceRequestReport(Request $request)
    {
        if ($request->ajax()) {
            $query = AdvanceRequestHistory::query()
                ->with('user:id,name,employee_id', 'advanceRequest')
                ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                    $q->whereBetween(
                        \DB::raw('DATE(action_date)'),
                        [
                            Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d'),
                            Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d'),
                        ]
                    );
                })
                ->when($request->user_id, function ($q) use ($request) {
                    $q->whereIn('user_id', (array) $request->user_id);
                })->orderBy('action_date', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('employee', function ($row) {
                    return $row->user ? '<a href="' . route('backend.users.show', $row->user) . '"><span class="badge badge-pill bg-success-light">(' . $row->user->employee_id . ') ' . $row->user->name . '</span></a>' : '';
                })
                ->addColumn('advance_request_id', function ($row) {
                    return $row->advanceRequest->reference_number ?? 'N/A';
                })
                ->addColumn('total_amount', function ($row) {
                    return $row->advanceRequest->amount ?? '0.00';
                })
                ->addColumn('approved_amount', function ($row) {
                    return $row->approved_amount ?? '0.00';
                })
                ->addColumn('installment_amount', function ($row) {
                    return $row->amount ?? 'N/A';
                })
                ->addColumn('loan_duration', function ($row) {
                    return $row->advanceRequest->loan_months ?? 'N/A';
                })
                ->addColumn('total_installment', function ($row) {
                    return $row->advanceRequest->instalments ?? 'N/A';
                })
                ->addColumn('installment_paid', function ($row) {
                    return $row->installments_paid ?? 'N/A';
                })
                ->addColumn('installment_pending', function ($row) {
                    return $row->installments_pending ?? 'N/A';
                })
                ->addColumn('payment_mode', function ($row) {
                    return $row->advanceRequest->loan_mode ?? 'N/A';
                })
                ->editColumn('action_date', function ($row) {
                    return \Carbon\Carbon::parse($row->action_date)->format('d-m-Y');
                })
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 2);
                })
            // filter columns
                ->filterColumn('employee', function ($q, $keyword) {
                    $q->whereHas('user', function ($qq) use ($keyword) {
                        $qq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('employee_id', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('advance_request_id', function ($q, $keyword) {
                    $q->whereHas('advanceRequest', function ($qq) use ($keyword) {
                        $qq->where('reference_number', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('total_amount', function ($q, $keyword) {
                    $q->whereHas('advanceRequest', function ($qq) use ($keyword) {
                        $qq->where('amount', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('approved_amount', function ($q, $keyword) {
                    $q->where('approved_amount', 'like', "%{$keyword}%");
                })
                ->filterColumn('installment_amount', function ($q, $keyword) {
                    $q->where('amount', 'like', "%{$keyword}%");
                })
                ->rawColumns(['employee'])
                ->make(true);
        }

        view()->share('activeLink', 'advance-request-report');
        return view('payroll::taxes.advance_request_report');
    }

    public function pdfExport(Request $request)
    {
        $records = AdvanceRequestHistory::query()
            ->with('user:id,name,employee_id', 'advanceRequest')
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween(
                    \DB::raw('DATE(action_date)'),
                    [
                        Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d'),
                        Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d'),
                    ]
                );
            })
            ->when($request->user_id, function ($q) use ($request) {
                $q->whereIn('user_id', (array) $request->user_id);
            })
            ->get();
        $pdf = Pdf::loadView('payroll::taxes.advance_request_report_pdf', compact('records'))->setPaper('A4', 'landscape');

        return $pdf->download('advance-request-report.pdf');
    }

    public function excelExport(Request $request)
    {
        return Excel::download(new AdvanceRequestReportExport($request), 'advance-request-report.xlsx');
    }
}
