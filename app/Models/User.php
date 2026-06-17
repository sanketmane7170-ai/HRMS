<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Events\OnlineStatusChanged;
use App\Models\UserWorkDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Modules\Asset\Traits\HasAsset;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Traits\HasAttendance;
use Modules\CompanyDocument\Entities\CompanyDocument;
use Modules\Document\Entities\DocumentRequest;
use Modules\Expense\Entities\Expense;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Payroll\Entities\EmployeeTaxUser;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Traits\HasPayroll;
use Modules\Shift\Entities\UsersShift;
use Modules\Warning\Entities\UserWarning;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Modules\Attendance\Enums\WorkStatus;
use Modules\Attendance\Entities\WorkStatusLog;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles, HasAsset, HasAttendance, HasPayroll;

    // const ROLE_ADMIN        = 1;
    // const ROLE_EMPLOYEE     = 2;
    // const ROLE_HR           = 3;
    // const ROLE_LM           = 13;
    // const ROLE_DEPT_MANAGER = 7;
    const ROLE_SUPER_ADMIN  = 'superadmin';
    const ROLE_ADMIN        = 'admin';
    const ROLE_EMPLOYEE     = 'employee';
    const ROLE_HR           = 'hr';
    const ROLE_LM           = 'linemanager';
    const ROLE_DEPT_MANAGER = 'departmentmanager';
    const STATUS_ACTIVE     = 'active';
    const STATUS_INACTIVE   = 'in-active';
    const STATUS_BANNED     = 'banned';
    const STATUS_RESIGNED   = 'resigned';
    const STATUS_TERMINATED = 'terminated';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profile_image',
        'department_id',
        'division_id',
        'designation_id',
        'employee_id',
        'longitude',
        'latitude',
        'ftoken',
        'is_previous_leave',
        'company_document_id',
        'version_info',
        'device_info',
        'online',
        'username',
        'status',
        'biometric_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'status_updated_at' => 'datetime',
        'work_status'       => WorkStatus::class,
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function aiPhoto(): HasOne
    {
        return $this->hasOne(AiPhoto::class);
    }

    public function workDetail(): HasOne
    {
        return $this->hasOne(UserWorkDetail::class);
    }

    public function indianPayrollProfile(): HasOne
    {
        return $this->hasOne(\Modules\IndianPayroll\Entities\EmployeeProfile::class);
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(UserDependent::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }
    public function documentrequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }
    public function warning(): HasMany
    {
        return $this->hasMany(UserWarning::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }
    public function employeeTaxUsers()
    {
        return $this->hasMany(EmployeeTaxUser::class);
    }
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function lineManager()
    {
        // return $this->hasMany(UserWorkDetail::class, 'report_to_id');
        return UserWorkDetail::whereJsonContains('report_to_ids', $this->id)->get();
    }
    public function companyDocument()
    {
        return $this->belongsTo(CompanyDocument::class, 'company_document_id');
    }

    /**
     * Return first name of the current user
     */
    public function getFirstNameAttribute()
    {
        $names    = explode(' ', $this->name);
        $lessthan = count($names) - 1;
        $count    = 0;
        $get_name = '';
        while ($count < $lessthan) {
            $get_name = $get_name . ' ' . $names[$count];
            $count++;
        }
        return trim($get_name) ?? '';
    }

    /**
     * Return last name of the current user
     */
    public function getLastNameAttribute()
    {
        $names    = explode(' ', $this->name);
        $lessthan = count($names) - 1;
        return $names[$lessthan] ?? '';
    }

    public static function generateUserName($name): string
    {
        $username = Str::lower(Str::slug($name));
        if (User::where('username', '=', $username)->exists()) {
            $uniqueUserName = $username . '-' . Str::lower(Str::random(4));
            $username       = self::generateUserName($uniqueUserName);
        }
        return $username;
    }

    public function getCurrentRole(): ?Role
    {
        $assignedRole = $this->getRoleNames();
        if (isset($assignedRole) && ! empty($assignedRole)) {
            if (isset($assignedRole[0])) {
                $role = Role::where('name', $assignedRole[0])->first();
            } else {
                $role = null;
            }
        } else {
            $role = null;
        }
        return $role;
    }

    /**
     * Return User Profile Image
     */
    public function getProfileImage()
    {
        //$profileImage = asset('assets/backend/img/profiles/avatar-01.jpg');
        $gender = UserProfile::where('user_id', $this->id)->whereNotIn('id', [self::ROLE_ADMIN])->value('gender');
        if ($gender == 'Female') {
            $profileImage = asset('assets/backend/img/profiles/girl-avtar.png');
        } else {
            $profileImage = asset('assets/backend/img/profiles/boy-avtar.png');
        }

        // if ($this->profile_image) {
        //     $profileImage = asset('uploads/profile/' . $this->profile_image);
        //     $profileImage = asset('uploads/profile/' . $this->profile_image);
        // }
        if ($this->profile_image) {

            $profileImage = str_contains($this->profile_image, 'uploads/profile/')
                ? asset($this->profile_image)
                : asset('uploads/profile/' . $this->profile_image);

        }
        return $profileImage;
    }

    /**
     * Return true if user in probation and false if not in probation
     */
    public function isInProbation(): bool
    {
        if (strtotime($this->workDetail->probation_end_date) > strtotime(now()->toDateString())) {
            if (getSetting('leave_probation_module') == 'true') {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Return true if user is in notice period (has departure date set and future)
     */
    public function isInNoticePeriod(): bool
    {
        $settlement = $this->settlement;

        if ($settlement && $settlement->departure_date) {
            $departureDate = Carbon::parse($settlement->departure_date);
            $today         = Carbon::today();

            // User is in notice period if departure date is in future
            return $departureDate->isFuture() || $departureDate->isToday();
        }

        return false;
    }

    /**
     * Get the user's settlement record
     */
    public function settlement(): HasOne
    {
        return $this->hasOne(UserSettlement::class);
    }

    /**
     * Scope a query to only include user having birthday this month.
     */
    public function scopeNotAdmin(Builder $query)
    {
        $query->whereDoesntHave('roles', function ($query) {
            return $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        });
    }

    // public function scopeVisibleForAuthUser(Builder $query, $user)
    // {
    //     // Implementation for visibility based on role
    //     if ($user->hasRole('Admin') || $user->hasRole('HR')) {
    //         return $query;
    //     }
    //     // Default behavior for other roles (e.g., Department Manager, Employee)
    //     // Adjust logic here if specific filtering is required
    //     return $query;
    // }

    public function bankDetail(): HasOne
    {
        return $this->hasOne(UserBankDetail::class);
    }

    public function all_overtime(): HasMany
    {
        return $this->hasMany(UserOvertime::class, 'user_id', 'id');
    }

    public function all_allowance(): HasMany
    {
        return $this->hasMany(UserSalaryAllowance::class, 'user_id', 'id');
    }

    public function all_deduction(): HasMany
    {
        return $this->hasMany(UserDeduction::class, 'user_id', 'id');
    }

    public function monthlyNotFixAllowance()
    {
        return $this->hasMany(UserSalaryAllowance::class, 'user_id', 'id')
            ->where('is_fixed_for_current_month', 0);
    }

    public function monthlyFixAllowance()
    {
        return $this->hasMany(UserSalaryAllowance::class, 'user_id', 'id')
            ->where('is_fixed_for_current_month', 1);
    }

    public function monthlyNotFixDeduction()
    {
        return $this->hasMany(UserDeduction::class, 'user_id', 'id')
            ->where('is_fixed_for_current_month', 0);
    }

    public function monthlyFixDeduction()
    {
        return $this->hasMany(UserDeduction::class, 'user_id', 'id')
            ->where('is_fixed_for_current_month', 1);
    }

    public function shifts(): HasMany
    {
        return $this->HasMany(UserShift::class);
    }

    public function user_shift(): HasMany
    {
        return $this->HasMany(UsersShift::class);
    }

    public function assigned_shifts()
    {
        return $this->hasMany(UsersShift::class, 'user_id');
    }

    public function emergencyContacts(): hasOne
    {
        return $this->hasOne(UserEmergencyContact::class);
    }

    public function shift_data()
    {
        return $this->hasManyThrough(
            ShiftSchedule::class,
            UsersShift::class,
            'user_id',
            'id',
            'id',
            'schedule_id'
        );
    }

    public function without_attendance_days()
    {
        return $this->hasOne(EmployeeWorkingDay::class);
    }

    public function calculateGratuity($chosenDate)
    {
        if (! $this->workDetail || ! $this->workDetail?->joining_date) {
            return [
                'designation'         => optional($this->designation)->name ?? '',
                'joining_date'        => null,
                'based_date'          => $chosenDate,
                'basic_salary'        => round(optional($this->salary)->basic ?? 0),
                'day'                 => 0,
                'month'               => 0,
                'year'                => 0,
                'below5year'          => 0,
                'above5year'          => 0,
                'below5yearsOfAmount' => 0,
                'above5yearsOfAmount' => 0,
                'totalamount'         => 0,
                'remarks'             => 'Joining date not available',
            ];
        }
        // $chosenDate = Carbon::parse("2018-10-23");
        $joiningDate = $this->workDetail?->joining_date;
        $designation = $this->designation->name;
        // $joiningDate = Carbon::parse("2024-11-25");
        $basicSalary = isset($this->salary) ? $this->salary->basic : 0;
        // $basicSalary = 11000;

        $below5yearsOfAmount = 0;
        $above5yearsOfAmount = 0;
        $totalamount         = 0;
        // $days_based = "0";
        $day = $joiningDate->diffInDays($chosenDate);
        $day++;
        $month = $day / (365 / 12);
        $year  = ($day / (365 / 12)) / 12;
        // $year = $joiningDate->diffInYears($chosenDate);
        // $below5year = $joiningDate->diffInYears($chosenDate);
        $below5year = $year;
        $above5year = 0;
        if ($year > 5) {
            $above5year = $year - 5;
            $below5year = 5;
        }
        // $month = $joiningDate->diffInMonths($chosenDate);
        // No gratuity if less than 1 year
        if ($day < 365) {
            return
                [
                'designation'         => $designation,
                'joining_date'        => $joiningDate,
                'based_date'          => $chosenDate,
                'basic_salary'        => round($basicSalary),
                'day'                 => round($day),
                'month'               => round($month, 2),
                'year'                => round($year, 2),
                // 'days_based' => round($days_based),
                'below5year'          => round($below5year, 2),
                'above5year'          => round($above5year, 2),
                'below5yearsOfAmount' => round($below5yearsOfAmount, 2),
                'above5yearsOfAmount' => round($above5yearsOfAmount, 2),
                'totalamount'         => round($totalamount, 2),
                'remarks'             => 'No gratuity applicable for less than 1 year of service',
            ];
        }
        // $yearsWorked = floor($day / 365);
        // $remainingDays = $day % 365;

        // // Initialize gratuity amount
        // $gratuityAmount = 0;

        // // Gratuity for years worked
        // if ($yearsWorked <= 5) {
        //     $days_based = 21;
        //     $gratuityAmount += $dailySalary * 21 * $yearsWorked; // 21 days per year
        // } else {
        //     $days_based = 21 * 5;
        //     $gratuityAmount += $dailySalary * 21 * 5; // 21 days for first 5 years
        //     $days_based += 30 * $yearsWorked - 5;
        //     $gratuityAmount += $dailySalary * 30 * ($yearsWorked - 5); // 30 days for years beyond 5
        // }

        // // Gratuity for remaining days
        // if ($yearsWorked < 5) {
        //     $days_based += ($remainingDays / 21);
        //     $gratuityAmount += ($dailySalary * 21 / 365) * $remainingDays; // Pro-rata 21 days
        // } else {
        //     $days_based += ($remainingDays / 30);
        //     $gratuityAmount += ($dailySalary * 30 / 365) * $remainingDays; // Pro-rata 30 days
        // }

        // // Apply resignation rules for < 5 years
        // // if ($yearsWorked < 3) {
        // //     $gratuityAmount *= (1 / 3); // 1/3 of entitlement
        // // } elseif ($yearsWorked < 5) {
        // //     $gratuityAmount *= (2 / 3); // 2/3 of entitlement
        // // }
        $below5yearsOfAmount = ((($basicSalary / 30) * 21) * $below5year);
        $above5yearsOfAmount = ((($basicSalary / 30) * 30) * $above5year);
        $totalamount         = $below5yearsOfAmount + $above5yearsOfAmount;

        return [
            'designation'         => $designation,
            'joining_date'        => $joiningDate,
            'based_date'          => $chosenDate,
            'basic_salary'        => round($basicSalary),
            'day'                 => round($day),
            'month'               => round($month, 2),
            'year'                => round($year, 2),
            // 'days_based' => round($days_based),
            'below5year'          => round($below5year, 2),
            'above5year'          => round($above5year, 2),
            'below5yearsOfAmount' => round($below5yearsOfAmount, 2),
            'above5yearsOfAmount' => round($above5yearsOfAmount, 2),
            'totalamount'         => round($totalamount, 2),
            'remarks'             => 'Gratuity calculated as per UAE Labour Law',

        ];
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function directReports()
    {
        return $this->hasMany(UserWorkDetail::class, 'report_to_ids', 'id');
    }

    public function workStatusLogs()
    {
        return $this->hasMany(WorkStatusLog::class);
    }

    public function allReports()
    {
        return $this->hasMany(UserWorkDetail::class, 'report_to_ids', 'id')->with('user.role', 'allReports');
    }
    public function userrole()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
            ->where('model_type', 'App\Models\User')
            ->withPivot('model_type');
    }
    public function getProfileImageUrlAttribute()
    {
        // Check if the user has a custom profile image
        $profilePath = 'uploads/profile/' . $this->profile_image;
        if ($this->profile_image && file_exists(public_path($profilePath))) {
            return asset($profilePath);
        }

        // Use gender-based default profile images if no custom image is set
        $gender = $this->profile ? $this->profile->gender : 'Male';

        // Online fallback images (change these URLs as needed)
        $femaleAvatar  = 'https://cdn-icons-png.flaticon.com/512/2922/2922565.png';
        $maleAvatar    = 'https://cdn-icons-png.flaticon.com/512/2922/2922510.png';
        $defaultAvatar = 'https://cdn-icons-png.flaticon.com/512/149/149071.png';

        return $gender == 'Female' ? $femaleAvatar : $maleAvatar;
    }
    public function setOnline()
    {
        $this->online = true;
        $this->save();

        broadcast(new OnlineStatusChanged($this));
    }

    public function setOffline()
    {
        $this->online = false;
        $this->save();

        broadcast(new OnlineStatusChanged($this));
    }
    public function performanceAppraisals()
    {
        return $this->hasMany(\Modules\Performance\Entities\PerformanceAppraisal::class, 'employee_id');
    }
    public function user_salary(): HasOne
    {
        return $this->hasOne(\Modules\Payroll\Entities\UserSalary::class, 'user_id');
    }
    public function airTicketsDetail(): HasMany
    {
        return $this->hasMany(AirTicketDetail::class);
    }
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    public function scopeVisibleForAuthUser(Builder $query, User $authUser)
    {
        // Super Admin login → hide only Super Admin
        if ($authUser->hasRole(self::ROLE_SUPER_ADMIN)) {
            return $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', self::ROLE_SUPER_ADMIN);
            });
        }

        // Admin OR any other role → hide Super Admin + Admin
        return $query->whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', [
                self::ROLE_SUPER_ADMIN,
                self::ROLE_ADMIN,
            ]);
        });
    }
    public function resignations()
    {
        return $this->hasMany(\Modules\Resignation\Entities\Resignation::class, 'employee_id');
    }

    public function probationLetters()
    {
        return $this->hasMany(ProbationLetter::class);
    }

    public function onboardingRecord()
    {
        return $this->hasOne(\Modules\Onboarding\Entities\OnboardingRecord::class);
    }

    public function visaProcess()
    {
        return $this->hasOne(\Modules\Onboarding\Entities\VisaProcess::class);
    }

    public function complianceRecord()
    {
        return $this->hasOne(\Modules\Onboarding\Entities\ComplianceRecord::class);
    }
    public function operationalReadiness()
    {
        return $this->hasOne(\Modules\Onboarding\Entities\OperationalReadiness::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
