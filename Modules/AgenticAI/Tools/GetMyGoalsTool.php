<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Performance\Entities\PerformanceAppraisal;
use Illuminate\Support\Facades\Auth;

class GetMyGoalsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_goals';
    }

    public function description(): string
    {
        return 'Get my current performance goals, KPIs, and appraisal status.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[], 
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (!$user) {
            return ['error' => 'User not logged in.'];
        }

        try {
            $appraisal = PerformanceAppraisal::where('employee_id', $user->id)
                ->with('criteria')
                ->latest()
                ->first();

            if (!$appraisal) {
                return [
                    'message' => 'No active performance appraisal found.',
                    'status' => 'No Data'
                ];
            }

            return [
                'appraisal' => [
                    'id' => $appraisal->id,
                    'period' => $appraisal->period,
                    'date' => $appraisal->appraisal_date,
                    'status' => $appraisal->status,
                    'comments' => $appraisal->employee_comments,
                    'kpis' => $appraisal->criteria->map(function($c) {
                        return [
                            'name' => $c->title ?? 'KPI',
                            'rating' => $c->rating,
                            'comment' => $c->comment
                        ];
                    })
                ]
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to fetch performance data',
                'message' => $e->getMessage()
            ];
        }
    }
}
