<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Apparel\Entities\ApparelRequest;
use App\Models\EMPAirTicket;
use Modules\GeneralRequest\Entities\GeneralRequest;
use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;

class ProcessAdminRequestTool extends BaseTool
{
    public function name(): string
    {
        return 'process_admin_request';
    }

    public function description(): string
    {
        return 'Process administrative requests across multiple modules (Apparel, Air Ticket, General Request). Admins and managers can approve, reject, or list pending requests.';
    }

    public function isSensitive(): bool
    {
        return true; // Requires approval for approve/reject actions
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'module' => [
                    'type' => 'string',
                    'enum' => ['apparel', 'air_ticket', 'general_request'],
                    'description' => 'Module to process: apparel, air_ticket, or general_request'
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['list_pending', 'approve', 'reject'],
                    'description' => 'Action to perform: list_pending, approve, or reject'
                ],
                'request_id' => [
                    'type' => 'integer',
                    'description' => 'Required for approve/reject: ID of the request'
                ],
                'comment' => [
                    'type' => 'string',
                    'description' => 'Optional: Comment for approval or rejection'
                ]
            ],
            'required' => ['module', 'action']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $module = $args['module'] ?? null;
        $action = $args['action'] ?? null;

        if (!$module || !$action) {
            return [
                'error' => 'Missing parameters',
                'message' => 'Please specify both module and action.'
            ];
        }

        try {
            // Permission check
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);
            $isManager = in_array('manager', $roles);

            if (!$isAdmin && !$isManager) {
                return [
                    'error' => 'Access denied',
                    'message' => 'Only administrators and managers can process requests.'
                ];
            }

