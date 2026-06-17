<?php
namespace Modules\Attendance\Exports;

use App\Models\extraWorkRequest;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Attendance\Entities\Holiday;

class ExtraWorkHoursExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $request;
    protected $year;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->month   = $request->month;
        $this->year    = date('Y');
    }

    public function query()
    {
        return extraWorkRequest::where('year', $this->year)
            ->when($this->request->filled('month') && $this->month !== 'all', function ($query) {
                return $query->where('month', $this->month);
            })
            ->with('user')
            ->orderBy('date', 'desc');
    }

    public function map($row): array
    {
        $status = match ((int) $row->status) {
            0       => 'Pending',
            1       => 'Added To Payroll',
            2       => 'Added To Leave',
            3       => 'Rejected',
            default => 'Unknown',
        };
        $user    = $row->user != null ? User::find($row->user->id) : null;
        $addedby = $row->added_by != null ? User::find($row->added_by) : null;
        if ($user) {
            $today   = $row->date;
            $holiday = Holiday::whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
            $rate         = $holiday ? 1.50 : 1.25;
            $totalDays    = Carbon::parse($today)->daysInMonth;
            $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
            $user_shifts  = User::find($user->id)
                ->assigned_shifts()
                ->with('shift_schedule_information')
                ->where('assigned_for_date', $today)
                ->get();
            if ($company_hour < 0) {
                foreach ($user_shifts as $index => $shiftData) {
                    $shift = $shiftData->shift_schedule_information;
                    // Convert shift start and end times to Carbon instances
                    $shiftStart = Carbon::parse($shift->shift_start);
                    $shiftEnd   = Carbon::parse($shift->shift_end);

                    // Calculate the hours between shift start and end
                    if ($shiftEnd->lessThan($shiftStart)) {
                        $shiftEnd->addDay();
                    }
                    $hoursDifference   = $shiftEnd->diffInMinutes($shiftStart);
                    $totalShiftMinuts += $hoursDifference;
                }
                $working_hours = $hours . '.' . $minutes;
            } else {
                $working_hours = $company_hour;
            }
            // $extraminit        = $row->minit / 60;
            // $extraHours        = floatval($row->hours + $extraminit);
            // $calculated_amount = round(($user->salary->basic / $totalDays / $working_hours) * $rate * $extraHours, 2);

            $extraminit        = round($row->minit / 60, 2);
            $extraHours        = floatval($row->hours + $extraminit);
            $basicSalary       = $user->salary ? $user->salary->basic : 0;
            $calculated_amount = round(($basicSalary / $totalDays / $working_hours) * $rate * $extraHours, 2);
        } else {
            $calculated_amount = '-';
        }
        // $extraminit   = $row->minit / 60;
        // $extraHours   = floatval($row->hours + $extraminit);
        // $payableHours = number_format($extraHours, 2);

        $extraminit        = round($row->minit / 60, 2);
        $extraHours        = floatval($row->hours + $extraminit);
        $payableHours      = number_format($extraHours, 2);
        $value             = number_format($row->extra_hours, 2);
        [$hours, $minutes] = explode('.', $value);
        // $extra_hoursdata =  "{$hours} Hours {$minutes} Minutes";
        $extra_hoursdata = "{$row->hours} Hours {$row->minit} Minutes";

        return [
            $row->user->name ?? 'N/A',
            $addedby->name ?? 'N/A',
            $extra_hoursdata,
            $payableHours,
            Carbon::parse($row->date)->format('d-m-Y'),
            $calculated_amount,
            $status,
        ];
    }

    public function headings(): array
    {
        return ['User Name', 'Added By', 'Extra Hours (HH:MM)', 'Payable Hours', 'Date', 'Cash Amount', 'Status'];
    }
}
