<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Recruitment\Entities\Interview;

class BasicInterviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Super simple interview list - just HTML table, no JavaScript
     */
    public function index()
    {
        $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])
            ->orderBy('scheduled_at', 'desc')
            ->get();
            
        return view('recruitment::interviews.basic', compact('interviews'));
    }
}