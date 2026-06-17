<?php

namespace Modules\Attendance\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Enums\AttendanceStatus;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\User;
use App\Models\extraWork;
use App\Models\extraWorkRequest;
use App\Models\Setting;
use DatePeriod;
use DateTime;
use DateInterval;

class EmployeeAttendanceController extends Controller
{

    public function __construct()
    {
        view()->share('activeLink', 'attendances');
    }

    /**
     * Display a listing of the attenance of logged in user.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Attendance::my()->orderByDesc('date');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status->getHtml();
                })
                ->editColumn('timediff', function ($row) {
                    if($row->clock_out==""){
                        $row->clock_out = "18:00"; // if no clock out time then we will choose 6.00PM as clock out time
                    }
                    // Calculate time difference in hours and minutes
                    $clockIn = Carbon::parse($row->clock_in);
                    $clockOut = Carbon::parse($row->clock_out);
                    // If clock_out is before clock_in, it might be the next day
                    if($clockOut < $clockIn) {
                       $clockOut->addDay(); // Add a day to handle next-day clock_out
                    }

                    $diff = $clockIn->diff($clockOut);
                    return sprintf('%02d:%02d:%02d', $diff->h, $diff->i,$diff->s);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('attendance::employee.index');
    }    
}
