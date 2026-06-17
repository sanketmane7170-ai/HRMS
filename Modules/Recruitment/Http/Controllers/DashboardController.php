<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Interview;
use Modules\Recruitment\Entities\Offer;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'recruitment-dashboard');
    }

    public function index()
    {
        // Provide initial stats to avoid showing 0 while AJAX loads
        $stats = [
            'activeJobs' => Job::where('status', 'active')->count(),
            'totalApplications' => Application::count(),
            'upcomingInterviews' => Interview::where('scheduled_at', '>=', Carbon::now())->where('status', 'scheduled')->count(),
            'pendingOffers' => Offer::where('status', 'pending')->count()
        ];
        
        return view('recruitment::index', compact('stats'));
    }

    public function stats()
    {
        // Enforce permission (Sanket - REC-SEC-022)
        if (!auth()->user()->hasRole(['admin', 'hr']) && !auth()->user()->can('View Dashboard')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        try {
            $activeJobs = Job::where('status', 'active')->count();
            $totalApplications = Application::count();
            $upcomingInterviews = Interview::where('scheduled_at', '>=', Carbon::now())->where('status', 'scheduled')->count();
            $pendingOffers = Offer::where('status', 'pending')->count();

            return response()->json([
                'success' => true,
                'activeJobs' => $activeJobs,
                'totalApplications' => $totalApplications,
                'upcomingInterviews' => $upcomingInterviews,
                'pendingOffers' => $pendingOffers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'activeJobs' => 0,
                'totalApplications' => 0,
                'upcomingInterviews' => 0,
                'pendingOffers' => 0
            ], 500);
        }
    }

    public function recentApplications()
    {
        // Enforce permission (Sanket - REC-SEC-022)
        if (!auth()->user()->hasRole(['admin', 'hr']) && !auth()->user()->can('View Dashboard')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $applications = Application::with(['job', 'user'])
                                 ->orderBy('created_at', 'desc')
                                 ->take(5)
                                 ->get();

        return response()->json($applications);
    }

    public function upcomingInterviews()
    {
        // Enforce permission (Sanket - REC-SEC-022)
        if (!auth()->user()->hasRole(['admin', 'hr']) && !auth()->user()->can('View Dashboard')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $interviews = Interview::with(['application.job', 'application.user'])
                              ->where('scheduled_at', '>=', Carbon::now())
                              ->where('status', 'scheduled')
                              ->orderBy('scheduled_at', 'asc')
                              ->take(5)
                              ->get();

        return response()->json($interviews);
    }
}