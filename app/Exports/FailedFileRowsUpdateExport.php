<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class FailedFileRowsUpdateExport implements FromCollection, WithHeadings
{
    protected $failedRows;

    public function __construct(array $failedRows)
    {
        $this->failedRows = $failedRows;
    }

    /**
     * Return collection of failed rows
     */
    public function collection()
    {
        return new Collection($this->failedRows);
        // // Flatten each failed row for export
        // return collect($this->failedRows)->map(function ($row) {
        //     return [
        //         'row_number' => $row['row'],
        //         'errors'     => implode('; ', $row['errors']),
        //         'values'     => json_encode($row['values']), // store full row data
        //     ];
        // });
    }

    /**
     * Define the Excel headings
     */
    public function headings(): array
    {
        return [
            'department_id',
            'title',
            'comment',
            'issue_date',
            'expire_date',
            'notification_days',
        ];
    }
}
