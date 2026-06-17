<?php
namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Modules\Payroll\Entities\UserDeduction;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class UserDeductionSampleExport implements FromQuery, WithHeadings, ShouldAutoSize, WithEvents, WithMapping
{
    protected $row_count;
    protected $deductions;
    protected $availableMonths;
    protected $month;
    protected $year;
    protected $selectedMonthName;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year  = $year;

        $this->row_count = 9999;

        $this->deductions = SetAllowanceDeducation::where('type', 2)->get();

        // Selected month name (Jan, Feb)
        $this->selectedMonthName = Carbon::create()->month($this->month)->format('M');

        // Only selected month in dropdown (recommended)
        $this->availableMonths = [$this->selectedMonthName];
    }

    public function query()
    {
        return User::with(['workDetail', 'salary'])
            ->where('status', 'active')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('settlement_status', 0);
    }

    public function map($user): array
    {
        $data = [
            $user->employee_id ?? null,
            $user->first_name ?? null,
            $user->last_name ?? null,
            $user->email ?? null,
            date('Y-m-d'),
            $this->year,
            $this->selectedMonthName,
        ];

        foreach ($this->deductions as $deduction) {

            $title = $deduction->name;

            $existing = UserDeduction::where([
                'user_id'    => $user->id,
                'salary_id'  => $user->salary->id ?? null,
                'title'      => $title,
                'month_code' => $this->month,
                'year'       => $this->year,
            ])->first();

            $data[] = $existing->amount ?? null;
            $data[] = $existing->deduction_type ?? 'fixed';
            $data[] = isset($existing)
                ? ($existing->is_fixed_for_current_month == 1 ? 'yes' : 'no')
                : 'yes';
        }

        return $data;
    }

    public function headings(): array
    {
        $headers = [
            __trans('employee_id'),
            __trans('first_name'),
            __trans('last_name'),
            __trans('work_email'),
            __trans('date'),
            __trans('year'),
            __trans('month'),
        ];

        foreach ($this->deductions as $deduction) {
            $headers[] = $deduction->name . ' Amount';
            $headers[] = $deduction->name . ' Type';
            $headers[] = $deduction->name . ' Monthly Fixed';
        }

        return $headers;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $row_count = $this->row_count;

                $typeOptions    = ['fixed', 'percentage'];
                $monthlyOptions = ['yes', 'no'];

                // MONTH DROPDOWN (COLUMN G)
                $monthValidation = $event->sheet->getCell("G2")->getDataValidation();
                $monthValidation->setType(DataValidation::TYPE_LIST);
                $monthValidation->setAllowBlank(false);
                $monthValidation->setShowDropDown(true);
                $monthValidation->setFormula1(sprintf('"%s"', implode(',', $this->availableMonths)));

                for ($i = 3; $i <= $row_count; $i++) {
                    $event->sheet->getCell("G{$i}")
                        ->setDataValidation(clone $monthValidation);
                }

                // START FROM COLUMN H
                $startColumn = 8;

                foreach ($this->deductions as $deduction) {

                    $typeColumn    = Coordinate::stringFromColumnIndex($startColumn + 1);
                    $monthlyColumn = Coordinate::stringFromColumnIndex($startColumn + 2);

                    // TYPE DROPDOWN
                    $validation = $event->sheet->getCell("{$typeColumn}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1(sprintf('"%s"', implode(',', $typeOptions)));

                    for ($i = 3; $i <= $row_count; $i++) {
                        $event->sheet->getCell("{$typeColumn}{$i}")
                            ->setDataValidation(clone $validation);
                    }

                    // MONTHLY FIXED DROPDOWN
                    $validation2 = $event->sheet->getCell("{$monthlyColumn}2")->getDataValidation();
                    $validation2->setType(DataValidation::TYPE_LIST);
                    $validation2->setAllowBlank(false);
                    $validation2->setShowDropDown(true);
                    $validation2->setFormula1(sprintf('"%s"', implode(',', $monthlyOptions)));

                    for ($i = 3; $i <= $row_count; $i++) {
                        $event->sheet->getCell("{$monthlyColumn}{$i}")
                            ->setDataValidation(clone $validation2);
                    }

                    $startColumn += 3;
                }
            },
        ];
    }
}
