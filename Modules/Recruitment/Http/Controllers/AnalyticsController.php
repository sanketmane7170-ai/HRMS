<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Recruitment\AnalyticsService;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        // Enforce permission globally (Sanket - REC-SEC-023)
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole(['admin', 'hr']) && !auth()->user()->can('View Analytics')) {
                abort(403, 'Unauthorized access to analytics');
            }
            return $next($request);
        });
        
        $this->analyticsService = $analyticsService;
        view()->share('activeLink', 'recruitment-analytics');
    }

    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        $filters = $this->getFilters($request);
        $metrics = $this->analyticsService->getDashboardMetrics($filters);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        }

        return view('recruitment::analytics.dashboard', compact('metrics'));
    }

    /**
     * Get recruitment funnel data
     */
    public function getFunnelContent(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request);
        $funnelData = $this->analyticsService->getRecruitmentFunnel($filters);
        
        return response()->json([
            'success' => true,
            'data' => $funnelData
        ]);
    }

    /**
     * Display funnel analysis page
     */
    public function funnel(Request $request)
    {
        $filters = $this->getFilters($request);
        $metrics = [
            'funnel' => $this->analyticsService->getRecruitmentFunnel($filters)
        ];

        return view('recruitment::analytics.funnel', compact('metrics'));
    }

    /**
     * Get time to hire metrics
     */
    public function getTimeToHire(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request);
        $metrics = $this->analyticsService->getTimeToHireMetrics($filters);
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get source effectiveness data
     */
    public function getSourceEffectiveness(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request);
        $effectiveness = $this->analyticsService->getSourceEffectiveness($filters);
        
        return response()->json([
            'success' => true,
            'data' => $effectiveness
        ]);
    }

    /**
     * Get cost per hire metrics
     */
    public function getCostPerHire(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request);
        $costs = $this->analyticsService->getCostPerHire($filters);
        
        return response()->json([
            'success' => true,
            'data' => $costs
        ]);
    }

    /**
     * Get interview performance metrics
     */
    public function getInterviewMetrics(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request);
        $metrics = $this->analyticsService->getInterviewMetrics($filters);
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get diversity metrics
     */
    public function getDiversityMetrics(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request);
        $metrics = $this->analyticsService->getDiversityMetrics($filters);
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Export analytics report
     */
    public function exportReport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:pdf,excel,csv',
            'report_type' => 'required|in:dashboard,funnel,time_to_hire,source_effectiveness,cost_analysis,interview_metrics,diversity'
        ]);

        $filters = $this->getFilters($request);
        $format = $request->input('format');
        $reportType = $request->input('report_type');

        try {
            $data = match($reportType) {
                'dashboard' => $this->analyticsService->getDashboardMetrics($filters),
                'funnel' => $this->analyticsService->getRecruitmentFunnel($filters),
                'time_to_hire' => $this->analyticsService->getTimeToHireMetrics($filters),
                'source_effectiveness' => $this->analyticsService->getSourceEffectiveness($filters),
                'cost_analysis' => $this->analyticsService->getCostPerHire($filters),
                'interview_metrics' => $this->analyticsService->getInterviewMetrics($filters),
                'diversity' => $this->analyticsService->getDiversityMetrics($filters),
                default => throw new \InvalidArgumentException('Invalid report type'),
            };

            $filename = "recruitment_{$reportType}_" . now()->format('Y-m-d_H-i-s');

            switch ($format) {
                case 'pdf':
                    return $this->exportToPdf($data, $filename, $reportType);
                case 'excel':
                    return $this->exportToExcel($data, $filename, $reportType);
                case 'csv':
                    return $this->exportToCsv($data, $filename, $reportType);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comparison data between periods
     */
    public function getComparison(Request $request): JsonResponse
    {
        $request->validate([
            'current_start' => 'required|date',
            'current_end' => 'required|date',
            'previous_start' => 'required|date', 
            'previous_end' => 'required|date',
            'metric' => 'required|in:funnel,time_to_hire,source_effectiveness,interview_metrics'
        ]);

        $currentFilters = [
            'date_from' => $request->input('current_start'),
            'date_to' => $request->input('current_end')
        ];

        $previousFilters = [
            'date_from' => $request->input('previous_start'),
            'date_to' => $request->input('previous_end')
        ];

        $metric = $request->input('metric');

        $currentData = match($metric) {
            'funnel' => $this->analyticsService->getRecruitmentFunnel($currentFilters),
            'time_to_hire' => $this->analyticsService->getTimeToHireMetrics($currentFilters),
            'source_effectiveness' => $this->analyticsService->getSourceEffectiveness($currentFilters),
            'interview_metrics' => $this->analyticsService->getInterviewMetrics($currentFilters),
            default => throw new \InvalidArgumentException('Invalid metric'),
        };

        $previousData = match($metric) {
            'funnel' => $this->analyticsService->getRecruitmentFunnel($previousFilters),
            'time_to_hire' => $this->analyticsService->getTimeToHireMetrics($previousFilters),
            'source_effectiveness' => $this->analyticsService->getSourceEffectiveness($previousFilters),
            'interview_metrics' => $this->analyticsService->getInterviewMetrics($previousFilters),
            default => throw new \InvalidArgumentException('Invalid metric'),
        };

        return response()->json([
            'success' => true,
            'data' => [
                'current_period' => $currentData,
                'previous_period' => $previousData,
                'comparison' => $this->calculateComparison($currentData, $previousData, $metric)
            ]
        ]);
    }

    /**
     * Extract filters from request
     */
    private function getFilters(Request $request): array
    {
        $filters = [];

        if ($request->has('date_from')) {
            $filters['date_from'] = Carbon::parse($request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $filters['date_to'] = Carbon::parse($request->input('date_to'));
        }

        if ($request->has('job_id') && $request->input('job_id')) {
            $filters['job_id'] = $request->input('job_id');
        }

        if ($request->has('department_id') && $request->input('department_id')) {
            $filters['department_id'] = $request->input('department_id');
        }

        return $filters;
    }

    /**
     * Export data to PDF
     */
    private function exportToPdf($data, string $filename, string $reportType)
    {
        // Implementation would use DomPDF or similar
        // This is a placeholder for the PDF export functionality
        return response()->json([
            'success' => true,
            'message' => 'PDF export functionality to be implemented',
            'download_url' => url("/exports/{$filename}.pdf")
        ]);
    }

    /**
     * Export data to Excel
     */
    private function exportToExcel($data, string $filename, string $reportType)
    {
        // Implementation would use Laravel Excel
        // This is a placeholder for the Excel export functionality
        return response()->json([
            'success' => true,
            'message' => 'Excel export functionality to be implemented',
            'download_url' => url("/exports/{$filename}.xlsx")
        ]);
    }

    /**
     * Export data to CSV
     */
    private function exportToCsv($data, string $filename, string $reportType)
    {
        // Implementation would flatten data and create CSV
        // This is a placeholder for the CSV export functionality
        return response()->json([
            'success' => true,
            'message' => 'CSV export functionality to be implemented',
            'download_url' => url("/exports/{$filename}.csv")
        ]);
    }

    /**
     * Calculate comparison between periods
     */
    private function calculateComparison($current, $previous, string $metric): array
    {
        $comparison = [];

        switch ($metric) {
            case 'funnel':
                $comparison['total_applications_change'] = $this->calculatePercentChange(
                    $previous['total_applications'], 
                    $current['total_applications']
                );
                break;

            case 'time_to_hire':
                $comparison['average_days_change'] = $this->calculatePercentChange(
                    $previous['average_days'], 
                    $current['average_days']
                );
                break;

            case 'interview_metrics':
                $comparison['completion_rate_change'] = $this->calculatePercentChange(
                    $previous['completion_rate'], 
                    $current['completion_rate']
                );
                break;
        }

        return $comparison;
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentChange($previous, $current): array
    {
        if ($previous == 0) {
            return [
                'value' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'increase' : 'neutral'
            ];
        }

        $change = (($current - $previous) / $previous) * 100;

        return [
            'value' => round(abs($change), 2),
            'direction' => $change > 0 ? 'increase' : ($change < 0 ? 'decrease' : 'neutral')
        ];
    }
}