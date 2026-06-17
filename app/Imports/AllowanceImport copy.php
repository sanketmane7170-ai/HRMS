<?php

namespace App\Imports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class AllowanceImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            if (empty($row['employee_id'])) {
                continue;
            }

            $user = User::where('employee_id', $row['employee_id'])->first();

            if (!$user || !$user->salary) {
                continue;
            }

            try {
                $monthNumber = Carbon::createFromFormat('M', $row['month'])->format('n');
            } catch (\Exception $e) {
                continue;
            }

            foreach ($row as $key => $value) {

                // detect allowance columns like hra_amount
                if (str_contains($key, '_amount')) {

                    $title = str_replace('_amount', '', $key);

                    $typeColumn = $title . '_type';
                    $fixedColumn = $title . '_monthly_fixed';

                    $type = $row[$typeColumn] ?? null;
                    $amount = $value ?? 0;
                    $monthlyFixed = $row[$fixedColumn] ?? 'no';

                    if (!$amount) {
                        continue;
                    }

                    $percentageAmount = 0;

                    if ($type === 'percentage' && $user->salary->basic) {
                        $percentageAmount = ($user->salary->basic * $amount) / 100;
                    }

                    $data = [
                        'date' => $row['date'] ?? date('Y-m-d'),
                        'year' => $row['year'] ?? date('Y'),
                        'month_code' => $monthNumber,
                        'title' => ucwords(str_replace('_', ' ', $title)),
                        'allowance_type' => $type,
                        'amount' => $amount,
                        'salary_id' => $user->salary->id,
                        'percentage_amount' => $percentageAmount,
                        'is_fixed_for_current_month' => strtolower($monthlyFixed) === 'yes' ? 1 : 0
                    ];

                    $user->allowance()->create($data);
                }
            }
        }
    }
}
