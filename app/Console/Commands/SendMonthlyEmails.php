<?php
namespace App\Console\Commands;

use App\Mail\SendMail;
use App\Models\PHLeaveReport;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Leave\Entities\Leave;
use Modules\Payroll\Http\Controllers\UserPaySlipController;
use Illuminate\Support\Str;
use Modules\Leave\Entities\LeaveType;


class SendMonthlyEmails extends Command
{
    protected $signature   = 'email:monthly-mail'; // Command name
    protected $description = 'Send monthly emails based on role permissions';

    public function handle()
    {

        $this->info('[' . now() . '] email:monthly-mail started.');

        $monthlyEmails = [
            'Leave Monthly Email',
            'Attendance Monthly Email',
            'Late Comers Monthly Email',
            'Early Comers Monthly Email',
            'Salary Increments Monthly Email',
            'Expense Monthly Email',
            'Gratuity Accrual Monthly Email',
            'Medical Insurance Accrual Monthly Email',
            'Air Ticket Accrual Monthly Email',
            'Leave Salary Accrual Monthly Email',
            'Accrual Monthly Email',
            'PH Leave Monthly Email',
            'Leave Balance Monthly Email',
        ];

        // $query = User::where('status', User::STATUS_ACTIVE);
        // $email_users = $query->get();
        \DB::enableQueryLog();
        $email_users = User::where('status', User::STATUS_ACTIVE)
            ->whereHas('roles.permissions', function ($q) {
                $q->where('name', 'LIKE', '%Monthly Email%');
            })
            ->get();

        Log::info('email:daily-mail', ["getQueryLog" => \DB::getQueryLog()]);
        Log::info('email:daily-mail', ["email_users" => $email_users]);
        foreach ($email_users as $key => $email_user) {
            $this->info('[' . now() . '] email_users: ' . json_encode($email_users));

            foreach ($monthlyEmails as $row => $monthlyEmail) {
                if ($email_user->can($monthlyEmail) || $email_user->hasRole('admin')) {
                    $this->info('[' . now() . '] monthlyEmail: ' . json_encode($monthlyEmail));

                    $this->sendEmail($email_user, $monthlyEmail);
                }
            }
        }
        $this->info('Monthly Emails have been sent successfully!');
        $this->info('[' . now() . '] email:monthly-mail ended]\nMonthly Emails have been sent successfully!.');

    }

