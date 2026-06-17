<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Recruitment\Entities\Interview;
use Modules\Recruitment\Entities\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InterviewDebugController extends Controller
{
    /**
     * Debug interview data - no authentication required for testing
     */
    public function debug()
    {
        try {
            $data = [
                'database_connection' => DB::connection()->getPdo() ? 'Connected' : 'Failed',
                'interviews_table_exists' => Schema::hasTable('interviews'),
                'applications_table_exists' => Schema::hasTable('recruitment_applications'),
                'users_table_exists' => Schema::hasTable('users'),
                
                'total_interviews' => Interview::count(),
                'total_applications' => Application::count(),
                'total_users' => User::count(),
                
                'interview_columns' => Schema::getColumnListing('interviews'),
                
                'sample_interview_raw' => Interview::first(),
                'sample_application_raw' => Application::first(),
                
                'interviews_with_relations' => Interview::with(['application.job', 'application.user', 'interviewer'])
                    ->limit(3)
                    ->get()
                    ->map(function($interview) {
                        return [
                            'id' => $interview->id,
                            'scheduled_at' => $interview->scheduled_at,
                            'status' => $interview->status,
                            'type' => $interview->type,
                            'application_id' => $interview->application_id,
                            'interviewer_id' => $interview->interviewer_id,
                            'has_application' => $interview->application ? 'yes' : 'no',
                            'has_interviewer' => $interview->interviewer ? 'yes' : 'no',
                            'application_data' => $interview->application ? [
                                'id' => $interview->application->id,
                                'candidate_name' => $interview->application->candidate_name,
                                'job_id' => $interview->application->job_id,
                                'has_job' => $interview->application->job ? 'yes' : 'no',
                                'job_title' => $interview->application->job ? $interview->application->job->title : 'no job'
                            ] : null,
                            'interviewer_data' => $interview->interviewer ? [
                                'id' => $interview->interviewer->id,
                                'name' => $interview->interviewer->name
                            ] : null
                        ];
                    }),
                
                'current_user' => auth()->check() ? [
                    'id' => auth()->id(),
                    'email' => auth()->user()->email,
                    'name' => auth()->user()->name
                ] : 'Not authenticated'
            ];
            
            return response()->json($data, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
    
    /**
     * Simple HTML page to show interview data
     */
    public function show()
    {
        try {
            $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])
                ->orderBy('scheduled_at', 'desc')
                ->get();
                
            return view('recruitment::interviews.debug', compact('interviews'));
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Error loading interviews'
            ]);
        }
    }
}