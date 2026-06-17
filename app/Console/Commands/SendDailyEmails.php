<?php
namespace App\Console\Commands;

use App\Mail\SendMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Leave\Entities\Leave;
use Modules\NotificationManager\Entities\AlertRecipient;
use Modules\NotificationManager\Entities\EmailAlertLog;

class SendDailyEmails extends Command
{
    protected $signature   = 'email:daily-mail'; // Command name
    protected $description = 'Send daily emails based on role permissions';

    public function handle()
    {

        $this->info('[' . now() . '] email:daily-mail started.');

        $dailyEmails = [
            'Leave Daily Email',
            'Attendance Daily Email',
            'Late Comers Daily Email',
            'Early Comers Daily Email',
            'Expense Daily Email',
        ];
        // $query = User::where('status', User::STATUS_ACTIVE);
        // $email_users = $query->get();

        DB::enableQueryLog();
        $email_users = User::where('status', User::STATUS_ACTIVE)
            ->whereHas('roles.permissions', function ($q) {
                $q->where('name', 'LIKE', '%Daily Email%');
            })
            ->get();

        Log::info('email:daily-mail', ["getQueryLog" => DB::getQueryLog()]);
        Log::info('email:daily-mail', ["email_users" => $email_users]);

        foreach ($email_users as $key => $email_user) {
            $this->info('[' . now() . '] email_user: ' . json_encode($email_user));

            foreach ($dailyEmails as $row => $dailyEmail) {
                if ($email_user->can($dailyEmail) || $email_user->hasRole('admin')) {
                    $this->info('[' . now() . '] dailyEmail: ' . json_encode($dailyEmail));

                    $this->sendEmail($email_user, $dailyEmail);
                }
            }
        }

        $alertRecipients = AlertRecipient::with('user')
            ->where('alert_status', 1)
            ->get();

        foreach ($alertRecipients as $alertRecipient) {

            if (! $alertRecipient->user) {
                continue;
            }

            $user = $alertRecipient->user;

            foreach ($dailyEmails as $dailyEmail ) {

                if ($user->hasRole('admin') && $dailyEmail=="Late Comers Daily Email") {

                    $this->sendEmail($user, $dailyEmail);
                }
            }
        }

        $this->info('Daily Emails have been sent successfully!');
        $this->info('[' . now() . '] email:daily-mail ended]\nDaily Emails have been sent successfully!.');

    }

    /**
     * Send email to the user.
     */
    protected function sendEmail($email_user, $emailType)
    {
        Log::info('SendEmail started.', [
            'date_time' => now()->toDateTimeString(),                                // Current date and time
            'data'      => ['email_user' => $email_user, 'emailType' => $emailType], // Example array
        ]);

        if ($emailType == "Leave Daily Email") {
            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                });
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
            $users       = $query->get();
            $exportExcel = [];

            foreach ($users as $key => $user) {
                $data = [
                    $user->employee_id,
                    $user->name,
                ];
                $date = now()->subDay()->toDateString();

                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                if ($holiday) {
                    $data[] = "H";
                } else {
                    DB::connection()->enableQueryLog();
                    $attendance = Attendance::where('user_id', $user->id)->where('date', $date)->get();
                    $queries    = DB::getQueryLog();
                    $last_query = end($queries);

                    // dd($last_query);

                    if (! empty($attendance[0]->status->name)) {

                        if ($attendance[0]->status->name == 'Leave') {
                            $data[] = "L";
                        } else {
                            $todayisleave = Leave::where([['user_id', $user->id], ['status', 'approved']])
                                ->whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->first();
                            if ($todayisleave) {
                                $data[] = "L";
                            }
                        }
                    }

                    $attendances[] = $data;
                }
            }

            $headers = [
                __trans('employee_id'),
                __trans('name'),
            ];

