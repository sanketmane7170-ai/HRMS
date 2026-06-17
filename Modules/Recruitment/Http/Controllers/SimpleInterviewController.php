<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Recruitment\Entities\Interview;
use Modules\Recruitment\Entities\Application;
use App\Models\User;

class SimpleInterviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Simple interview list without complex permissions
     */
    public function index()
    {
        $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])
            ->orderBy('scheduled_at', 'desc')
            ->get();
            
        $interviewers = User::select('id', 'name')->get();
        
        return view('recruitment::interviews.simple', compact('interviews', 'interviewers'));
    }

    /**
     * AJAX endpoint for simple DataTable
     */
    public function data(Request $request)
    {
        $query = Interview::with(['application.job', 'application.user', 'interviewer']);

        // Apply filters
        if ($request->filled('interviewer_id')) {
            $query->where('interviewer_id', $request->interviewer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $interviews = $query->orderBy('scheduled_at', 'desc')->get();

        $data = [];
        foreach ($interviews as $index => $interview) {
            // Get candidate name with fallback logic
            $candidateName = 'Unknown Candidate';
            if ($interview->application && $interview->application->user) {
                $candidateName = $interview->application->user->name;
            } elseif ($interview->application && $interview->application->candidate_name) {
                $candidateName = $interview->application->candidate_name;
            }
            
            // Handle empty type field
            $type = $interview->type ?: 'phone';
            
            $data[] = [
                'DT_RowIndex' => $index + 1,
                'candidate' => $candidateName,
                'job_title' => optional($interview->application)->job->title ?? 'N/A',
                'interviewer_name' => optional($interview->interviewer)->name ?? 'N/A',
                'scheduled_date' => $interview->scheduled_at ? $interview->scheduled_at->format('M d, Y H:i') : 'N/A',
                'type' => ucfirst(str_replace('_', ' ', $type)),
                'status' => ucfirst($interview->status),
                'duration' => ($interview->duration_minutes ?? 60) . ' minutes',
                'actions' => '<a href="#" class="btn btn-sm btn-info">View</a>'
            ];
        }

        return response()->json([
            'data' => $data,
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data)
        ]);
    }
}