<?php
namespace Modules\Api\Transformers;

use App\Models\EmployeeWorkingDay;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Api\Transformers\AllowanceResource;
use Modules\Api\Transformers\DeductionResource;
use Modules\Api\Transformers\OvertimeResource;
use Modules\Payroll\Traits\SalaryCalculation;

class PaySlipResource extends JsonResource
{
    use SalaryCalculation;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $user = User::with([
            'department',
            'all_overtime'        => function ($query) {
                $query->where('month_code', $this->month_code);
            },
            'monthlyNotFixAllowance',
            'monthlyFixAllowance' => function ($query) {
                $query->where([['month_code', $this->month_code], ['year', $this->year]]);
            },
            'monthlyNotFixDeduction',
            'monthlyFixDeduction' => function ($query) {
                $query->where([['month_code', $this->month_code], ['year', $this->year]]);
            },
        ])->where('id', $this->user_id)->first();

        if (getSetting('attendance_base_payroll') == 'true') {

            $total_net_salary = $this->getTotalNetSalary($user, $this->month_code, $this->year,$this->start_date,$this->end_date);
            $net_salary       = $this->getNetSalaryAsPerAttendance($user, $this->month_code, $this->year,$this->start_date,$this->end_date);

        } else {
            $working_days     = EmployeeWorkingDay::where(['month_code' => $this->month_code, 'year' => $this->year, 'user_id' => $user->id])->value('total_working_days');
            $total_net_salary = $this->getNetSalaryAsPerAttendance_EXTRA($user, $this->month_code, $this->year,$this->start_date,$this->end_date, $working_days);
            $net_salary       = $this->getNetSalaryAsPerAttendance_EXTRA($user, $this->month_code, $this->year,$this->start_date,$this->end_date, $working_days);

        }

        $fixed_allowance = json_decode($user->salary->fixed_allowances, true);
        $fixed_deduction = json_decode($user->salary->fixed_deductions, true);

        return [
            'id'                        => $this->id,
            'employee_id'               => $user->employee_id,
            'name'                      => $user->name,
            'department'                => $user->department?->name ?? 'NA',
            'basic'                     => strval($user->salary->basic),
            'net_salary_in_attendance'  => $net_salary,
            'total_net_salary'          => $total_net_salary,
            'status'                    => $this->status,
            'all_overtime'              => OvertimeResource::collection($user->all_overtime),
            'monthly_fix_allowance'     => AllowanceResource::collection($user->monthlyFixAllowance),
            'monthly_not_fix_allowance' => AllowanceResource::collection($user->monthlyNotFixAllowance),
            'monthly_fix_deduction'     => DeductionResource::collection($user->monthlyFixDeduction),
            'monthly_not_fix_deduction' => DeductionResource::collection($user->monthlyNotFixDeduction),
            'fixed_allowance'           => $fixed_allowance,
            'fixed_deduction'           => $fixed_deduction,
        ];
    }
}
