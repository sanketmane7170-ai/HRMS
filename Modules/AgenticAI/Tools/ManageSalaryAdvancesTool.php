<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Payroll\Entities\AdvanceRequest;
use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;

class ManageSalaryAdvancesTool extends BaseTool
{
    protected $fcmService;

    public function __construct()
    {
        $this->fcmService = app(FirebaseService::class);
    }

    public function name(): string
    {
        return 'manage_salary_advances';
    }

    public function description(): string
    {
        return 'Manage salary advance requests. Employees can request advances and view their requests. Managers/Admins can view, approve, or reject advance requests.';
    }

    public function isSensitive(): bool
    {
        return true; // Requires approval for create/approve/reject actions
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list_my_requests', 'list_all_requests', 'request_advance', 'approve', 'reject'],
                    'description' => 'Action to perform: list_my_requests, list_all_requests (admin), request_advance, approve, or reject'
                ],
                'advance_id' => [
                    'type' => 'integer',
                    'description' => 'Required for approve/reject: ID of the advance request'
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Required for request_advance: Amount of advance requested'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Optional: Reason for advance request or approval/rejection comment'
                ],
                'installments' => [
                    'type' => 'integer',
                    'description' => 'Optional for request_advance: Number of monthly installments for repayment (default: 3)'
                ],
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Optional for list_all_requests: Filter by specific employee ID (admin only)'
                ]
            ],
            'required' => ['action']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $action = $args['action'] ?? null;

        if (!$action) {
            return [
                'error' => 'Missing action',
                'message' => 'Please specify an action: list_my_requests, list_all_requests, request_advance, approve, or reject'
            ];
        }

        try {
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);
            $isManager = in_array('manager', $roles);

            switch ($action) {
                case 'list_my_requests':
                    return $this->listMyRequests($user);

                case 'list_all_requests':
                    if (!$isAdmin && !$isManager) {
                        return [
                            'error' => 'Access denied',
                            'message' => 'Only administrators and managers can view all requests.'
                        ];
                    }
                    return $this->listAllRequests($user, $args['user_id'] ?? null);

                case 'request_advance':
                    return $this->requestAdvance($user, $args);

                case 'approve':
                    if (!$isAdmin && !$isManager) {
                        return [
                            'error' => 'Access denied',
                            'message' => 'Only administrators and managers can approve requests.'
                        ];
                    }
                    return $this->approveRequest($user, $args);

                case 'reject':
                    if (!$isAdmin && !$isManager) {
                        return [
                            'error' => 'Access denied',
                            'message' => 'Only administrators and managers can reject requests.'
                        ];
                    }
                    return $this->rejectRequest($user, $args);

                default:
                    return [
                        'error' => 'Invalid action',
                        'message' => 'Action must be one of: list_my_requests, list_all_requests, request_advance, approve, reject'
                    ];
            }

        } catch (\Exception $e) {
            \Log::error('ManageSalaryAdvancesTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'action' => $action
            ]);

            return [
                'error' => 'Operation failed',
                'message' => 'Unable to complete the operation. Error: ' . $e->getMessage()
            ];
        }
    }

    private function listMyRequests($user)
    {
        $requests = AdvanceRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'my_requests' => $requests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'reference_number' => $req->reference_number,
                    'amount' => $req->amount,
                    'reason' => $req->reason,
                    'installments' => $req->installments,
                    'status' => $req->status,
                    'requested_date' => $req->created_at->format('Y-m-d'),
                    'approved_by' => $req->approved_by_name ?? null,
                    'approval_date' => $req->approval_date ? \Carbon\Carbon::parse($req->approval_date)->format('Y-m-d') : null,
                    'comments' => $req->comments ?? null
                ];
            })->toArray(),
            'total_requests' => $requests->count()
        ];
    }

    private function listAllRequests($user, $userId = null)
    {
        $query = AdvanceRequest::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $requests = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'all_requests' => $requests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'reference_number' => $req->reference_number,
                    'employee_id' => $req->user_id,
                    'employee_name' => $req->user->name ?? 'Unknown',
                    'amount' => $req->amount,
                    'reason' => $req->reason,
                    'installments' => $req->installments,
                    'status' => $req->status,
                    'requested_date' => $req->created_at->format('Y-m-d'),
                    'approved_by' => $req->approved_by_name ?? null,
                    'approval_date' => $req->approval_date ? \Carbon\Carbon::parse($req->approval_date)->format('Y-m-d') : null,
                    'comments' => $req->comments ?? null
                ];
            })->toArray(),
            'total_requests' => $requests->count(),
            'pending_count' => $requests->where('status', 'pending')->count()
        ];
    }

    private function requestAdvance($user, $args)
    {
        $amount = $args['amount'] ?? null;
        $reason = $args['reason'] ?? '';
        $installments = $args['installments'] ?? 3;

        if (!$amount) {
            return [
                'error' => 'Missing amount',
                'message' => 'Please specify the advance amount.'
            ];
        }

        // Generate unique reference number
        $referenceNumber = 'ADV-' . strtoupper(uniqid());

        $advance = AdvanceRequest::create([
            'user_id' => $user->id,
            'reference_number' => $referenceNumber,
            'amount' => $amount,
            'reason' => $reason,
            'installments' => $installments,
            'status' => 'pending',
            'requested_date' => now()
        ]);

        // Send notification to admin/manager
        try {
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new GenerateNotification(
                    'New Salary Advance Request',
                    "{$user->name} has requested a salary advance of {$amount}",
                    route('backend.payroll.advance-request.index')
                ));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send notification for advance request', ['error' => $e->getMessage()]);
        }

        return [
            'success' => true,
            'message' => "Salary advance request created successfully. Reference: {$referenceNumber}",
            'advance' => [
                'id' => $advance->id,
                'reference_number' => $referenceNumber,
                'amount' => $amount,
                'installments' => $installments,
                'status' => 'pending'
            ]
        ];
    }

    private function approveRequest($user, $args)
    {
        $advanceId = $args['advance_id'] ?? null;
        $comment = $args['reason'] ?? '';

        if (!$advanceId) {
            return [
                'error' => 'Missing advance ID',
                'message' => 'Please specify the advance request ID to approve.'
            ];
        }

        $advance = AdvanceRequest::find($advanceId);

        if (!$advance) {
            return [
                'error' => 'Request not found',
                'message' => "Advance request ID {$advanceId} not found."
            ];
        }

        if ($advance->status !== 'pending') {
            return [
                'error' => 'Invalid status',
                'message' => "This request has already been {$advance->status}."
            ];
        }

        $advance->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_by_name' => $user->name,
            'approval_date' => now(),
            'comments' => $comment
        ]);

        // Notify employee
        try {
            $employee = User::find($advance->user_id);
            if ($employee) {
                $employee->notify(new GenerateNotification(
                    'Salary Advance Approved',
                    "Your salary advance request of {$advance->amount} has been approved by {$user->name}",
                    route('backend.payroll.advance-request.index')
                ));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send approval notification', ['error' => $e->getMessage()]);
        }

        return [
            'success' => true,
            'message' => "Advance request #{$advanceId} approved successfully.",
            'advance' => [
                'id' => $advance->id,
                'reference_number' => $advance->reference_number,
                'employee_name' => $advance->user->name ?? 'Unknown',
                'amount' => $advance->amount,
                'status' => 'approved',
                'approved_by' => $user->name
            ]
        ];
    }

    private function rejectRequest($user, $args)
    {
        $advanceId = $args['advance_id'] ?? null;
        $comment = $args['reason'] ?? '';

        if (!$advanceId) {
            return [
                'error' => 'Missing advance ID',
                'message' => 'Please specify the advance request ID to reject.'
            ];
        }

        $advance = AdvanceRequest::find($advanceId);

        if (!$advance) {
            return [
                'error' => 'Request not found',
                'message' => "Advance request ID {$advanceId} not found."
            ];
        }

        if ($advance->status !== 'pending') {
            return [
                'error' => 'Invalid status',
                'message' => "This request has already been {$advance->status}."
            ];
        }

        $advance->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_by_name' => $user->name,
            'approval_date' => now(),
            'comments' => $comment
        ]);

        // Notify employee
        try {
            $employee = User::find($advance->user_id);
            if ($employee) {
                $employee->notify(new GenerateNotification(
                    'Salary Advance Rejected',
                    "Your salary advance request of {$advance->amount} has been rejected by {$user->name}",
                    route('backend.payroll.advance-request.index')
                ));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send rejection notification', ['error' => $e->getMessage()]);
        }

        return [
            'success' => true,
            'message' => "Advance request #{$advanceId} rejected.",
            'advance' => [
                'id' => $advance->id,
                'reference_number' => $advance->reference_number,
                'employee_name' => $advance->user->name ?? 'Unknown',
                'amount' => $advance->amount,
                'status' => 'rejected',
                'rejected_by' => $user->name
            ]
        ];
    }
}
