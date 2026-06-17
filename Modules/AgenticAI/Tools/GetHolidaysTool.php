<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetHolidaysTool extends BaseTool
{
    public function name(): string
    {
        return 'get_holidays';
    }

    public function description(): string
    {
        return 'Get company holidays and public holidays. Use when user asks about holidays, public holidays, or days off.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'year' => [
                    'type' => 'integer',
                    'description' => 'Year to get holidays for (default current year)',
                    'default' => date('Y')
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $year = $args['year'] ?? date('Y');
        
        try {
            $holidays = DB::table('holidays')
                ->whereYear('start_date', $year)
                ->orderBy('start_date')
                ->get();
                
            if ($holidays->isEmpty()) {
                return [
                    'message' => "No holidays found for year {$year}.",
                    'holidays' => []
                ];
            }
            
            return [
                'holidays' => $holidays->map(function($holiday) {
                    return [
                        'name' => $holiday->detail ?? $holiday->name ?? 'Holiday',
                        'date' => date('M d, Y (D)', strtotime($holiday->start_date)),
                        'type' => $holiday->is_recurring ? 'Recurring Holiday' : 'Holiday'
                    ];
                })->toArray(),
                'count' => $holidays->count(),
                'year' => $year
            ];
        } catch (\Exception $e) {
            \Log::error('GetHolidaysTool failed', [
                'error' => $e->getMessage(),
                'year' => $year
            ]);
            
            return [
                'error' => 'Failed to fetch holidays',
                'message' => 'Unable to retrieve holidays. Error: ' . $e->getMessage()
            ];
        }
    }
}
