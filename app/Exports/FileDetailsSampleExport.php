<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Spatie\Permission\Models\Role;
use App\Models\Department;

class FileDetailsSampleExport implements FromCollection, WithHeadings, ShouldAutoSize,WithEvents
{
    protected  $selects;
    protected  $row_count;                                              
    protected  $column_count;

    public function __construct()
    {
        $departments = Department::pluck('name')->toArray();
        
        $selects=[
            ['columns_name'=>'A','options'=>$departments],
        ];

        $this->selects=$selects;
        $this->row_count=50;
        $this->column_count=5;
    }

    public function collection()
    {
        $query = [
            [
                "department_id"=> 'Please Select',
                "title"=> "Test File",
                "comment"=> "This is a test file comment",
                "issue_date"=> "2025-01-07",
                "expire_date"=> "2025-01-08",
                "notification_days"=> "30",
            ]
        ];

        return collect($query);
        ;
    }

    public function headings(): array
    {
        $headers = [
            __trans('department'),
            __trans('title'),
            __trans('comment'),
            __trans('issue_date'),
            __trans('expire_date'),
            __trans('notification_days'),
        ];

        return $headers;
    }

    public function registerEvents(): array
    {
        return [
            // handle by a closure.
            AfterSheet::class => function(AfterSheet $event) {
                $row_count = $this->row_count;
                $column_count = $this->column_count;
                foreach ($this->selects as $select){
                    $drop_column = $select['columns_name'];
                    $options = $select['options'];
                    // set dropdown list for first data row
                    $validation = $event->sheet->getCell("{$drop_column}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST );
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION );
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input error');
                    $validation->setError('Value is not in list.');
                    $validation->setPromptTitle('Pick from list');
                    $validation->setPrompt('Please pick a value from the drop-down list.');
                    $validation->setFormula1(sprintf('"%s"',implode(',',$options)));

                    // clone validation to remaining rows
                    for ($i = 3; $i <= $row_count; $i++) {
                        $event->sheet->getCell("{$drop_column}{$i}")->setDataValidation(clone $validation);
                    }
                    // set columns to autosize
                    for ($i = 1; $i <= $column_count; $i++) {
                        $column = Coordinate::stringFromColumnIndex($i);
                        $event->sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                }

            },
        ];
    }
}