            switch ($module) {
                case 'apparel':
                    return $this->processApparelRequest($user, $action, $args);
                
                case 'air_ticket':
                    return $this->processAirTicketRequest($user, $action, $args);
                
                case 'general_request':
                    return $this->processGeneralRequest($user, $action, $args);
                
                default:
                    return [
                        'error' => 'Invalid module',
                        'message' => 'Module must be one of: apparel, air_ticket, general_request'
                    ];
            }

        } catch (\Exception $e) {
            \Log::error('ProcessAdminRequestTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'module' => $module,
                'action' => $action
            ]);

            return [
                'error' => 'Operation failed',
                'message' => 'Unable to process request. Error: ' . $e->getMessage()
            ];
        }
    }

    private function processApparelRequest($user, $action, $args)
    {
        if ($action === 'list_pending') {
            $requests = ApparelRequest::where('status', 'pending')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'pending_apparel_requests' => $requests->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'employee_id' => $req->user_id,
                        'employee_name' => $req->user->name ?? 'Unknown',
                        'item_type' => $req->item_type ?? 'N/A',
                        'size' => $req->size ?? 'N/A',
                        'quantity' => $req->quantity ?? 1,
                        'requested_date' => $req->created_at->format('Y-m-d'),
                        'status' => $req->status
                    ];
                })->toArray(),
                'total_pending' => $requests->count()
            ];
        }

        $requestId = $args['request_id'] ?? null;
        $comment = $args['comment'] ?? '';

        if (!$requestId) {
            return ['error' => 'Missing request ID', 'message' => 'Please specify the request ID.'];
        }

        $request = ApparelRequest::find($requestId);
        if (!$request) {
            return ['error' => 'Request not found', 'message' => "Apparel request ID {$requestId} not found."];
        }

        if ($request->status !== 'pending') {
            return ['error' => 'Invalid status', 'message' => "This request has already been {$request->status}."];
        }

        $newStatus = $action === 'approve' ? 'approved' : 'rejected';
        $request->update([
            'status' => $newStatus,
            'approved_by' => $user->id,
            'approval_date' => now(),
            'comments' => $comment
        ]);

        // Notify employee
        try {
            $employee = User::find($request->user_id);
            if ($employee) {
                $employee->notify(new GenerateNotification(
                    "Apparel Request {$newStatus}",
                    "Your apparel request has been {$newStatus} by {$user->name}",
                    route('backend.apparel.index')
                ));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send apparel notification', ['error' => $e->getMessage()]);
        }

        return [
            'success' => true,
            'message' => "Apparel request #{$requestId} {$newStatus} successfully.",
            'request' => [
                'id' => $request->id,
                'employee_name' => $request->user->name ?? 'Unknown',
                'status' => $newStatus
            ]
        ];
    }

    private function processAirTicketRequest($user, $action, $args)
    {
        if ($action === 'list_pending') {
            $requests = EMPAirTicket::where('status', 'pending')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'pending_air_ticket_requests' => $requests->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'employee_id' => $req->user_id,
                        'employee_name' => $req->user->name ?? 'Unknown',
                        'destination' => $req->destination ?? 'N/A',
                        'travel_date' => $req->date ? \Carbon\Carbon::parse($req->date)->format('Y-m-d') : 'N/A',
                        'amount' => $req->amount ?? 0,
                        'requested_date' => $req->created_at->format('Y-m-d'),
                        'status' => $req->status
                    ];
                })->toArray(),
                'total_pending' => $requests->count()
            ];
        }

        $requestId = $args['request_id'] ?? null;
        $comment = $args['comment'] ?? '';

        if (!$requestId) {
            return ['error' => 'Missing request ID', 'message' => 'Please specify the request ID.'];
        }

        $request = EMPAirTicket::find($requestId);
        if (!$request) {
            return ['error' => 'Request not found', 'message' => "Air ticket request ID {$requestId} not found."];
        }

        if ($request->status !== 'pending') {
            return ['error' => 'Invalid status', 'message' => "This request has already been {$request->status}."];
        }

        $newStatus = $action === 'approve' ? 'approved' : 'rejected';
        $request->update([
            'status' => $newStatus,
            'approved_by' => $user->id,
            'approval_date' => now(),
            'comments' => $comment
        ]);

        // Notify employee
        try {
            $employee = User::find($request->user_id);
            if ($employee) {
                $employee->notify(new GenerateNotification(
                    "Air Ticket Request {$newStatus}",
                    "Your air ticket request has been {$newStatus} by {$user->name}",
                    route('backend.air-ticket.index')
                ));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send air ticket notification', ['error' => $e->getMessage()]);
        }

        return [
            'success' => true,
            'message' => "Air ticket request #{$requestId} {$newStatus} successfully.",
            'request' => [
                'id' => $request->id,
                'employee_name' => $request->user->name ?? 'Unknown',
                'status' => $newStatus
            ]
        ];
    }

    private function processGeneralRequest($user, $action, $args)
    {
        if ($action === 'list_pending') {
            $requests = GeneralRequest::where('status', 'pending')
                ->with(['user', 'type'])
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'pending_general_requests' => $requests->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'employee_id' => $req->user_id,
                        'employee_name' => $req->user->name ?? 'Unknown',
                        'request_type' => $req->type->name ?? 'N/A',
                        'subject' => $req->subject ?? 'N/A',
                        'description' => $req->description ?? 'N/A',
                        'requested_date' => $req->created_at->format('Y-m-d'),
                        'status' => $req->status
                    ];
                })->toArray(),
                'total_pending' => $requests->count()
            ];
        }

        $requestId = $args['request_id'] ?? null;
        $comment = $args['comment'] ?? '';

        if (!$requestId) {
            return ['error' => 'Missing request ID', 'message' => 'Please specify the request ID.'];
        }

        $request = GeneralRequest::find($requestId);
        if (!$request) {
            return ['error' => 'Request not found', 'message' => "General request ID {$requestId} not found."];
        }

        if ($request->status !== 'pending') {
            return ['error' => 'Invalid status', 'message' => "This request has already been {$request->status}."];
        }

        $newStatus = $action === 'approve' ? 'approved' : 'rejected';
        $request->update([
            'status' => $newStatus,
            'approved_by' => $user->id,
            'approval_date' => now(),
            'admin_comments' => $comment
        ]);

        // Notify employee
        try {
            $employee = User::find($request->user_id);
            if ($employee) {
                $employee->notify(new GenerateNotification(
                    "General Request {$newStatus}",
                    "Your general request has been {$newStatus} by {$user->name}",
                    route('backend.general-request.index')
                ));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send general request notification', ['error' => $e->getMessage()]);
        }

        return [
            'success' => true,
            'message' => "General request #{$requestId} {$newStatus} successfully.",
            'request' => [
                'id' => $request->id,
                'employee_name' => $request->user->name ?? 'Unknown',
                'status' => $newStatus
            ]
        ];
    }
}