    /**
     * Send email to the user.
     */
    protected function sendEmail($email_user, $emailType)
    {
        Log::info('Monthly SendEmail started.', [
            'date_time' => now()->toDateTimeString(),                                // Current date and time
            'data'      => ['email_user' => $email_user, 'emailType' => $emailType], // Example array
        ]);

        $year                 = Carbon::now()->subMonth()->year;
        $month                = Carbon::now()->subMonth()->month;
        $daysInMonth          = Carbon::now()->subMonth()->daysInMonth;
        // $yesterdaydate        = now()->subDay()->toDateString();
        $yesterdaydate = now()->subDay();
        $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $endOfPreviousMonth   = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        if ($emailType == "Leave Monthly Email") {

            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
                });

            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }

            $users       = $query->get();
            $attendances = []; // ✅ Initialize
            $exportExcel = [];

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dates[] = Carbon::createFromDate($year, $month, $i)->toDateString();
            }

            foreach ($users as $user) {
                $data = [
                    $user->employee_id,
                    $user->name,
                ];

                foreach ($dates as $date) {
                    $status = '';

                    $holiday = Holiday::whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->first();

                    if ($holiday) {
                        $status = "H";
                    } else {
                        $attendance = Attendance::where('user_id', $user->id)
                            ->where('date', $date)
                            ->first();

                        if ($attendance && $attendance->status && $attendance->status->name == 'Leave') {
                            $status = "L";
                        } else {
                            $leave = Leave::where('user_id', $user->id)
                                ->where('status', 'approved')
                                ->whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->first();

                            if ($leave) {
                                $status = "L";
                            }
                        }
                    }

                    $data[] = $status;
                }

                $attendances[] = $data;
            }

            // Prepare headers
            $headers = [
                __trans('employee_id'),
                __trans('name'),
            ];

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $headers[] = $i;
            }

            $exportExcel = $attendances;
            $filePath    = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
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
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $storagefilePath,$email_user));

                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        } else if ($emailType == "Attendance Monthly Email") {

            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
                });
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $users = $query->get();
            $attendances = [];
            $exportExcel = [];
        
            // Prepare date array for the month
            $dates = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dates[] = Carbon::createFromDate($year, $month, $i)->toDateString();
            }
        
            foreach ($users as $user) {
                $data = [
                    $user->employee_id,
                    $user->name,
                ];
        
                foreach ($dates as $date) {
                    $status = '';
        
                    // Check holiday first
                    $holiday = Holiday::whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->first();
        
                    if ($holiday) {
                        $status = "H";
                    } else {
                        $attendance = Attendance::where('user_id', $user->id)
                            ->where('date', $date)
                            ->first();
        
                        if ($attendance && $attendance->status) {
                            switch (strtolower($attendance->status->name)) {
                                case 'present':
                                    $status = "P";
                                    break;
                                case 'absent':
                                    $status = "A";
                                    break;
                                case 'leave':
                                    $status = "L";
                                    break;
                                case 'weekend':
                                    $status = "W";
                                    break;
                                case 'holiday':
                                    $status = "H";
                                    break;
                                default:
                                    $status = "NA";
                            }
                        } else {
                            // Check approved leave if attendance is missing
                            $leave = Leave::where('user_id', $user->id)
                                ->where('status', 'approved')
                                ->whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->first();
        
                            if ($leave) {
                                $status = "L";
                            }
                        }
                    }
        
                    $data[] = $status;
                }
        
                $attendances[] = $data;
            }
        
            // Prepare headers
            $headers = [
                __trans('employee_id'),
                __trans('name'),
            ];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $headers[] = $i;
            }
        
            $exportExcel = $attendances;
            $filePath = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
        
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
        
            foreach ($attendances as $row) {
                fputcsv($handle, $row);
            }
        
            fclose($handle);
        
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $storagefilePath,$email_user));
        
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Late Comers Monthly Email") {

            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
                });
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $users = $query->get();
            $attendances = [];
            $exportExcel = [];
        
            $dates = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dates[] = Carbon::createFromDate($year, $month, $i)->toDateString();
            }
        
            foreach ($users as $user) {
                $data = [
                    $user->employee_id,
                    $user->name,
                ];
        
                foreach ($dates as $date) {
                    $status = '';
        
                    // Check holiday
                    $holiday = Holiday::whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->first();
        
                    if ($holiday) {
                        $status = "H";
                    } else {
                        $attendance = Attendance::where('user_id', $user->id)
                            ->where('date', $date)
                            ->first();
        
                        if ($attendance && $attendance->status && $attendance->status->name == 'Present') {
                            $users_shift = DB::table('users_shifts')
                                ->join('shift_schedules', 'users_shifts.schedule_id', '=', 'shift_schedules.id')
                                ->where('users_shifts.user_id', $user->id)
                                ->where('users_shifts.assigned_for_date', $date)
                                ->first();
        
                            $shift_start = $users_shift ? Carbon::parse($users_shift->shift_start)->format('H:i:s') : null;
                            $clock_in = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i:s') : null;
        
                            if ($shift_start) {
                                // Try location visit first
                                $locationVisit = LocationVisits::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->orderBy('id', 'asc')
                                    ->first();
        
                                if ($locationVisit && $locationVisit->visit_in) {
                                    $visit_in = Carbon::parse($locationVisit->visit_in)->format('H:i:s');
                                    if ($visit_in > $shift_start) {
                                        $late = Carbon::parse($visit_in)->diff(Carbon::parse($shift_start))->format('%h:%I');
                                        $status = "L-P($late)";
                                    } else {
                                        $status = "P";
                                    }
                                } else {
                                    // Fallback: checkins table
                                    $checkin = Checkin::where('user_id', $user->id)
                                        ->where('date', $date)
                                        ->where('type', 'in')
                                        ->orderBy('id', 'asc')
                                        ->first();
        
                                    if ($checkin && Carbon::parse($checkin->time)->format('H:i:s') > $shift_start) {
                                        $actual_in = Carbon::parse($checkin->time)->format('H:i:s');
                                        $late = Carbon::parse($actual_in)->diff(Carbon::parse($shift_start))->format('%h:%I');
                                        $status = "L-P($late)";
                                    } elseif ($clock_in && $clock_in > $shift_start) {
                                        $late = Carbon::parse($clock_in)->diff(Carbon::parse($shift_start))->format('%h:%I');
                                        $status = "L-P($late)";
                                    } else {
                                        $status = "P";
                                    }
                                }
                            } else {
                                $status = "P";
                            }
                        } elseif ($attendance && $attendance->status && $attendance->status->name == 'Absent') {
                            $status = "A";
                        } elseif ($attendance && $attendance->status) {
                            $status = $attendance->status->name ?? "NA";
                        } else {
                            $status = "";
                        }
                    }
        
                    $data[] = $status;
                }
        
                $attendances[] = $data;
            }
        
            // Prepare headers
            $headers = [
                __trans('employee_id'),
                __trans('name'),
            ];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $headers[] = $i;
            }
        
            $exportExcel = $attendances;
            $filePath = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
        
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($attendances as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $storagefilePath,$email_user));
        
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Early Comers Monthly Email") {

            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
                });
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $users = $query->get();
            $attendances = [];
            $exportExcel = [];
        
            $dates = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dates[] = Carbon::createFromDate($year, $month, $i)->toDateString();
            }
        
            foreach ($users as $user) {
                $data = [
                    $user->employee_id,
                    $user->name,
                ];
        
                foreach ($dates as $date) {
                    $status = "";
        
                    // Holiday check
                    $holiday = Holiday::whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->first();
        
                    if ($holiday) {
                        $status = "H";
                    } else {
                        $attendance = Attendance::where('user_id', $user->id)
                            ->where('date', $date)
                            ->first();
        
                        if ($attendance && $attendance->status && $attendance->status->name == 'Present') {
                            $users_shift = DB::table('users_shifts')
                                ->join('shift_schedules', 'users_shifts.schedule_id', '=', 'shift_schedules.id')
                                ->where('users_shifts.user_id', $user->id)
                                ->where('users_shifts.assigned_for_date', $date)
                                ->first();
        
                            $shift_start = $users_shift ? Carbon::parse($users_shift->shift_start)->format('H:i:s') : null;
                            $clock_in = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i:s') : null;
        
                            if ($shift_start) {
                                $locationVisit = LocationVisits::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->orderBy('id', 'asc')
                                    ->first();
        
                                if ($locationVisit && $locationVisit->visit_in) {
                                    $visit_in = Carbon::parse($locationVisit->visit_in)->format('H:i:s');
                                    if ($visit_in < $shift_start) {
                                        $early = Carbon::parse($shift_start)->diff(Carbon::parse($visit_in))->format('%h:%I');
                                        $status = "E-P($early)";
                                    } else {
                                        $status = "P";
                                    }
                                } else {
                                    $checkin = Checkin::where('user_id', $user->id)
                                        ->where('date', $date)
                                        ->where('type', 'in')
                                        ->orderBy('id', 'asc')
                                        ->first();
        
                                    if ($checkin) {
                                        $checkin_time = Carbon::parse($checkin->time)->format('H:i:s');
                                        if ($checkin_time < $shift_start) {
                                            $early = Carbon::parse($shift_start)->diff(Carbon::parse($checkin_time))->format('%h:%I');
                                            $status = "E-P($early)";
                                        } else {
                                            $status = "P";
                                        }
                                    } elseif ($clock_in && $clock_in < $shift_start) {
                                        $early = Carbon::parse($shift_start)->diff(Carbon::parse($clock_in))->format('%h:%I');
                                        $status = "E-P($early)";
                                    } else {
                                        $status = "P";
                                    }
                                }
                            } else {
                                $status = "P";
                            }
                        } elseif ($attendance && $attendance->status && $attendance->status->name == 'Absent') {
                            $status = "A";
                        } elseif ($attendance && $attendance->status) {
                            $status = $attendance->status->name ?? "NA";
                        } else {
                            $status = "";
                        }
                    }
        
                    $data[] = $status;
                }
        
                $attendances[] = $data;
            }
        
            // Prepare headers
            $headers = [
                __trans('employee_id'),
                __trans('name'),
            ];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $headers[] = $i;
            }
        
            $exportExcel = $attendances;
            $filePath = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
        
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($attendances as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $storagefilePath,$email_user));
        
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Salary Increments Monthly Email") {

            $query = DB::table('user_salary_increments')
                ->select(
                    'user_salary_increments.*',
                    'departments.name as department_name',
                    'users.name as user_name',
                    'users.employee_id'
                )
                ->join('users', 'user_salary_increments.user_id', '=', 'users.id')
                ->join('departments', 'users.department_id', '=', 'departments.id')
                ->whereBetween('increment_date', [$startOfPreviousMonth, $endOfPreviousMonth]);
        
            if ($email_user->hasRole('employee')) {
                $query->where('user_salary_increments.user_id', $email_user->id);
            }
        
            $increments = $query->get();
        
            $headers = [
                __trans('employee_id'),
                __trans('employee_name'),
                __trans('department'),
                __trans('before_increment'),
                __trans('increment'),
                __trans('after_increment'),
                __trans('increment_date'),
            ];
        
            $exportExcel = [];
        
            foreach ($increments as $i => $data) {
                $exportExcel[] = [
                    $data->employee_id,
                    $data->user_name,
                    $data->department_name,
                    $data->before_increment,
                    $data->increment,
                    $data->after_increment,
                    Carbon::parse($data->increment_date)->toDateString(),
                ];
            }
        
            $filePath  = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($exportExcel as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $storagefilePath,$email_user));
        
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Expense Monthly Email") {

            $query = DB::table('expenses')
                ->select(
                    'expenses.*',
                    'expense_types.name as expense_type_name',
                    'users.name as user_name',
                    'users.employee_id',
                    'creator.name as creator_name'
                )
                ->join('users', 'expenses.user_id', '=', 'users.id')
                ->join('users as creator', 'expenses.created_by', '=', 'creator.id')
                ->join('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id')
                ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
                ->orderBy('date', 'desc');
        
            if ($email_user->hasRole('employee')) {
                $query->where('expenses.user_id', $email_user->id);
            }
        
            $expenses = $query->get();
        
            $headers = [
                __trans('user_id'),
                __trans('creator_name'),
                __trans('employee_name'),
                __trans('date'),
                __trans('expense_types'),
                __trans('name'),
                __trans('amount'),
                __trans('remark'),
                __trans('status'),
            ];
        
            $exportExcel = [];
        
            foreach ($expenses as $expense) {
                $exportExcel[] = [
                    $expense->employee_id,
                    $expense->creator_name,
                    $expense->user_name,
                    Carbon::parse($expense->date)->toDateString(),
                    $expense->expense_type_name,
                    $expense->name,
                    number_format($expense->amount, 2),
                    $expense->remark,
                    $expense->status,
                ];
            }
        
            $filePath  = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($exportExcel as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $storagefilePath,$email_user));
        
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Gratuity Accrual Monthly Email") {

            $query = User::whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->where('status', 'active');
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $employees  = $query->get();
            $sl_no = 0;
        
            $gratuities = $employees->map(function ($employee) use ($yesterdaydate, &$sl_no) {
                $gratuity = $employee->calculateGratuity($yesterdaydate);
                $sl_no++;
            
                return [
                    'sl_no'         => $sl_no,
                    'employee_id'   => $employee->employee_id,
                    'employee_name' => $employee->name,
                    'joining_date'  => isset($gratuity['joining_date']) && $gratuity['joining_date'] instanceof \Carbon\Carbon
                                        ? $gratuity['joining_date']->format('Y-m-d')
                                        : 'N/A',
                    'designation'   => $gratuity['designation'] ?? 'N/A',
                    'based_date'    => $yesterdaydate->format('Y-m-d'),
                    'basic_salary'  => isset($gratuity['basic_salary']) ? number_format($gratuity['basic_salary'], 2) : '0.00',
                    'totalgrant'    => isset($gratuity['totalamount']) ? number_format($gratuity['totalamount'], 2) : '0.00',
                ];
            });
            
        
            $totalamount = $gratuities->sum(function ($item) {
                return floatval(str_replace(',', '', $item['totalgrant']));
            });
        
            $gratuities->push([
                'sl_no'         => '',
                'employee_id'   => '',
                'employee_name' => 'Total',
                'joining_date'  => '',
                'designation'   => '',
                'based_date'    => '',
                'basic_salary'  => '',
                'totalgrant'    => number_format($totalamount, 2),
            ]);
        
            $headers = [
                __trans('Sl No'),
                __trans('Emp ID'),
                __trans('Full Name'),
                __trans('DOJ'),
                __trans('Designation'),
                __trans('LWD'),
                __trans('Basic'),
                __trans('Total Grant'),
            ];
        
            $filePath  = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
            foreach ($gratuities as $row) {
                fputcsv($handle, array_values($row));
            }
            fclose($handle);
        
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $gratuities->toArray(), $storagefilePath,$email_user));
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Medical Insurance Accrual Monthly Email") {

            $query = User::with('workDetail')
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                })
                ->where('status', 'active');
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $employees = $query->get();
            $employees_data = [];
        
            $total_annual_premium = 0;
            $total_monthly_premium = 0;
        
            foreach ($employees as $i => $employee) {
                $row = [
                    'employee_id' => $employee->employee_id,
                    'name'        => $employee->name,
                    'medical_insurance_provided' => 'No',
                    'annual_premium'             => '0.00',
                    'monthly_premium'            => '0.00',
                ];
        
                if ($employee->workDetail) {
                    $provided = $employee->workDetail->medical_insurance_provided == 1;
                    $annual   = floatval($employee->workDetail->annual_premium);
                    $monthly  = $provided && $annual > 0 ? $annual / 12 : 0;
        
                    $row['medical_insurance_provided'] = $provided ? "Yes" : "No";
                    $row['annual_premium']             = number_format($annual, 2);
                    $row['monthly_premium']            = number_format($monthly, 2);
        
                    $total_annual_premium  += $annual;
                    $total_monthly_premium += $monthly;
                }
        
                $employees_data[] = $row;
            }
        
            // Summary row
            $employees_data[] = [
                'employee_id'               => '',
                'name'                      => 'Total',
                'medical_insurance_provided' => '',
                'annual_premium'            => number_format($total_annual_premium, 2),
                'monthly_premium'           => number_format($total_monthly_premium, 2),
            ];
        
            $headers = [
                __trans('Emp ID'),
                __trans('Employee Name'),
                __trans('medical_insurance_provided'),
                __trans('annual_premium'),
                __trans('monthly_premium'),
            ];
        
            $filePath  = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
        
            foreach ($employees_data as $row) {
                fputcsv($handle, array_values($row));
            }
        
            fclose($handle);
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $employees_data, $storagefilePath,$email_user));
        
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Air Ticket Accrual Monthly Email") {

            $query = User::with('workDetail')
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                })
                ->where('status', 'active');
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $employees = $query->get();
            $employees_data = [];
            $total_amount = 0;
        
            foreach ($employees as $i => $employee) {
                $row = [
                    'employee_id'          => $employee->employee_id,
                    'name'                 => $employee->name,
                    'policy_name'          => '',
                    'air_ticket_provided'  => 'No',
                    'amount'               => '0.00',
                ];
        
                if ($employee->workDetail && $employee->workDetail->air_ticket_setting_id > 0) {
                    $airticketsetting = AirTicketSetting::find($employee->workDetail->air_ticket_setting_id);
                    if ($airticketsetting) {
                        $request_after_months = $airticketsetting->request_after_months ?: 0;
                        $allowance_amount     = $airticketsetting->allowance_amount ?: 0;
        
                        $monthly_amount = ($request_after_months > 0) ? $allowance_amount / $request_after_months : 0;
        
                        $row['policy_name']         = $airticketsetting->policy_name;
                        $row['air_ticket_provided'] = 'Yes';
                        $row['amount']              = number_format($monthly_amount, 2);
        
                        $total_amount += $monthly_amount;
                    }
                }
        
                $employees_data[] = $row;
            }
        
            // Summary row
            $employees_data[] = [
                'employee_id'          => '',
                'name'                 => 'Total',
                'policy_name'          => '',
                'air_ticket_provided'  => '',
                'amount'               => number_format($total_amount, 2),
            ];
        
            $headers = [
                __trans('Emp ID'),
                __trans('Employee Name'),
                __trans('policy_name'),
                __trans('air_ticket_provided'),
                __trans('amount'),
            ];
        
            $filePath  = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
        
            foreach ($employees_data as $row) {
                fputcsv($handle, array_values($row));
            }
        
            fclose($handle);
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $employees_data, $storagefilePath,$email_user));
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Leave Salary Accrual Monthly Email") {

            $query = User::with('salary')
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                })
                ->where('status', 'active');
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $employees = $query->get();
            $employees_data = [];
            $totalamount = 0;
        
            foreach ($employees as $i => $employee) {
                $row = [
                    'employee_id' => $employee->employee_id,
                    'name'        => $employee->name,
                    'amount'      => '0.00',
                ];
        
                if ($employee->salary && $employee->salary->basic > 0 && getSetting('leave_salary') == 'yes') {
                    $fixed_allowance = $employee->salary->fixed_allowances ? json_decode($employee->salary->fixed_allowances, true) : [];
        
                    $hra              = intval($fixed_allowance['housing_allowance'] ?? 0);
                    $travel_allowance = intval($fixed_allowance['transportation_allowance'] ?? 0);
                    $other_allowance  = intval($fixed_allowance['other_allowance'] ?? 0);
                    $food_allowance   = intval($employee->salary->food_allowance ?? 0);
        
                    $amount = 0;
                    switch (getSetting('salary_paid_on')) {
                        case 'gross':
                            $amount = round(($employee->salary->basic + $hra + $food_allowance + $travel_allowance + $other_allowance) / 12, 2);
                            break;
                        case 'basic':
                            $amount = round($employee->salary->basic / 12, 2);
                            break;
                        case 'basic_housing':
                            $amount = round(($employee->salary->basic + $hra) / 12, 2);
                            break;
                    }
        
                    $row['amount'] = number_format($amount, 2);
                    $totalamount += $amount;
                }
        
                $employees_data[] = $row;
            }
        
            // Summary row
            $employees_data[] = [
                'employee_id' => '',
                'name'        => 'Total',
                'amount'      => number_format($totalamount, 2),
            ];
        
            $headers = [
                __trans('Emp ID'),
                __trans('Employee Name'),
                __trans('amount'),
            ];
        
            $filePath  = $emailType . "/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$emailType}");
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $handle = fopen(storage_path('app/' . $filePath), 'w');
            fputcsv($handle, $headers);
        
            foreach ($employees_data as $row) {
                fputcsv($handle, array_values($row));
            }
        
            fclose($handle);
        
            $storagefilePath = storage_path('app/' . $filePath);
            chmod($storagefilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $employees_data, $storagefilePath,$email_user));
                Log::info('Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'data'      => ['email_user' => $email_user, 'emailType' => $emailType],
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
        else if ($emailType == "Accrual Monthly Email") {

            $query = User::whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->where('status', 'active');
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $employees = $query->get();
            $accruals = [];
        
            $sl_no                   = 0;
            $total_basic             = 0;
            $total_hra               = 0;
            $total_travel_allowance  = 0;
            $total_other_allowance   = 0;
            $total_gross             = 0;
            $total_gratuity          = 0;
            $total_leave_salary      = 0;
            $total_air_fair          = 0;
            $total_medical_insurance = 0;
            $total_visa              = 0;
            $total_bonus             = 0;
            $total_total_accruals    = 0;
            $total_month_accruals    = 0;
        
            foreach ($employees as $row => $employee) {
                try {
                    $accruals[$row]["s_no"]             = $row + 1;
                    $accruals[$row]["employee_id"]      = $employee->employee_id;
                    $accruals[$row]["name"]             = $employee->name;
                    $accruals[$row]["department_name"]  = optional($employee->department)->name ?? '-';
                    $accruals[$row]["designation_name"] = optional($employee->designation)->name ?? '-';
        
                    $basic = $hra = $travel_allowance = $other_allowance = $food_allowance = 0;
        
                    if (isset($employee->salary)) {
                        $basic            = $employee->salary->basic ?? 0;
                        $hra              = $employee->salary->hra ?? 0;
                        $travel_allowance = $employee->salary->travel_allowance ?? 0;
                        $other_allowance  = $employee->salary->other_allowance ?? 0;
                        $food_allowance   = $employee->salary->food_allowance ?? 0;
        
                        $fixed_allowance = json_decode($employee->salary->fixed_allowances ?? "{}", true);
        
                        $hra              = (int)($fixed_allowance['housing_allowance'] ?? $hra);
                        $travel_allowance = (int)($fixed_allowance['transportation_allowance'] ?? $travel_allowance);
                        $other_allowance  = (int)($fixed_allowance['other_allowance'] ?? $other_allowance);
                    }
        
                    $total_basic            += $basic;
                    $total_hra              += $hra;
                    $total_travel_allowance += $travel_allowance;
                    $total_other_allowance  += $other_allowance;
        
                    $accruals[$row]["basic"]           = $basic;
                    $accruals[$row]["hra"]             = $hra;
                    $accruals[$row]["travel_allowance"] = $travel_allowance;
                    $accruals[$row]["other_allowance"] = $other_allowance;
        
                    $userpayslipcontroller = new UserPaySlipController();
                    $start_date =  date('Y-m-01', strtotime("$year-$month-01"));
                    $end_date   =  date('Y-m-t', strtotime("$year-$month-01"));
                    $gross = $userpayslipcontroller->getGrossSalary($employee, $month, $year,$start_date,$end_date);
                    $total_gross += $gross;
                    $accruals[$row]["gross"] = $gross;
        
                    $gratuity_array = $employee->calculateGratuity($yesterdaydate);
                    $gratuity = $gratuity_array['totalamount'] ?? 0;
                    $total_gratuity += $gratuity;
                    $accruals[$row]["gratuity"] = $gratuity;
        
                    $leave_salary = 0;
                    if (getSetting('leave_salary') == 'yes') {
                        switch (getSetting('salary_paid_on')) {
                            case 'gross':
                                $leave_salary = round(($basic + $hra + $food_allowance + $travel_allowance + $other_allowance) / 12, 2);
                                break;
                            case 'basic':
                                $leave_salary = round($basic / 12, 2);
                                break;
                            case 'basic_housing':
                                $leave_salary = round(($basic + $hra) / 12, 2);
                                break;
                        }
                    }
        
                    $total_leave_salary += $leave_salary;
                    $accruals[$row]["leave_salary"] = $leave_salary;
        
                    // Airfare
                    $air_fair = 0;
                    if (isset($employee->workDetail) && $employee->workDetail->air_ticket_setting_id > 0) {
                        $airticketsetting = AirTicketSetting::find($employee->workDetail->air_ticket_setting_id);
                        if ($airticketsetting) {
                            $air_fair = number_format($airticketsetting->allowance_amount / $airticketsetting->request_after_months, 2);
                        }
                    }
                    $total_air_fair += $air_fair;
                    $accruals[$row]['air_fair'] = $air_fair;
        
                    // Medical insurance
                    $medical_insurance = 0;
                    if ($employee->workDetail && $employee->workDetail->annual_premium > 0) {
                        $medical_insurance = number_format($employee->workDetail->annual_premium / 12, 2);
                    }
                    $total_medical_insurance += $medical_insurance;
                    $accruals[$row]['medical_insurance'] = $medical_insurance;
        
                    $bonus = $visa = 0;
                    foreach ($employee->all_allowance as $all_allowance) {
                        $title = strtolower($all_allowance->title);
                        $isCurrentMonth = $all_allowance->month_code == $month && $all_allowance->year == $year;
        
                        if ($title == "bonus" || $title == "visa") {
                            $amount = $all_allowance->amount;
                            $value = $all_allowance->allowance_type === 'fixed'
                                ? $amount
                                : ($amount * $all_allowance->percentage_amount) / 100;
        
                            if (!$all_allowance->is_fixed_for_current_month || $isCurrentMonth) {
                                if ($title == "bonus") $bonus = $value;
                                if ($title == "visa")  $visa  = $value;
                            }
                        }
                    }
        
                    $accruals[$row]['bonus'] = $bonus;
                    $total_bonus += $bonus;
        
                    $accruals[$row]['visa'] = $visa;
                    $total_visa += $visa;
        
                    $total_accruals = $gratuity + $leave_salary + $air_fair + $medical_insurance;
                    $total_total_accruals += $total_accruals;
        
                    $month_accruals = $total_accruals + $gross;
                    $total_month_accruals += $month_accruals;
        
                    $accruals[$row]['total_accruals']  = $total_accruals;
                    $accruals[$row]['month_accruals']  = $month_accruals;
        
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
        
            // Add total row
            $accruals[] = [
                "s_no"              => "",
                "employee_id"       => "",
                "name"              => "Total",
                "department_name"   => "",
                "designation_name"  => "",
                "basic"             => $total_basic,
                "hra"               => $total_hra,
                "travel_allowance"  => $total_travel_allowance,
                "other_allowance"   => $total_other_allowance,
                "gross"             => $total_gross,
                "gratuity"          => $total_gratuity,
                "leave_salary"      => $total_leave_salary,
                "air_fair"          => $total_air_fair,
                "medical_insurance" => $total_medical_insurance,
                "visa"              => $total_visa,
                "bonus"             => $total_bonus,
                "total_accruals"    => $total_total_accruals,
                "month_accruals"    => $total_month_accruals,
            ];
        
            $headers = [
                __trans('Sl No'),
                __trans('Emp ID'),
                __trans('Full Name'),
                __trans('Department'),
                __trans('Designation'),
                __trans('Basic'),
                __trans('HRA'),
                __trans('TA'),
                __trans('Other Allow'),
                __trans('Salary Gross'),
                __trans('Gratuity'),
                __trans('Leave Salary'),
                __trans('Air Fare'),
                __trans('Medical Insurance'),
                __trans('Visa'),
                __trans('Bonus'),
                __trans('Total Accruals'),
                __trans('Total' . Carbon::now()->format('F Y')),
            ];
        
            $exportExcel = array_map('array_values', $accruals);
        
            $sanitizedType = Str::slug($emailType, '_');
            $filePath  = "$sanitizedType/monthly_" . date('Y-m-d') . '.csv';
            $directory = storage_path("app/{$sanitizedType}");
        
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $fullPath = storage_path('app/' . $filePath);
            $handle = fopen($fullPath, 'w');
            fputcsv($handle, $headers);
        
            foreach ($exportExcel as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
            chmod($fullPath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $fullPath,$email_user));
                Log::info('Accrual email sent successfully.', [
                    'date_time'   => now()->toDateTimeString(),
                    'email_user'  => $email_user->email,
                    'employee_count' => count($employees)
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending accrual email: ' . $e->getMessage());
            }
        }
        else if ($emailType === "PH Leave Monthly Email") {

            $query = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
                });
        
            if ($email_user->hasRole('employee')) {
                $query->where("id", $email_user->id);
            }
        
            $users         = $query->get();
            $exportExcel   = [];
            $headers       = ['Employee Name'];
        
            $holidays = Holiday::whereYear('start_date', $startOfPreviousMonth)
                ->orWhereYear('end_date', $endOfPreviousMonth)
                ->get();
        
            $phleavereport = PHLeaveReport::get();
        
            foreach ($users as $i => $user) {
                $employeeName = $user->name;
                $department   = optional($user->department)->name ?? '-';
                $exportExcel[$i]['Employee Name'] = $employeeName . ' (' . $department . ')';
        
                foreach ($holidays as $holiday) {
                    $holidayStart = Carbon::parse($holiday->start_date)->toDateString();
                    $holidayEnd   = Carbon::parse($holiday->end_date)->toDateString();
                    $diffInDays   = Carbon::parse($holidayStart)->diffInDays(Carbon::parse($holidayEnd)) + 1;
        
                    $columnHeader = $holiday->detail . ' (' . $diffInDays . ')';
                    if ($i === 0) {
                        $headers[] = $columnHeader;
                    }
        
                    $phcount = $phleavereport->where('user_id', $user->id)
                                             ->where('holiday_id', $holiday->id)
                                             ->count();
        
                    $exportExcel[$i][$columnHeader] = $phcount;
                }
            }
        
            // Safe file path handling
            $sanitizedType = Str::slug($emailType, '_');
            $fileName      = "monthly_" . date('Y-m-d') . '.csv';
            $filePath      = "{$sanitizedType}/{$fileName}";
            $directory     = storage_path("app/{$sanitizedType}");
        
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        
            $storageFilePath = storage_path("app/{$filePath}");
        
            $handle = fopen($storageFilePath, 'w');
            fputcsv($handle, $headers);
        
            foreach ($exportExcel as $row) {
                fputcsv($handle, array_values($row));
            }
        
            fclose($handle);
            chmod($storageFilePath, 0644);
        
            try {
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $storageFilePath,$email_user));
        
                Log::info('PH Leave Monthly Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'user'      => $email_user->email,
                    'type'      => $emailType,
                    'file'      => $filePath,
                ]);
        
            } catch (\Exception $e) {
                Log::error('Error sending PH Leave Monthly Email: ' . $e->getMessage());
            }
        } else if ($emailType === "Leave Balance Monthly Email") {

            $users = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
                })->get();
            
            $exportExcel = [];
            $headers = [];
            $types = LeaveType::get(['id', 'name', 'days']);

            foreach ($users as $i => $user) {
                $exportExcel[$i]['Employee Name'] = $user->name . ' (' . $user->employee_id . ')';

                if ($i == 0) {
                    $headers[] = 'Employee Name';
                }

                foreach ($types as $type) {
                    if ($i == 0) {
                        $headers[] = $type->name;
                    }

                    $exportExcel[$i][$type->name] = calculatePendingLeave($type, $user->id);
                }
            }

            $sanitizedType = Str::slug($emailType, '_');  // like "leave_monthly"
            $fileName = "monthly_" . date('Y-m-d') . '.csv';
            $filePath = "{$sanitizedType}/{$fileName}";
            $directory = storage_path("app/{$sanitizedType}");

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $storageFilePath = storage_path("app/{$filePath}");
            $handle = fopen($storageFilePath, 'w');

            fputcsv($handle, $headers); // write header row
            foreach ($exportExcel as $row) {
                fputcsv($handle, array_values($row));
            }
            fclose($handle);
            chmod($storageFilePath, 0644);
            try {

                // Mail::to("bodarmanish777@gmail.com")->send(new SendMail($emailType, "monthly", $exportExcel, $storageFilePath,$email_user));
                Mail::to($email_user->email)->send(new SendMail($emailType, "monthly", $exportExcel, $storageFilePath,$email_user));
        
                Log::info('Leave Balance Monthly Email sent successfully.', [
                    'date_time' => now()->toDateTimeString(),
                    'user'      => $email_user->email,
                    'type'      => $emailType,
                    'file'      => $filePath,
                ]);
        
            } catch (\Exception $e) {
                Log::error('Error sending Leave Balance Monthly Email: ' . $e->getMessage());
            }
        }
        
    }
}
