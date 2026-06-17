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
use App\Models\EmployeeWorkingDay;
use Exception;

class WorkingDayImport implements ToModel, WithStartRow
{
    use Importable;

    protected  $month;
    protected  $year;                                              

    protected $dateFormats = [
        'Y-m-d', // Format used in the CSV file
    ];

    public function __construct($month,$year)
    {
        $this->month = $month; 
        $this->year =  $year; 
    }
    
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
        $month = $this->month;
        $year = $this->year;

        // Find the user by employee_id (index 0)
        if (empty($row[0])) {
            return null;
        }

        $user = User::where('employee_id', $row[0])->first();

        if ($user) {
            // Check if the entry exists for the given month and year
            $attendanceDay = $user->without_attendance_days()->where([
                'month_code' => $month,
                'year' => $year,
            ])->first();

            $totalWorkingDays = $row[6] ?? 0;

            // If the entry exists, update it
            if ($attendanceDay) {
                $attendanceDay->update([
                    'total_working_days' => $totalWorkingDays,
                ]);
            } else {
                // If the entry does not exist, create a new one
                $user->without_attendance_days()->create([
                    'month_code' => $month,
                    'year' => $year,
                    'total_working_days' => $totalWorkingDays,
                ]);
            }
        }
    }
}
