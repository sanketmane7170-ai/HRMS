<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Recruitment\Entities\Interview;
use Modules\Recruitment\Entities\Application;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class InterviewListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        view()->share('activeLink', 'recruitment-interviews');
    }

    /**
     * Display a listing of interviews.
     */
    public function index()
    {
        if (request()->ajax()) {
            return $this->getInterviewsData();
        }
        
        $interviewers = User::select('id', 'name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        return view('recruitment::interviews.list', compact('interviewers'));
    }

    /**
     * Get interviews data for DataTable
     */
    private function getInterviewsData()
    {
        $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])
            ->select('interviews.*');

        // Apply filters
        if (request()->filled('interviewer_id')) {
            $interviews->where('interviewer_id', request('interviewer_id'));
        }

        if (request()->filled('status')) {
            $interviews->where('status', request('status'));
        }

        if (request()->filled('type')) {
            $interviews->where('type', request('type'));
        }

        return DataTables::of($interviews)
            ->addIndexColumn()
            ->addColumn('candidate', function ($interview) {
                if ($interview->application && $interview->application->user) {
                    return $interview->application->user->name;
                }
                if ($interview->application && $interview->application->candidate_name) {
                    return $interview->application->candidate_name;
                }
                return 'Unknown Candidate';
            })
            ->addColumn('job_title', function ($interview) {
                return optional($interview->application)->job->title ?? 'N/A';
            })
            ->addColumn('interviewer_name', function ($interview) {
                return optional($interview->interviewer)->name ?? 'N/A';
            })
            ->addColumn('scheduled_date', function ($interview) {
                return $interview->scheduled_at ? $interview->scheduled_at->format('M d, Y H:i') : 'N/A';
            })
            ->addColumn('type_display', function ($interview) {
                $type = $interview->type ?: 'phone'; // Default to phone if empty
                return ucfirst(str_replace('_', ' ', $type));
            })
            ->addColumn('status_display', function ($interview) {
                return ucfirst($interview->status);
            })
            ->addColumn('duration_display', function ($interview) {
                return ($interview->duration_minutes ?? 60) . ' minutes';
            })
            ->addColumn('actions', function ($interview) {
                $actions = [];
                $actions[] = '<a href="' . route('recruitment.interviews.show', $interview->id) . '" class="btn btn-sm btn-info" title="View">View</a>';
                
                if (auth()->user()->hasRole(['admin', 'hr']) || auth()->user()->can('Manage Interviews')) {
                    $actions[] = '<a href="' . route('recruitment.interviews.edit', $interview->id) . '" class="btn btn-sm btn-primary" title="Edit">Edit</a>';
                }
                
                return '<div class="btn-group">' . implode(' ', $actions) . '</div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Simple data endpoint for testing
     */
    public function testData()
    {
        $data = [
            'total_interviews' => Interview::count(),
            'total_applications' => Application::count(),
            'user_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'sample_interviews' => Interview::with(['application.job', 'interviewer'])
                ->limit(5)
                ->get()
                ->map(function($interview) {
                    return [
                        'id' => $interview->id,
                        'scheduled_at' => $interview->scheduled_at,
                        'status' => $interview->status,
                        'type' => $interview->type,
                        'job_title' => optional($interview->application)->job->title ?? 'N/A',
                        'interviewer' => optional($interview->interviewer)->name ?? 'N/A'
                    ];
                })
        ];
        
        return response()->json($data);
    }
}