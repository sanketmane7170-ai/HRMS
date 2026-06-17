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

class MedicalpremiumImport implements ToModel, WithStartRow
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
        if (!array_filter($row)) {
            return null;
        }

        // Row indices: 1 is Employee ID, 3 is medical_insurance_provided (Yes/No), 4 is annual_premium
        if (empty($row[1])) {
            return null;
        }

        $user = User::where('employee_id', $row[1])->first();

        if ($user) {
            $user->workDetail()->update([
                'medical_insurance_provided' => isset($row[3]) ? ($row[3] == "Yes" ? 1 : 0) : 0,
                'annual_premium' => isset($row[4]) ? $row[4] : '0.00'
            ]);
        }
    }
}