            $headers[]   = $date . "(" . now()->subDay()->format('l') . ")";
            $exportExcel = [];
            $filePath    = $emailType . "/daily_" . date('Y-m-d') . '.csv';
            $directory   = storage_path("app/{$emailType}");
            if (! file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($attendances as $row) {
                fputcsv($handle, is_array($row) ? $row : [$row]);
            }
            fclose($handle);

            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);

            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "daily", $exportExcel, $storagefilePath, $email_user));

                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),                                // Current date and time
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType], // Example array
                ]);
                EmailAlertLog::create([
                    'email'      => $email_user->email,
                    'status'     => 'success',
                    'alert_type' => $emailType,
                    'message'    => 'Email sent successfully.',
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
                EmailAlertLog::create([
                    'email'   => $email_user->email,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        } else if ($emailType == "Attendance Daily Email") {

            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                });
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
            $users       = $query->get();
            $exportExcel = [];

            foreach ($users as $key => $user) {
                $data = [
                    $user->employee_id,
                    $user->name,
                ];
                $date = now()->subDay()->toDateString();

                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                if ($holiday) {
                    $data[] = "H";
                } else {
                    DB::connection()->enableQueryLog();
                    $attendance = Attendance::where('user_id', $user->id)->where('date', $date)->get();
                    $queries    = DB::getQueryLog();
                    $last_query = end($queries);

                    // dd($last_query);

                    if (! empty($attendance[0]->status->name)) {

                        if ($attendance[0]->status->name == 'Present') {
                            $data[] = "P";
                        } elseif ($attendance[0]->status->name == 'Absent') {
                            $data[] = "A";
                        } elseif ($attendance[0]->status->name == 'Leave') {
                            $data[] = "L";
                        } elseif ($attendance[0]->status->name == 'Weekend') {
                            $data[] = "W";
                        } elseif ($attendance[0]->status->name == 'Holiday') {
                            $data[] = "H";
                        } else {
                            $data[] = "NA";
                        }
                    } else {
                        $todayisleave = Leave::where([['user_id', $user->id], ['status', 'approved']])
                            ->whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date)
                            ->first();
                        if ($todayisleave) {
                            $data[] = "L";
                        } else {
                            $data[] = "";
                        }
                    }
                }

                $attendances[] = $data;
            }

            $headers = [
                __trans('employee_id'),
                __trans('name'),
            ];

            $headers[]   = $date . "(" . now()->subDay()->format('l') . ")";
            $exportExcel = [];
            $filePath    = $emailType . "/daily_" . date('Y-m-d') . '.csv';
            $directory   = storage_path("app/{$emailType}");
            if (! file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($attendances as $row) {
                fputcsv($handle, is_array($row) ? $row : [$row]);
            }
            fclose($handle);

            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);

            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "daily", $exportExcel, $storagefilePath, $email_user));
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),                                // Current date and time
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType], // Example array
                ]);
                EmailAlertLog::create([
                    'email'      => $email_user->email,
                    'status'     => 'success',
                    'alert_type' => $emailType,
                    'message'    => 'Email sent successfully.',
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
                EmailAlertLog::create([
                    'email'   => $email_user->email,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        } else if ($emailType == "Late Comers Daily Email") {

            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                });
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
            $users       = $query->get();
            $exportExcel = [];

            foreach ($users as $key => $user) {
                $data = [
                    $user->employee_id,
                    $user->name,
                ];
                $date = now()->subDay()->toDateString();

                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                if ($holiday) {
                    $data[] = "H";
                } else {
                    DB::connection()->enableQueryLog();
                    $attendance = Attendance::where('user_id', $user->id)->where('date', $date)->get();
                    $queries    = DB::getQueryLog();
                    $last_query = end($queries);
                    if (! empty($attendance[0]->status->name)) {
                        // echo"<pre>";print_r($attendance[0]->clock_in);die;
                        if ($attendance[0]->status->name == 'Present' || $attendance[0]->status->name == 'Late') {

                            $users_shifts = DB::table('users_shifts')
                                ->join('shift_schedules', 'users_shifts.schedule_id', '=', 'shift_schedules.id')
                                ->where('users_shifts.user_id', $user->id)
                                ->where('users_shifts.assigned_for_date', $date)
                                ->get();
                            if (! empty($users_shifts[0]->shift_start)) {
                                $shift_start = Carbon::parse($users_shifts[0]->shift_start)->format('H:i:00');
                                $clock_in    = Carbon::parse($attendance[0]->clock_in)->format('H:i:00');
                                // $visit_in =  Carbon::parse($attendance[0]->visit_in)->format('H:i:00') ;
                                $locationvisits = LocationVisits::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->orderBy('id', 'asc')->first();

                                if ($locationvisits) {
                                    $visit_in = Carbon::parse($locationvisits->visit_in)->format('H:i:00');
                                    // dd($visit_in);
                                    if ($shift_start < $visit_in) {
                                        // dd($locationvisits->visit_in);
                                        $late        = (new Carbon($visit_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $lateMinutes = (new Carbon($visit_in))->diffInMinutes(new Carbon($shift_start), true);
                                        $data[]      = "L-P(" . $late . ")";
                                    } else {
                                        $data[] = "P";
                                    }
                                } else {
                                    $checkins = Checkin::where('user_id', $user->id)
                                        ->where('date', $date)
                                        ->where('type', 'in')
                                        ->orderBy('id', 'asc')->first();
                                    if (isset($checkins) && $shift_start < Carbon::parse($checkins->time)->format('H:i:00')) {
                                        $clock_in = Carbon::parse($checkins->time)->format('H:i:00');
                                        $early    = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $data[]   = "L-P(" . $early . ")";
                                    } else if ($shift_start < $clock_in) {
                                        $late        = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                        $lateMinutes = (new Carbon($clock_in))->diffInMinutes(new Carbon($shift_start), true);
                                        $data[]      = "L-P(" . $late . ")";
                                    } else {
                                        $data[] = "P";
                                    }
                                }
                            } else {
                                $data[] = "P";
                            }
                        } elseif ($attendance[0]->status->name == 'Absent') {
                            $data[] = "A";
                        } else {
                            $data[] = "NA";
                        }
                    } else {
                        $data[] = "";
                    }
                }

                $attendances[] = $data;
            }

            $headers = [
                __trans('employee_id'),
                __trans('name'),
            ];

            $headers[]   = $date . "(" . now()->subDay()->format('l') . ")";
            $exportExcel = [];
            $filePath    = $emailType . "/daily_" . date('Y-m-d') . '.csv';
            $directory   = storage_path("app/{$emailType}");
            if (! file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($attendances as $row) {
                fputcsv($handle, is_array($row) ? $row : [$row]);
            }
            fclose($handle);

            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);

            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "daily", $exportExcel, $storagefilePath, $email_user));
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),                                // Current date and time
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType], // Example array
                ]);
                EmailAlertLog::create([
                    'email'      => $email_user->email,
                    'status'     => 'success',
                    'alert_type' => $emailType,
                    'message'    => 'Email sent successfully.',
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
                EmailAlertLog::create([
                    'email'   => $email_user->email,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        } else if ($emailType == "Early Comers Daily Email") {

            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                });

            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }

            $users       = $query->get();
            $attendances = [];                              // ✅ Initialize attendances
            $date        = now()->subDay()->toDateString(); // ✅ Single date reference

            foreach ($users as $user) {
                $data = [
                    $user->employee_id,
                    $user->name,
                ];

                $holiday = Holiday::whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->first();

                if ($holiday) {
                    $data[] = "H";
                } else {
                    $attendance = Attendance::where('user_id', $user->id)
                        ->where('date', $date)
                        ->first();

                    if ($attendance && ! empty($attendance->status->name)) {

                        if ($attendance->status->name === 'Present') {

                            $user_shift = DB::table('users_shifts')
                                ->join('shift_schedules', 'users_shifts.schedule_id', '=', 'shift_schedules.id')
                                ->where('users_shifts.user_id', $user->id)
                                ->where('users_shifts.assigned_for_date', $date)
                                ->first();

                            if ($user_shift && ! empty($user_shift->shift_start)) {

                                $shift_start = Carbon::parse($user_shift->shift_start)->format('H:i:00');
                                $clock_in    = Carbon::parse($attendance->clock_in)->format('H:i:00');

                                $location_visit = LocationVisits::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->orderBy('id', 'asc')
                                    ->first();

                                if ($location_visit) {
                                    $visit_in = Carbon::parse($location_visit->visit_in)->format('H:i:00');

                                    if ($visit_in < $shift_start) {
                                        $early  = (new Carbon($shift_start))->diff(new Carbon($visit_in))->format('%h:%I');
                                        $data[] = "L-P(" . $early . ")";
                                    } else {
                                        $data[] = "P";
                                    }

                                } else {
                                    $checkin = Checkin::where('user_id', $user->id)
                                        ->where('date', $date)
                                        ->where('type', 'in')
                                        ->orderBy('id', 'asc')
                                        ->first();

                                    if ($checkin) {
                                        $checkin_time = Carbon::parse($checkin->time)->format('H:i:00');
                                        if ($checkin_time < $shift_start) {
                                            $early  = (new Carbon($shift_start))->diff(new Carbon($checkin_time))->format('%h:%I');
                                            $data[] = "L-P(" . $early . ")";
                                        } else {
                                            $data[] = "P";
                                        }
                                    } else if ($clock_in < $shift_start) {
                                        $early  = (new Carbon($shift_start))->diff(new Carbon($clock_in))->format('%h:%I');
                                        $data[] = "L-P(" . $early . ")";
                                    } else {
                                        $data[] = "P";
                                    }
                                }
                            } else {
                                $data[] = "P"; // No shift info, mark as present
                            }

                        } elseif ($attendance->status->name === 'Absent') {
                            $data[] = "A";
                        } else {
                            $data[] = "NA";
                        }

                    } else {
                        $data[] = ""; // No attendance record
                    }
                }

                $attendances[] = $data;
            }

            // Prepare headers
            $headers = [
                __trans('employee_id'),
                __trans('name'),
                $date . " (" . now()->subDay()->format('l') . ")",
            ];

            $exportExcel = $attendances; // ✅ Assign the data

            // Prepare file
            $filePath  = $emailType . "/daily_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");

            if (! file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);

            foreach ($attendances as $row) {
                fputcsv($handle, is_array($row) ? $row : [$row]);
            }

            fclose($handle);

            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);

            try {
                // Consider making this email dynamic or configurable
                Mail::to($email_user->email)->send(new SendMail($emailType, "daily", $exportExcel, $storagefilePath, $email_user));

                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
                EmailAlertLog::create([
                    'email'      => $email_user->email,
                    'status'     => 'success',
                    'alert_type' => $emailType,
                    'message'    => 'Email sent successfully.',
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
                EmailAlertLog::create([
                    'email'   => $email_user->email,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        } else if ($emailType == "Expense Daily Email") {

            $query = DB::table('expenses')
                ->select('expenses.*', 'expense_types.name as expense_types', 'users.name as user_name', 'users.employee_id', 'creator.name as creator_name')
                ->join('users', 'expenses.user_id', '=', 'users.id')
                ->join('users as creator', 'expenses.created_by', '=', 'creator.id')
                ->join('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id');

            $date = now()->subDay()->toDateString();
            $query->where('date', '>=', $date);
            $query->where('date', '<=', $date);
            if ($email_user->hasRole('employee')) {
                $query->where('expenses.user_id', $email_user->id);
            }
            $query->orderBy('date', 'desc');
            $expenses = $query->get();

            $headers[] = __trans('user_id');
            $headers[] = __trans('creator_name');
            $headers[] = __trans('employee_name');
            $headers[] = __trans('date');
            $headers[] = __trans('expense_types');
            $headers[] = __trans('name');
            $headers[] = __trans('amount');
            $headers[] = __trans('remark');
            $headers[] = __trans('status');

            $exportExcel = [];

            foreach ($expenses as $i => $data) {
                $exportExcel[$i]['user_id']       = $data->employee_id;
                $exportExcel[$i]['creator_name']  = $data->creator_name;
                $exportExcel[$i]['employee_name'] = $data->user_name;
                $exportExcel[$i]['date']          = $data->date;
                $exportExcel[$i]['expense_types'] = $data->expense_types;
                $exportExcel[$i]['name']          = $data->name;
                $exportExcel[$i]['amount']        = $data->amount;
                $exportExcel[$i]['remark']        = $data->remark;
                $exportExcel[$i]['status']        = $data->status;
            }

            $filePath  = $emailType . "/daily_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
            if (! file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($exportExcel as $row) {
                fputcsv($handle, is_array($row) ? $row : [$row]);
            }
            fclose($handle);

            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);

            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "daily", $exportExcel, $storagefilePath, $email_user));
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),                                // Current date and time
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType], // Example array
                ]);
                EmailAlertLog::create([
                    'email'      => $email_user->email,
                    'status'     => 'success',
                    'alert_type' => $emailType,
                    'message'    => 'Email sent successfully.',
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
                EmailAlertLog::create([
                    'email'   => $email_user->email,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
