<?php
namespace App\Console\Commands;

use App\Models\Country;
use App\Models\PHLeaveReport;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeaveBalanceTransaction;
use Carbon\Carbon;
use Google\Service\Datastore\Count;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;

class PHHolidayCredit extends Command
{
    protected $signature   = 'ph:holiday-credit {--holiday_id=} {--country_id=}';
    protected $description = 'Credit PH leave for users who worked on a holiday';

    public function handle()
    {
        $holidayId = $this->option('holiday_id');

        Log::info('PH Command Started', ['holiday_id' => $holidayId]);

        if (! $holidayId) {
            Log::error('Holiday ID missing');
            $this->error('Please provide --holiday_id');
            return;
        }

        $holiday = Holiday::find($holidayId);

        if (! $holiday) {
            Log::error('Holiday not found', ['holiday_id' => $holidayId]);
            $this->error('Holiday not found');
            return;
        }

        Log::info('Processing Holiday', [
            'holiday_id' => $holiday->id,
            'detail'     => $holiday->detail,
            'start_date' => $holiday->start_date,
            'end_date'   => $holiday->end_date,
        ]);

        $workedUsers = DB::table('attendances as a')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.user_id', 'a.date')
            ->whereBetween('a.date', [$holiday->start_date, $holiday->end_date])
            ->whereIn('a.status', [
                \Modules\Attendance\Enums\AttendanceStatus::Present,
                \Modules\Attendance\Enums\AttendanceStatus::Late,
                \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
            ])
            ->whereNotNull('a.clock_in')
            ->groupBy('a.user_id', 'a.date')
            ->get();

        Log::info('Worked users fetched', ['count' => $workedUsers->count()]);
        $setting = Setting::where('key', 'is_given_without_attend_PH')->first();

        Log::info('Setting loaded', [
            'is_given_without_attend_PH' => $setting->value ?? null,
        ]);

        foreach ($workedUsers as $row) {

            $user_id = $row->user_id;
            $date    = $row->date;

            Log::info('Processing user-date', [
                'user_id' => $user_id,
                'date'    => $date,
            ]);

            $user = User::find($user_id);
            if (! $user) {
                Log::warning('User not found', ['user_id' => $user_id]);
                continue;
            }
            log::info('User found', [
                'user_id' => $user->id,
                'name'    => $user->name,
            ]);

            // ✅ PH Leave Type
            $phLeave = LeaveType::where('name', 'like', '%PH%')
                ->first();

            if (! $phLeave) {
                Log::warning('PH Leave type not found', [
                    'user_id'    => $user_id,
                ]);
                continue;
            }
            $year = Carbon::parse($date)->year;
            $leaveBalance = LeaveBalance::where([
                ['year', $year],
                ['user_id', $user_id],
                ['leave_type_id', $phLeave->id],
            ])->first();

            if (! $leaveBalance) {

                Log::info('Creating leave balance', [
                    'user_id'       => $user_id,
                    'leave_type_id' => $phLeave->id,
                    'year'          => $year,
                ]);

                $leaveBalance = LeaveBalance::create([
                    'year'                   => $year,
                    'user_id'                => $user_id,
                    'leave_type_id'          => $phLeave->id,

                    // ✅ Default values
                    'available'              => 0,
                    'monthwiseDay'           => 0,
                    'balance_added_by_cron'  => 0,
                    'cron_job_date'          => null,
                    'isAddThisMonthLeave'    => null,
                    'thisYearAvailableLeave' => 0,
                    'previous_year_balance'  => 0,
                    'current_year_balance'   => 0,
                ]);
            }

            // ✅ Attendance check
            $attendance = Attendance::where('user_id', $user_id)
                ->whereIn('status', [
                    AttendanceStatus::Present,
                    AttendanceStatus::Late,
                    AttendanceStatus::EarlyOut,
                ])
                ->whereDate('date', $date)
                ->first();

            Log::info('Attendance check', [
                'user_id'          => $user_id,
                'date'             => $date,
                'attendance_found' => $attendance ? true : false,
            ]);

            $worked = ($attendance && $attendance->clock_in) ? 1 : 0;

            if ($setting && $setting->value == 1) {
                Log::info('Override setting applied (force worked)');
                $worked = 1;
            }

            if (! $worked) {
                Log::warning('User skipped (not worked)', [
                    'user_id' => $user_id,
                    'date'    => $date,
                ]);
                continue;
            }

            $exists = PHLeaveReport::where([
                'user_id' => $user_id,
                'date'    => $date,
            ])->exists();

            if ($exists) {
                Log::warning('Duplicate skipped', [
                    'user_id' => $user_id,
                    'date'    => $date,
                ]);
                $this->line("Skipped (Already exists): User {$user_id} - {$date}");
                continue;
            }

            DB::beginTransaction();

            try {

                Log::info('Creating transaction', [
                    'user_id'     => $user_id,
                    'old_balance' => $leaveBalance->available,
                ]);

                // ✅ Transaction
                UserLeaveBalanceTransaction::create([
                    'user_id'          => $user_id,
                    'leave_type_id'    => $phLeave->id,
                    'transaction_type' => 'add',
                    'old_balance'      => $leaveBalance->available,
                    'update_balance'   => 1,
                    'new_balance'      => ($leaveBalance->available + 1),
                    'transaction_date' => $date,
                    'description'      => 'Worked on holiday: ' . $holiday->detail,
                ]);

                // ✅ Report
                PHLeaveReport::create([
                    'user_id'       => $user_id,
                    'holiday_id'    => $holiday->id,
                    'leave_type_id' => $phLeave->id,
                    'date'          => $date,
                ]);

                // ✅ Update balance
                $leaveBalance->increment('available', 1);
                $leaveBalance->increment('monthwiseDay', 1);

                DB::commit();

                Log::info('PH Leave Added Successfully', [
                    'user_id' => $user_id,
                    'date'    => $date,
                ]);

                $this->info("PH Added: User {$user_id} - {$date}");

            } catch (\Exception $e) {

                DB::rollBack();

                Log::error('PH Leave Failed', [
                    'user_id' => $user_id,
                    'date'    => $date,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        Log::info('PH Command Completed');
        $this->info('Done ✅');
    }
}
