<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\PerformanceReview\Entities\QuestionSet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class QuestionSampleExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $selects;
    protected $row_count;
    protected $column_count;

    public function __construct()
    {
        $question_sets = QuestionSet::pluck('name')->toArray();
        $option_labels = ['a', 'b', 'c', 'd'];

        $this->selects = [
            ['columns_name' => 'A', 'options' => $question_sets],  // question_set now first column
            ['columns_name' => 'H', 'options' => $option_labels],  // correct option
        ];

        $this->row_count = 50;
        $this->column_count = 8;
    }

    public function collection()
    {
        return collect([
            [
                "question_set" => "Please Select",
                "question_text" => "Sample question text",
                "option_a" => "Option A",
                "option_b" => "Option B",
                "option_c" => "Option C",
                "option_d" => "Option D",
                "max_score" => 10,
                "correct_option" => "Please Select"
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            __trans('question_set'),
            __trans('question_text'),
            __trans('option_a'),
            __trans('option_b'),
            __trans('option_c'),
            __trans('option_d'),
            __trans('max_score'),
            __trans('correct_option'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                foreach ($this->selects as $select) {
                    $col = $select['columns_name'];
                    $options = $select['options'];

                    $validation = $event->sheet->getCell("{$col}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input error');
                    $validation->setError('Value is not in list.');
                    $validation->setPromptTitle('Pick from list');
                    $validation->setPrompt('Please pick a value from the drop-down list.');
                    $validation->setFormula1(sprintf('"%s"', implode(',', $options)));

                    for ($i = 3; $i <= $this->row_count; $i++) {
                        $event->sheet->getCell("{$col}{$i}")->setDataValidation(clone $validation);
                    }
                }

                for ($i = 1; $i <= $this->column_count; $i++) {
                    $column = Coordinate::stringFromColumnIndex($i);
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            }
        ];
    }
}
