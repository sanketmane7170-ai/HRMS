<?php
namespace App\Console\Commands;

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
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;

class PHHolidayDebit extends Command
{
    protected $signature   = 'ph:holiday-debit {--date=}';
    protected $description = 'Debit PH leave for users who worked on a Without holiday';

    public function handle()
    {
        $date = $this->option('date');

        Log::info('PH Date Command Started', ['date' => $date]);

        if (! $date) {
            $this->error('Please provide --date=YYYY-MM-DD');
            return;
        }

        $parsedDate = Carbon::parse($date)->toDateString();

        // ✅ Get users who worked on that date
        $workedUsers = DB::table('attendances as a')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.user_id', 'a.date')
            ->whereDate('a.date', $parsedDate)
            ->where('u.id', '36')
            ->whereIn('a.status', [
                AttendanceStatus::Present,
                AttendanceStatus::Late,
                AttendanceStatus::EarlyOut,
            ])
            ->whereNotNull('a.clock_in')

            ->groupBy('a.user_id', 'a.date')
            ->get();

        Log::info('Worked users fetched', ['count' => $workedUsers->count()]);

        $setting = Setting::where('key', 'is_given_without_attend_PH')->first();

        foreach ($workedUsers as $row) {

            $user_id = $row->user_id;
            $date    = $row->date;

            Log::info('Processing user-date', compact('user_id', 'date'));

            $user = User::find($user_id);
            if (! $user) {
                continue;
            }

            // ✅ PH Leave Type
            $phLeave = LeaveType::where('name', 'like', '%PH%')->first();
            if (! $phLeave) {
                continue;
            }

            $year = Carbon::parse($date)->year;

            $leaveBalance = LeaveBalance::where([
                ['year', $year],
                ['user_id', $user_id],
                ['leave_type_id', $phLeave->id],
            ])->first();

            if (! $leaveBalance) {

                Log::warning('Leave balance not found, skipping user', [
                    'user_id'       => $user_id,
                    'leave_type_id' => $phLeave->id,
                    'year'          => $year,
                ]);

                $this->line("Skipped (No Leave Balance): User {$user_id} - {$date}");

                continue;
            }

            // ✅ Attendance check
            $attendance = Attendance::where('user_id', $user_id)
                ->whereDate('date', $date)
                ->whereIn('status', [
                    AttendanceStatus::Present,
                    AttendanceStatus::Late,
                    AttendanceStatus::EarlyOut,
                ])
                ->first();

            $worked = ($attendance && $attendance->clock_in) ? 1 : 0;

            if ($setting && $setting->value == 1) {
                $worked = 1;
            }

            if (! $worked) {
                continue;
            }

            // ✅ Duplicate check (IMPORTANT)
            $exists = PHLeaveReport::where([
                'user_id' => $user_id,
                'date'    => $date,
            ])->first();

            DB::beginTransaction();

            try {

                if ($exists) {

                    Log::warning('PH already exists, removing now', [
                        'user_id' => $user_id,
                        'date'    => $date,
                    ]);

                    // ✅ Prevent negative balance in logs
                    $newBalance = max(0, $leaveBalance->available - 1);

                    UserLeaveBalanceTransaction::create([
                        'user_id'          => $user_id,
                        'leave_type_id'    => $phLeave->id,
                        'transaction_type' => 'remove',
                        'old_balance'      => $leaveBalance->available,
                        'update_balance'   => -1,
                        'new_balance'      => $newBalance,
                        'transaction_date' => $date,
                        'description'      => 'PH removed (correction) for date: ' . $date,
                    ]);

                    // ✅ Delete report entry
                    $exists->delete();

                    // ✅ Safe decrement
                    if ($leaveBalance->available > 0) {
                        $leaveBalance->decrement('available', 1);
                    }

                    if ($leaveBalance->monthwiseDay > 0) {
                        $leaveBalance->decrement('monthwiseDay', 1);
                    }

                    DB::commit();

                    Log::info('PH Removed Successfully', [
                        'user_id' => $user_id,
                        'date'    => $date,
                    ]);

                    $this->info("PH Removed: User {$user_id} - {$date}");

                } else {

                    // ✅ Nothing to remove
                    Log::info('No PH record found to remove', [
                        'user_id' => $user_id,
                        'date'    => $date,
                    ]);

                    $this->line("Skipped (No PH Found): User {$user_id} - {$date}");
                    DB::rollBack(); // optional (no changes anyway)
                }

            } catch (\Exception $e) {

                DB::rollBack();

                Log::error('PH Remove Failed', [
                    'user_id' => $user_id,
                    'date'    => $date,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        Log::info('PH Date Command Completed');
        $this->info('Done ✅');
    }
}
