<?php

namespace App\Imports;

use Modules\FileManager\Entities\FileManager;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\Department;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class FileDetailsImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $failedRows = [];

    public function map($row): array
    {
        return [
            'issue_date'  => $this->formatDate($row['issue_date']),
            'expiry_date' => $this->formatDate($row['expire_date']),
        ];
    }

    public function getDepartmentId($data)
    {
        $data = Department::where('name', $data)
            ->orWhere('code', $data)->first();

        return $data->id ?? null;
    }

    public function model(array $row)
    {
        if (empty($row['department']) && empty($row['title'])) {
            return null;
        }

        return new FileManager([
            'department_id' => $this->getDepartmentId($row['department']),
            'title' => $row['title'],
            'comment' => $row['comment'],
            'issue_date' => $this->transformDate($row['issue_date']),
            'expiry_date' => $this->transformDate($row['expire_date']),
            'expiry_days' => $row['notification_days'] ?? 0,
        ]);
    }

    private function transformDate($value, $format = 'Y-m-d')
    {
        try {
            // If value is Excel serialized date (like 45234)
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format($format);
            }

            // If value is a string like "2025-08-01" or "20-08-2025"
            return Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            return null; // Or handle invalid date
        }
    }

    public function getFailedRows()
    {
        return $this->failedRows;
    }
}
