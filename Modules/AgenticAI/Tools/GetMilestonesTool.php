<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;

class GetMilestonesTool extends BaseTool
{
    public function name(): string
    {
        return 'get_milestones';
    }

    public function description(): string
    {
        return 'Get upcoming HR milestones including employee birthdays, work anniversaries, and probation endings. Use when user asks about upcoming events, celebrations, or probation periods.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'milestone_type' => [
                    'type' => 'string',
                    'enum' => ['birthday', 'anniversary', 'probation', 'all'],
                    'description' => 'Type of milestone to fetch. Options: birthday, anniversary, probation, or all (default)'
                ],
                'days_ahead' => [
                    'type' => 'integer',
                    'description' => 'Number of days to look ahead. Default: 60 for birthdays/anniversaries, 40 for probation'
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Filter by specific department ID'
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $milestoneType = $args['milestone_type'] ?? 'all';
        $daysAhead = $args['days_ahead'] ?? null;
        $departmentId = $args['department_id'] ?? null;

        try {
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);
            $isManager = in_array('manager', $roles);

            $result = [];

            // Birthdays - accessible to all users
            if ($milestoneType === 'birthday' || $milestoneType === 'all') {
                $birthdayDays = $daysAhead ?? 60;
                $birthdayQuery = getUpcomingBirthdayQuery($departmentId);
                $birthdays = $birthdayQuery->with('department')->get();

                $result['birthdays'] = $birthdays->map(function ($user) {
                    return [
                        'employee_id' => $user->id,
                        'employee_name' => $user->name,
                        'department' => $user->department->name ?? 'N/A',
                        'date_of_birth' => $user->profile->date_of_birth->format('Y-m-d'),
                        'upcoming_date' => $user->profile->date_of_birth->format('M d'),
                        'days_until' => now()->diffInDays($user->profile->date_of_birth->setYear(now()->year)),
                        'type' => 'birthday'
                    ];
                })->values()->toArray();
            }

            // Work Anniversaries - accessible to all users
            if ($milestoneType === 'anniversary' || $milestoneType === 'all') {
                $anniversaryDays = $daysAhead ?? 60;
                $anniversaryQuery = getUpcomingWorkAnniversariesQuery($departmentId);
                $anniversaries = $anniversaryQuery->with('department')->get();

                $result['anniversaries'] = $anniversaries->map(function ($user) {
                    $joiningDate = $user->workDetail->joining_date;
                    $yearsOfService = now()->diffInYears($joiningDate);
                    
                    return [
                        'employee_id' => $user->id,
                        'employee_name' => $user->name,
                        'department' => $user->department->name ?? 'N/A',
                        'joining_date' => $joiningDate->format('Y-m-d'),
                        'upcoming_date' => $joiningDate->format('M d'),
                        'years_of_service' => $yearsOfService + 1, // Next anniversary
                        'days_until' => now()->diffInDays($joiningDate->setYear(now()->year)),
                        'type' => 'anniversary'
                    ];
                })->values()->toArray();
            }

            // Probation Endings - admin/manager only
            if ($milestoneType === 'probation' || $milestoneType === 'all') {
                if (!$isAdmin && !$isManager) {
                    $result['probation'] = [
                        'error' => 'Access denied',
                        'message' => 'Only admins and managers can view probation information.'
                    ];
                } else {
                    $probationDays = $daysAhead ?? 40;
                    $probationQuery = getProbationEndQuery($probationDays);
                    $probations = $probationQuery->with('department')->get();

                    $result['probation_endings'] = $probations->map(function ($user) {
                        return [
                            'employee_id' => $user->id,
                            'employee_name' => $user->name,
                            'department' => $user->department->name ?? 'N/A',
                            'probation_end_date' => $user->workDetail->probation_end_date->format('Y-m-d'),
                            'days_until' => now()->diffInDays($user->workDetail->probation_end_date),
                            'type' => 'probation_ending'
                        ];
                    })->values()->toArray();
                }
            }

            // Add summary
            $result['summary'] = [
                'total_birthdays' => count($result['birthdays'] ?? []),
                'total_anniversaries' => count($result['anniversaries'] ?? []),
                'total_probation_endings' => count($result['probation_endings'] ?? []),
                'period' => $milestoneType === 'probation' ? ($daysAhead ?? 40) . ' days' : ($daysAhead ?? 60) . ' days'
            ];

            return $result;

        } catch (\Exception $e) {
            \Log::error('GetMilestonesTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'milestone_type' => $milestoneType
            ]);

            return [
                'error' => 'Failed to fetch milestones',
                'message' => 'Unable to retrieve milestone information. Error: ' . $e->getMessage()
            ];
        }
    }
}
