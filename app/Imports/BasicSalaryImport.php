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

class BasicSalaryImport implements ToModel, WithStartRow
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
        $user = User::where('employee_id',$row[0])->first();
        if(isset($row[6])){
            $user->salary()->update([
                'basic' => isset($row[6]) ? $row[6] : ''
            ]);
        }
    }
}
