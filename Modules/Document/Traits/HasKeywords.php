<?php
namespace Modules\Document\Traits;

use App\Enums\Document;
use App\Enums\Gender;
use App\Enums\MartialStatus;
use App\Models\EmployeeWorkingDay;
use Illuminate\Support\Str;
use Modules\Payroll\Entities\UserSalaryAllowance;

trait HasKeywords
{
    public function getKeyWordList()
    {
        return [
            '[[title]]',
            '[[nationality]]',
            '[[name]]',
            '[[first_name]]',
            '[[last_name]]',
            '[[department]]',
            '[[designation]]',
            '[[joining_date]]',
            '[[salary]]',
            '[[salary_in_words]]',
            '[[current_date]]',
            '[[passport_number]]',
            '[[currency_symbol]]',
            '[[currency]]',
            '[[today]]',
            '[[bank_name]]',
            '[[total_working_days]]',
            '[[logo]]',
            '[[small_logo]]',
            '[[sign]]',
            '[[header]]',
            '[[footer]]',
            '[[iban]]',
            '[[companydoc_logo]]', '[[companydoc_small_logo]]', '[[companydoc_sign]]', '[[companydoc_header]]', '[[companydoc_footer]]', '[[emirates_id_number]]',
        ];
    }

    /**
     * Replace the keyword with the actual word
     */
    private function getReplacementText($word): string | null
    {
        $replace  = str_replace(["[", "]"], '', $word);
        $callback = "get" . Str::studly($replace) . "Value";
        return self::$callback();
    }

    /**
     * Return the user nationality value
     */
    protected function getNationalityValue()
    {
        return $this->user->profile->country->name ?? '';
    }

    /**
     * Return the user name value
     */
    protected function getNameValue()
    {
        return $this->user->name ?? '';
    }

    /**
     * Return the user first name value
     */
    protected function getFirstNameValue()
    {
        return $this->user->first_name ?? '';
    }

    /**
     * Return the user last name value
     */
    protected function getLastNameValue()
    {
        return $this->user->last_name ?? '';
    }

    /**
     * Return the user department value
     */
    protected function getDepartmentValue()
    {

        return $this->user->department->name ?? '';
    }

    /**
     * Return the user designation value
     */
    protected function getDesignationValue()
    {
        return $this->user->designation->name ?? '';
    }

    /**
     * Return the user joining date value
     */
    protected function getJoiningDateValue()
    {
        return $this->user->workDetail?->joining_date->format(config('project.date_format')) ?? '';
    }

    /**
     * Return the today date value
     */
    protected function getTodayValue()
    {
        return now()->format(config('project.date_format')) ?? '';
    }
    /**
     * Return the user salary value
     */
    protected function getSalaryValue()
    {
        //return  rand(100000, 999999);
        return $this->getGrossSalary($this->user, '', '');
    }

    /**
     * Return the user salary in word value
     */
    public function getSalaryInWordsValue()
    {
        $gross_salary = $this->getGrossSalary($this->user, '', '');

        return numberToWord($gross_salary);
    }

    /**
     * Return the user Passwor Number
     */
    public function getPassportNumberValue()
    {
        $password = $this->user->documents()->where(
            ['type' => Document::Passport]
        )->first();

        return $password?->serial_number ?? __trans('not_available');
    }

    /**
     * Return the current date  value
     */
    public function getCurrentDateValue()
    {
        return now()->format(config('project.date_format'));
    }

    /**
     * Return title according to gender
     */
    public function getTitleValue(): string | null
    {
        $title = '';
        //// check if user is male
        if ($this->user->profile->gender == Gender::Male->value) {
            $title = 'Mr';
        }
        //// check if user is female
        if ($this->user->profile->gender == Gender::Female->value) {
            if ($this->user->profile->martial_status == MartialStatus::Single) {
                $title = 'Miss';
            } else {
                $title = 'Mr';
            }
        }

        return $title;
    }

    /**
     * Return Project currency symbol
     */
    public function getCurrencySymbolValue(): string
    {
        return config('project.currency_symbol');
    }

    /**
     * Return Project currency symbol value
     */
    public function getCurrencyValue(): string
    {
        return config('project.currency');
    }

