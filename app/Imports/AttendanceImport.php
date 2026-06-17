<?php
namespace App\Imports;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Enums\AttendanceStatus; // ✅ Add this
use Carbon\Carbon;

class AttendanceImport implements ToCollection, WithHeadingRow
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year  = $year;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['employee_id']) || empty($row['date'])) {
                continue;
            }

            // Map employee_id to actual user_id
            $user = User::where('employee_id', $row['employee_id'])->first();

            if (! $user) {
                Log::warning('Attendance import: User not found', ['employee_id' => $row['employee_id']]);
                continue;
            }

            $branchId     = Department::where('name', $row['branch_name'])->value('id') ?? null;
            $inTime       = $this->convertExcelTimeToTime($row['in_time']);
            $outTime      = $this->convertExcelTimeToTime($row['out_time']);
            $clockoutDate = $row['clockout_date'] ?: $row['date'];

            // Convert status string to Enum if provided
            $status = isset($row['status']) && !empty($row['status'])
                ? AttendanceStatus::tryFrom(strtolower($row['status'])) // convert to Enum
                : null;

            // -----------------------------
            // IN Checkin
            // -----------------------------
            if ($inTime) {
                Checkin::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date'    => $row['date'],
                        'type'    => CheckinType::IN,
                    ],
                    [
                        'time'      => $inTime,
                        'branch_id' => $branchId,
                    ]
                );

                $attendance = Attendance::firstOrNew([
                    'user_id' => $user->id,
                    'date'    => $row['date'],
                ]);

                $attendance->clock_in = $inTime;
                $attendance->created_by_id = $user->id;

                if ($status) {
                    $attendance->status = $status;
                }

                $attendance->save();
            }

            // -----------------------------
            // OUT Checkin
            // -----------------------------
            if ($outTime) {
                Checkin::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date'    => $clockoutDate,
                        'type'    => CheckinType::OUT,
                    ],
                    [
                        'time'      => $outTime,
                        'branch_id' => $branchId,
                    ]
                );

                $attendance = Attendance::firstOrNew([
                    'user_id' => $user->id,
                    'date'    => $clockoutDate,
                ]);

                $attendance->clock_out = $outTime;
                $attendance->clockout_date = $clockoutDate;

                if ($status) {
                    $attendance->status = $status;
                }

                // Calculate total worked minutes if clock_in exists
                if ($attendance->clock_in) {
                    $start = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                    $end   = Carbon::parse($clockoutDate . ' ' . $outTime);
                    $attendance->total_worked = $start->diffInMinutes($end);
                }

                $attendance->save();
            }
        }
    }

    public function convertExcelTimeToTime($value)
    {
        if (empty($value)) {
            return null;
        }

        // If numeric (Excel fraction)
        if (is_numeric($value)) {
            $seconds = round($value * 24 * 60 * 60);
            $hours   = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;

            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        // If already string like "10:00:04", just return as-is
        return $value;
    }
}
