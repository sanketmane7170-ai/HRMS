<?php

namespace Modules\Onboarding\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Onboarding\Entities\OnboardingRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class NewHiresImport implements ToModel, WithHeadingRow
{
    public $importedCount = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Try to handle aliases for headers
        $email = $row['email'] ?? $row['email_address'] ?? null;
        $fullName = $row['full_name'] ?? $row['name'] ?? $row['employee_name'] ?? null;
        $joiningDate = $row['joining_date'] ?? $row['date_of_joining'] ?? $row['start_date'] ?? null;

        // Basic validation or skipping empty rows
        if (!$email || !$fullName) {
            return null;
        }

        $email = trim(strtolower($email));

        // Check if record already exists by email
        $existing = OnboardingRecord::where('email', $email)->first();
        if ($existing) {
            return null;
        }

        // Try to find existing user or create null
        $user = User::where('email', $email)->first();

        // Optional: Attempt to map Department/Division name if provided
        $deptName = $row['department'] ?? $row['division'] ?? $row['dept'] ?? null;
        $divisionId = null;
        if ($deptName) {
            $division = \App\Models\Division::where('name', 'like', trim($deptName))->first();
            $divisionId = $division ? $division->id : null;
        }

        $this->importedCount++;

        return new OnboardingRecord([
            'user_id'          => $user ? $user->id : null,
            'full_name'        => trim($fullName),
            'email'            => $email,
            'department_id'    => null, // Can be mapped if needed, default null
            'division_id'      => $divisionId, // Mapping to Division as per user requirement
            'joining_date'     => $joiningDate ? $this->transformDate($joiningDate) : Carbon::now(),
            'status'           => 'pending',
            'progress_percent' => 0,
        ]);
    }

    /**
     * Transform date from Excel
     */
    private function transformDate($value)
    {
        if (!$value) return Carbon::now();

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }
}
