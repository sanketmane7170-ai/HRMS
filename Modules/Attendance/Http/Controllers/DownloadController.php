<?php

namespace Modules\Attendance\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Attendance\Exports\Attendance as AttendanceExport;
// use Modules\Attendance\Exports\SpecificUserAttendance as UserAttendanceExport;
use Modules\Attendance\Exports\UPGRADE_SpecificUserAttendance as UserAttendanceExport;
// use Modules\Attendance\Exports\BulkAttendance as BulkAttendanceExport;
use Modules\Attendance\Exports\UPGRADE_BulkAttendance as BulkAttendanceExport;

class DownloadController extends Controller
{
    /**
     * return Attendance file of the employees
     */
    public function csv(Request $request)
    {
        canPerform('Export Attendance');
        return Excel::download(new AttendanceExport($request), 'attendance' . date('Y-m-d') . '.xlsx');
    }

    public function userattendancecsv($user ,Request $request)
    {
        canPerform('Export Attendance');
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        return Excel::download(new AttendanceExport($request,$user), 'attendance' . date('Y-m-d') . '.xlsx');

        //return Excel::download(new UserAttendanceExport($user,$request), 'Attendance : ' . date('Y-m-d') . '.xlsx');
    }

    public function bulkcsv(Request $request)
    {
        canPerform('Export Attendance');
        return Excel::download(new BulkAttendanceExport($request), 'bulk-attendance' . date('Y-m-d') . '.xlsx');
    }
}
