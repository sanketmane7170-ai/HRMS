<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SearchEmployeesTool extends BaseTool
{
    public function name(): string
    {
        return 'search_employees';
    }

    public function description(): string
    {
        return 'Search for employees by name, email, department, or designation. Returns details including joining date, employee ID, department, and phone. Use this tool when the user asks for ANY details about an employee, including "when did they join?", "what is their email?", or "who is X?".';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Name, email, department, or designation to search for. Leave empty to list all employees.'
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of results (default 10, max 100).',
                    'default' => 10
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $query = trim($args['query'] ?? '');
        
        // Author: Sanket - Handle natural language "list all" requests
        $listAllPhrases = ['all employees', 'employee list', 'list all', 'every employee', 'all staff', 'list of employees'];
        foreach ($listAllPhrases as $phrase) {
            if (stripos($query, $phrase) !== false && strlen($query) < (strlen($phrase) + 5)) {
                $query = ''; // Treat as "list all"
                break;
            }
        }

        // Reduce limit to prevent "Empty Response" / timeouts on large datasets
        // Default to 10, max cap at 20 for chat display.
        $limit = min($args['limit'] ?? 10, 20); 

        try {
            $employeesQuery = User::query()->where('status', 'active');
            
            // If query is provided, filter by it
            if (!empty($query)) {
                $employeesQuery->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                });
            }
            
            // Get total count before limiting
            $totalCount = $employeesQuery->count();
            
            $employees = $employeesQuery
                ->with(['workDetail', 'department', 'designation'])
                ->limit($limit)
                ->get();
                
            if ($employees->isEmpty()) {
                return [
                    'message' => empty($query) 
                        ? "No active employees found." 
                        : "No employees found matching '{$query}'.",
                    'employees' => []
                ];
            }
            
            $result = [
                'employees' => $employees->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'email' => $employee->email,
                        'phone' => $employee->phone ?? 'N/A',
                        'employee_id' => $employee->employee_id ?? 'N/A',
                        'department' => $employee->department->name ?? 'N/A',
                        'designation' => $employee->designation->name ?? 'N/A',
                        'joining_date' => $employee->workDetail && $employee->workDetail->joining_date 
                            ? \Carbon\Carbon::parse($employee->workDetail->joining_date)->format('Y-m-d') 
                            : 'N/A',
                        'status' => $employee->status
                    ];
                })->toArray(),
                'count' => $employees->count(),
                'total_available' => $totalCount
            ];

            // Add guidance if truncated
            if ($totalCount > $limit) {
                $result['system_note'] = "Displaying top {$limit} of {$totalCount} results. To view the full list, please use the 'generate_document' tool to create a PDF or Excel report.";
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error('SearchEmployeesTool failed', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            return [
                'error' => 'Search failed',
                'message' => 'Unable to search employees. Please try again.'
            ];
        }
    }
}
