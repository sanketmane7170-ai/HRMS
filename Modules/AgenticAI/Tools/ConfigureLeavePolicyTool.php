<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use App\Models\LeavePolicy;
use Modules\LeavePolicy\Entities\LeavePolicyTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Notifications\Leave\LeavePolicyAssignedNotification;

class ConfigureLeavePolicyTool extends BaseTool
{
    public function name(): string
    {
        return 'configure_leave_policy';
    }

    public function description(): string
    {
        return 'Configure and manage leave policies. Admin-only tool to create policies, list templates, preview templates, or get policy details. HIGHLY SENSITIVE - creates policies affecting multiple employees.';
    }

    public function isSensitive(): bool
    {
        return true; // Requires user approval - highly sensitive operation
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list_templates', 'preview_template', 'get_policy_details', 'list_policies', 'create_policy'],
                    'description' => 'Action: list_templates, preview_template, get_policy_details, list_policies, or create_policy'
                ],
                'template_id' => [
                    'type' => 'integer',
                    'description' => 'Required for preview_template: Template ID to preview'
                ],
                'policy_id' => [
                    'type' => 'integer',
                    'description' => 'Required for get_policy_details: Policy ID to get details for'
                ],
                'country_code' => [
                    'type' => 'string',
                    'description' => 'Optional for list_templates: Filter templates by country code (e.g., AE, IN, US)'
                ],
                'policy_config' => [
                    'type' => 'object',
                    'description' => 'Required for create_policy: Policy configuration object',
                    'properties' => [
                        'policy_name' => ['type' => 'string', 'description' => 'Name of the leave policy'],
                        'leave_type_id' => ['type' => 'integer', 'description' => 'Leave type ID'],
                        'accrual_type' => ['type' => 'string', 'enum' => ['monthly', 'yearly', 'daily', 'upfront'], 'description' => 'Accrual type'],
                        'accrual_rate' => ['type' => 'number', 'description' => 'Days accrued per period'],
                        'max_balance' => ['type' => 'number', 'description' => 'Maximum leave balance'],
                        'carry_forward' => ['type' => 'boolean', 'description' => 'Allow carry forward'],
                        'employee_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Employee IDs to assign']
                    ]
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
                'message' => 'Please specify an action.'
            ];
        }

        try {
            // Permission check - Admin only
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);

            if (!$isAdmin) {
                return [
                    'error' => 'Access denied',
                    'message' => 'Only administrators can configure leave policies.'
                ];
            }

            switch ($action) {
                case 'list_templates':
                    return $this->listTemplates($args['country_code'] ?? null);

                case 'preview_template':
                    $templateId = $args['template_id'] ?? null;
                    if (!$templateId) {
                        return ['error' => 'Missing template ID', 'message' => 'Please specify a template ID.'];
                    }
                    return $this->previewTemplate($templateId);

                case 'list_policies':
                    return $this->listPolicies();

                case 'get_policy_details':
                    $policyId = $args['policy_id'] ?? null;
                    if (!$policyId) {
                        return ['error' => 'Missing policy ID', 'message' => 'Please specify a policy ID.'];
                    }
                    return $this->getPolicyDetails($policyId);

                case 'create_policy':
                    $policyConfig = $args['policy_config'] ?? null;
                    if (!$policyConfig) {
                        return ['error' => 'Missing policy configuration', 'message' => 'Please provide policy configuration.'];
                    }
                    return $this->createPolicy($user, $policyConfig);

                default:
                    return [
                        'error' => 'Invalid action',
                        'message' => 'Action must be one of: list_templates, preview_template, get_policy_details, list_policies, create_policy'
                    ];
            }

        } catch (\Exception $e) {
            \Log::error('ConfigureLeavePolicyTool failed', [
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

    private function listTemplates($countryCode = null)
    {
        try {
            $query = LeavePolicyTemplate::query();

            if ($countryCode) {
                $query->where('country_code', $countryCode);
            }

            $templates = $query->orderBy('country_code')->orderBy('name')->get();

            return [
                'templates' => $templates->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'country_code' => $template->country_code,
                        'country_name' => $template->country_name ?? 'N/A',
                        'leave_type' => $template->leave_type_name ?? 'N/A',
                        'accrual_type' => $template->accrual_type,
                        'accrual_rate' => $template->accrual_rate,
                        'max_balance' => $template->max_balance,
                        'description' => $template->description ?? 'N/A'
                    ];
                })->toArray(),
                'total_templates' => $templates->count()
            ];
        } catch (\Exception $e) {
            // Table might not exist - return helpful message
            return [
                'error' => 'Templates not available',
                'message' => 'Leave policy templates are not configured in this system. You can still create policies manually or contact your administrator to set up templates.',
                'templates' => [],
                'total_templates' => 0
            ];
        }
    }

    private function previewTemplate($templateId)
    {
        $template = LeavePolicyTemplate::find($templateId);

        if (!$template) {
            return [
                'error' => 'Template not found',
                'message' => "Template ID {$templateId} not found."
            ];
        }

        return [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'country_code' => $template->country_code,
                'country_name' => $template->country_name ?? 'N/A',
                'leave_type' => $template->leave_type_name ?? 'N/A',
                'leave_type_id' => $template->leave_type_id,
                'accrual_type' => $template->accrual_type,
                'accrual_rate' => $template->accrual_rate,
                'max_balance' => $template->max_balance,
                'carry_forward' => $template->carry_forward ?? false,
                'prorate_accrual' => $template->prorate_accrual ?? false,
                'description' => $template->description ?? 'N/A',
                'rules' => $template->rules ?? []
            ]
        ];
    }

    private function listPolicies()
    {
        $policies = LeavePolicy::with(['leaveType', 'users'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'policies' => $policies->map(function ($policy) {
                return [
                    'id' => $policy->id,
                    'name' => $policy->name,
                    'leave_type' => $policy->leaveType->name ?? 'N/A',
                    'accrual_type' => $policy->accrual_type ?? 'N/A',
                    'accrual_rate' => $policy->accrual_rate ?? 0,
                    'max_balance' => $policy->max_balance ?? 0,
                    'assigned_employees' => $policy->users->count(),
                    'status' => $policy->status ?? 'active',
                    'created_at' => $policy->created_at->format('Y-m-d')
                ];
            })->toArray(),
            'total_policies' => $policies->count()
        ];
    }

    private function getPolicyDetails($policyId)
    {
        $policy = LeavePolicy::with(['leaveType', 'users.department'])->find($policyId);

        if (!$policy) {
            return [
                'error' => 'Policy not found',
                'message' => "Policy ID {$policyId} not found."
            ];
        }

        return [
            'policy' => [
                'id' => $policy->id,
                'name' => $policy->name,
                'leave_type' => $policy->leaveType->name ?? 'N/A',
                'accrual_type' => $policy->accrual_type ?? 'N/A',
                'accrual_rate' => $policy->accrual_rate ?? 0,
                'max_balance' => $policy->max_balance ?? 0,
                'carry_forward' => $policy->carry_forward ?? false,
                'prorate_accrual' => $policy->prorate_accrual ?? false,
                'status' => $policy->status ?? 'active',
                'created_at' => $policy->created_at->format('Y-m-d'),
                'assigned_employees' => $policy->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'department' => $user->department->name ?? 'N/A'
                    ];
                })->toArray(),
                'total_assigned' => $policy->users->count()
            ]
        ];
    }

    private function createPolicy($user, $policyConfig)
    {
        // Validate required fields
        $requiredFields = ['policy_name', 'leave_type_id', 'accrual_type', 'accrual_rate', 'max_balance', 'employee_ids'];
        foreach ($requiredFields as $field) {
            if (!isset($policyConfig[$field])) {
                return [
                    'error' => 'Missing required field',
                    'message' => "The field '{$field}' is required for policy creation."
                ];
            }
        }

        DB::beginTransaction();
        try {
            // Create the policy
            $policy = LeavePolicy::create([
                'name' => $policyConfig['policy_name'],
                'leave_type_id' => $policyConfig['leave_type_id'],
                'accrual_type' => $policyConfig['accrual_type'],
                'accrual_rate' => $policyConfig['accrual_rate'],
                'max_balance' => $policyConfig['max_balance'],
                'carry_forward' => $policyConfig['carry_forward'] ?? false,
                'prorate_accrual' => $policyConfig['prorate_accrual'] ?? true,
                'status' => 'active',
                'created_by' => $user->id
            ]);

            // Assign employees to the policy
            $employeeIds = $policyConfig['employee_ids'];
            $policy->users()->attach($employeeIds);

            // Send notifications to assigned employees
            $assignedCount = 0;
            foreach ($employeeIds as $employeeId) {
                try {
                    $employee = User::find($employeeId);
                    if ($employee) {
                        $employee->notify(new LeavePolicyAssignedNotification($policy));
                        $assignedCount++;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to notify employee about policy assignment', [
                        'employee_id' => $employeeId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Leave policy '{$policyConfig['policy_name']}' created successfully and assigned to {$assignedCount} employees.",
                'policy' => [
                    'id' => $policy->id,
                    'name' => $policy->name,
                    'leave_type_id' => $policy->leave_type_id,
                    'accrual_type' => $policy->accrual_type,
                    'accrual_rate' => $policy->accrual_rate,
                    'max_balance' => $policy->max_balance,
                    'assigned_employees' => count($employeeIds),
                    'notifications_sent' => $assignedCount
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Failed to create leave policy', [
                'error' => $e->getMessage(),
                'policy_config' => $policyConfig
            ]);

            return [
                'error' => 'Policy creation failed',
                'message' => 'Unable to create leave policy. Error: ' . $e->getMessage()
            ];
        }
    }
}
