<?php

namespace Modules\Attendance\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Attendance\Entities\Holiday;

class CalendarController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'holidays');
    }

    /**
     * Return Calendar view for holiday
     */
    public function index(Request $request)
    {

        if (!config('attendance.holiday_use_calendar_view')) {
            return redirect()->route('backend.holidays.index');
        }
        return view('attendance::holiday.calendar');
    }

    /**
     * Return lIST of holidays for the calendar
     */
    public function getHolidayList(Request $request): JsonResponse
    {
        $holidays = Holiday::whereDate('start_date', '>=', $request->start)
            ->whereDate('end_date', '<=', $request->end)
            ->get()->map(function ($holiday) {
                return [
                    'id' => $holiday->id,
                    'title' => $holiday->detail,
                    'start' => $holiday->start_date,
                    'end' => $holiday->end_date,
                    'color' => '#' . str_pad(dechex(mt_rand($holiday->id, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
                    'update_url' => route('backend.holidays.update', $holiday),
                    'delete_url' => route('backend.holidays.destroy', $holiday),
                    'allDay' => true
                ];
            });

        return response()->json($holidays);
    }
}
