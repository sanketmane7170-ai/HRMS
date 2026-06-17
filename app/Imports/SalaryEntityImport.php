<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Spatie\Permission\Models\Role;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Country;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Modules\Payroll\Entities\UserSalary;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use App\Models\allTypeOfTransaction;

class SalaryEntityImport implements ToModel, WithStartRow, WithHeadingRow
{
    use Importable;

    protected $dateFormats = [
        'Y-m-d', // Format used in the CSV file
    ];
    
    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {
        if(!array_filter($row)) {
            return null;
         } 
        $user = User::where('employee_id', $row['employee_id'] ?? null)->first();
        if (!$user) {
            return null; // Skip if the user is not found
        }

        // $allowances = SetAllowanceDeducation::select('name', 'type')->get();

        $allowanceData = [];
        $deducationData = [];

        // foreach ($allowances as $allowance) {
        //     $allowanceName = $allowance->name;
        //     $normalizedAllowanceName = str_replace(' ', '_', strtolower($allowanceName)); // Normalize allowance name

        //     if (array_key_exists($normalizedAllowanceName, $row)) {
        //         if ($allowance->type == 1) {
        //             $allowanceData[$allowanceName] = $row[$normalizedAllowanceName];
        //         }

        //         if ($allowance->type == 2) {
        //             $deducationData[$allowanceName] = $row[$normalizedAllowanceName];
        //         }
        //     }
        // }

        $data = [];

        // Use normalized headers like 'basic_salary' from Excel file
        if (isset($row['basic_salary'])) {

            $addtransaction = allTypeOfTransaction::create([
                'user_id' => $user->id,
                'transaction_type' =>'salary',
                'old_value' => $row['basic_salary'] ?? 0,
                'update_value' => $row['basic_salary'] ?? 0,
                'new_value' => $row['basic_salary'] ?? 0,
                'transaction_date' => Carbon::now(),
                'description' => 'update/add this '.$user->name.' basic salary from import, import by this user: '.auth()->user()->name,
            ]);
            
            $data['basic'] = $row['basic_salary'];
        }

        // Merge fixed allowances with dynamic allowances
        $data['fixed_allowances'] = json_encode(array_merge([
            "housing_allowance" => $row['housing_allowance'] ?? '',
            "transportation_allowance" => $row['transportation_allowance'] ?? '',
            "functional_allowance" => $row['functional_allowance'] ?? '',
            "other_allowance" => $row['other_allowance'] ?? '',
            "tips" => $row['tips'] ?? '',
        ], $allowanceData));

        // Merge fixed deductions with dynamic deductions
        $data['fixed_deductions'] = json_encode(array_merge([
            "advance_salary" => $row['advance_salary'] ?? '',
            "loan_deduction" => $row['loan_deduction'] ?? '',
            "other_deduction" => $row['other_deduction'] ?? ''
        ], $deducationData));

        // Update user's salary with merged data
        $userSalary = UserSalary::where('user_id', $user->id)->first();
        if ($userSalary) {  
            $user->salary()->update($data);
        } else {
            $user->salary()->create($data);
        }
            // if(isset($row[6])){
                // $user->salary()->update([
                //     'basic' => isset($row[6]) ? $row[6] : '',
                //     'fixed_allowances' => json_encode([ 
                //         "housing_allowance" => $row[7],
                //         "transportation_allowance" => $row[8], 
                //         "other_allowance" => $row[9], 
                //         "tips" => $row[10] 
                //     ]),
                //     'fixed_deductions' => json_encode([ 
                //         "advance_salary" => $row[11], 
                //         "loan_deduction" => $row[12], 
                //         "other_deduction" =>$row[13]
                //     ])
                // ]);
            // }
        
            // $user->salary()->update(['basic' => isset($row[6]) ? $row[6] : '', ]);
        // }
        // $user->salary()->update($data);
    }
}
