<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\User;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Entities\Attendance;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class UserAttendanceImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $failedRows = [];

    public function model(array $row)
    {
        try {
            // Validate basic columns
            if (empty($row['user_name']) || empty($row['employee_id'])) {
                throw new \Exception('User Name or Employee ID is missing.');
            }

            // Find user by employee_id
            $user = User::where('employee_id', $row['employee_id'])->first();

            if (!$user) {
                throw new \Exception("User not found for employee ID: {$row['employee_id']}");
            }
            // Loop through all date columns (e.g., 2025-10-01, 2025-10-02, etc.)
            foreach ($row as $key => $value) {
                // Skip non-date columns
                if (in_array($key, ['user_name', 'employee_id']) || empty($value)) {
                    continue;
                }

                // Try to parse if it's a date column
                if (preg_match('/^\d{4}[-_]\d{2}[-_]\d{2}$/', $key)) {
                    // Convert underscore to hyphen to match database format
                    $date = str_replace('_', '-', $key);
                    $status = strtolower(trim($value));

                    $updateAttendance = Attendance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $date,
                        ],
                        [
                            'status' => $status,
                            'created_by_id' => Auth::id(),
                            'remark' => 'Updated via bulk import',
                        ]
                    );

                }
            }


        } catch (\Exception $e) {
            Log::error('Attendance import failed: ' . $e->getMessage(), ['row' => $row]);
            $row['error'] = $e->getMessage();
            $this->failedRows[] = $row;
            return null;
        }
    }

    public function getFailedRows()
    {
        return $this->failedRows;
    }
}
