<?php
namespace App\Imports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Payroll\Entities\UserDeduction;

class DeductionImport implements ToCollection, WithHeadingRow
{
    use Importable;

    protected $failedRows = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            // ✅ Employee ID validation
            if (empty($row['employee_id'])) {
                $row['error']       = 'Employee ID missing';
                $this->failedRows[] = $row;
                continue;
            }

            $user = User::with('salary')
                ->where('employee_id', $row['employee_id'])
                ->first();

            // ✅ User validation
            if (! $user || ! $user->salary) {
                $row['error']       = 'User or salary not found';
                $this->failedRows[] = $row;
                continue;
            }

            // ✅ Month parsing
            try {
                $monthNumber = is_numeric($row['month'])
                    ? $row['month']
                    : Carbon::parse($row['month'])->format('n');
            } catch (\Exception $e) {
                $row['error']       = 'Invalid month format';
                $this->failedRows[] = $row;
                continue;
            }

            foreach ($row as $key => $value) {

                if (! str_contains($key, '_amount')) {
                    continue;
                }

                $title = str_replace('_amount', '', $key);

                $typeColumn  = $title . '_type';
                $fixedColumn = $title . '_monthly_fixed';

                $type   = strtolower($row[$typeColumn] ?? null);
                $amount = $value;

                $existing = UserDeduction::where([
                    'user_id'    => $user->id,
                    'salary_id'  => $user->salary->id,
                    'title'      => ucwords(str_replace('_', ' ', $title)),
                    'month_code' => $monthNumber,
                    'year'       => $row['year'],
                ])->first();

                if (! $amount || $amount <= 0) {
                    if ($existing) {
                        $existing->delete();
                    }
                    continue;
                }

                // ✅ Skip empty amount
                if (! $amount) {
                    continue;
                }

                // ✅ Type validation
                if (! in_array($type, ['fixed', 'percentage'])) {
                    $row['error']       = "{$title} type must be 'fixed' or 'percentage'";
                    $this->failedRows[] = $row;
                    continue;
                }

                // ✅ Monthly fixed validation
                if (! in_array(strtolower($row[$fixedColumn] ?? 'no'), ['yes', 'no'])) {
                    $row['error']       = "{$title} monthly fixed must be 'yes' or 'no'";
                    $this->failedRows[] = $row;
                    continue;
                }

                // ✅ Amount validation
                if (! is_numeric($amount)) {
                    $row['error']       = "{$title} amount must be numeric";
                    $this->failedRows[] = $row;
                    continue;
                }
                if (! in_array($type, ['fixed', 'percentage'])) {

                    $row['error']       = "{$title} type must be 'fixed' or 'percentage'";
                    $this->failedRows[] = $row;
                    continue;
                }
                if (! in_array(strtolower($row[$fixedColumn] ?? 'no'), ['yes', 'no'])) {

                    $row['error']       = "{$title} monthly fixed must be 'yes' or 'no'";
                    $this->failedRows[] = $row;
                    continue;
                }

                $monthlyFixed = strtolower($row[$fixedColumn] ?? 'no');

                // ✅ Percentage calculation
                $percentageAmount = 0;

                if ($type === 'percentage' && $user->salary->basic) {
                    $percentageAmount = ($user->salary->basic * $amount) / 100;
                }

                // ✅ Insert or update
                UserDeduction::updateOrCreate(

                    [
                        'user_id'    => $user->id,
                        'salary_id'  => $user->salary->id,
                        'title'      => ucwords(str_replace('_', ' ', $title)),
                        'month_code' => $monthNumber,
                        'year'       => $row['year'],
                    ],

                    [
                        'date'                       => $row['date'] ?? date('Y-m-d'),
                        'deduction_type'             => $type,
                        'amount'                     => $amount,
                        'percentage_amount'          => $percentageAmount,
                        'is_fixed_for_current_month' => $monthlyFixed === 'yes' ? 1 : 0,
                    ]
                );
            }
        }
    }

    public function getFailedRows()
    {
        return $this->failedRows;
    }
}