    public function getGrossSalary($user, $month, $year)
    {
        $current_month   = $month ? $month : date('m');
        $current_year    = $year ? $year : date('Y');
        $total_allowance = 0;
        $total_deduction = 0;

        $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year);
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year);

        $basic_salary = $user->salary ? $user->salary->basic : 0;

        $fixed_entity_allowance = (isset($user->salary->fixed_allowances) && ! empty($user->salary->fixed_allowances)) ? json_decode($user->salary->fixed_allowances, true) : [];
        $fixed_entity_allowance = array_sum($fixed_entity_allowance);

        // $total_allowance = $monthly_fixed['total_allowance'] + $monthly_not_fixed['total_allowance'] + $fixed_entity_allowance;
        $total_allowance = $monthly_fixed['total_allowance'] + $fixed_entity_allowance;

        $total = $basic_salary + $total_allowance;

        return $total ? $total : __trans('not_set');
    }

    /**
     * get monthly based allowance & deduction calculation.
     */

    public function monthlyfixedExpensesCalculation($user, $month, $year)
    {
        $current_month        = $month ? $month : date('m');
        $current_year         = $year ? $year : date('Y');
        $fixed_allowance      = 0;
        $percentage_allowance = 0;
        $total_allowance      = 0;

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'month_code'                 => $current_month,
            'year'                       => $current_year,
            'is_fixed_for_current_month' => 1,
        ])->sum('percentage_amount');

        $total_allowance = $fixed_allowance + $percentage_allowance;
        $result          = [
            'total_allowance' => $total_allowance,
        ];

        return $result;
    }
    /**
     * get allowance & deduction calculation which are not restricted by month or any condition.
     */
    public function monthlynotfixedExpensesCalculation($user, $month, $year)
    {
        $fixed_allowance      = 0;
        $percentage_allowance = 0;
        $total_allowance      = 0;

        $fixed_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'fixed',
            'is_fixed_for_current_month' => 0,
        ])->sum('amount');

        $percentage_allowance = UserSalaryAllowance::where([
            'user_id'                    => $user->id,
            'allowance_type'             => 'percentage',
            'is_fixed_for_current_month' => 0,
        ])->sum('percentage_amount');

        $total_allowance = $fixed_allowance + $percentage_allowance;
        $result          = [
            'total_allowance' => $total_allowance,
        ];

        return $result;
    }

    protected function getBankNameValue()
    {
        return $this->user->bankDetail->bank_name ?? '';
    }

    protected function getLogoValue()
    {
        return $this->user->department->logo
            ? '<img src="' . asset('storage/' . $this->user->department->logo) . '" style="max-height: 100px;">'
            : '';
    }

    protected function getSmallLogoValue()
    {
        return $this->user->department->small_logo
            ? '<img src="' . asset('storage/' . $this->user->department->small_logo) . '" style="max-height: 80px;">'
            : '';
    }

    protected function getSignValue()
    {
        return $this->user->department->sign
            ? '<img src="' . asset('storage/' . $this->user->department->sign) . '" style="max-height: 60px;">'
            : '';
    }
    protected function getHeaderValue()
    {
        return $this->user->department->header
            ? '<img src="' . asset('storage/' . $this->user->department->header) . '" style="max-height: 200px;">'
            : '';
    }
    protected function getFooterValue()
    {
        return $this->user->department->footer
            ? '<img src="' . asset('storage/' . $this->user->department->footer) . '" style="max-height: 200px;">'
            : '';
    }
    protected function getIbanValue()
    {
        return $this->user->bankDetail->iba_number ?? '';
    }

    protected function getTotalWorkingDaysValue()
    {
        $user = $this->user;

        // Derive month/year from current date OR from user payroll context
        $month_code = now()->format('m');
        $year       = now()->format('Y');

        // Try fetching from EmployeeWorkingDay table
        $working_days = EmployeeWorkingDay::where([
            'month_code' => $month_code,
            'year'       => $year,
            'user_id'    => $user->id,
        ])->value('total_working_days');

        // If attendance_base_payroll is ON → use attendance count
        if (getSetting('attendance_base_payroll') == 'true') {

            // Payroll start & end range (same as your payslip logic)
            $start_date = now()->startOfMonth()->toDateString();
            $end_date   = now()->endOfMonth()->toDateString();

            $working_days = $user->attendances()
                ->whereIn('status', [
                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                    \Modules\Attendance\Enums\AttendanceStatus::Late,
                    \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                ])
                ->whereBetween('date', [$start_date, $end_date])
                ->count();
        }

        return $working_days ?? 0;
    }
     protected function getCompanydocLogoValue()
    {
        return $this->user->companyDocument?->logo
            ? '<img src="' . asset('uploads/companydocument/' . $this->user->companyDocument->logo) . '" style="max-height: 100px;">'
            : '';
    }

    protected function getCompanydocSmallLogoValue()
    {
        return $this->user->companyDocument?->small_logo
            ? '<img src="' . asset('uploads/companydocument/' . $this->user->companyDocument->small_logo) . '" style="max-height: 80px;">'
            : '';
    }

    protected function getCompanydocSignValue()
    {
        return $this->user->companyDocument?->sign
            ? '<img src="' . asset('uploads/companydocument/' . $this->user->companyDocument->sign) . '" style="max-height: 60px;">'
            : '';
    }

    protected function getCompanydocHeaderValue()
    {
        return $this->user->companyDocument?->header
            ? '<img src="' . asset('uploads/companydocument/' . $this->user->companyDocument->header) . '" style="width:100%;">'
            : '';
    }

    protected function getCompanydocFooterValue()
    {
        return $this->user->companyDocument?->footer
            ? '<img src="' . asset('uploads/companydocument/' . $this->user->companyDocument->footer) . '" style="width:100%;">'
            : '';
    }
}
