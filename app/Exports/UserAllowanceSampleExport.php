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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class UserAllowanceSampleExport implements FromQuery, WithHeadings, ShouldAutoSize, WithEvents, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $selects;
    protected $row_count;
    protected $column_count;
    protected $fixedAllowances;
    protected $availableMonths;
    protected $month;
    protected $year;
    protected $selectedMonthName;

    // public function __construct($month, $year)
    // {
    //     $this->month = $month;
    //     $this->year  = $year;

    //     $allowance_type    = ['fixed', 'percentage'];
    //     $for_current_month = ['yes', 'no'];

    //     // $fixedAllowance = SetAllowanceDeducation::where('type',1)->get();
    //     $this->fixedAllowances = SetAllowanceDeducation::where('type', 1)->get();

    //     $currentMonth = (int) date('n');
    //     // $availableMonths = [];
    //     // for ($i = 1; $i >= 0; $i--) {
    //     //     $monthIndex        = ($currentMonth - 1 - $i + 12) % 12;
    //     //     $availableMonths[] = $monthNames[$monthIndex];
    //     // }
    //     $currentMonth = Carbon::now()->month;

    //     $monthNames = [
    //         'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    //         'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    //     ];

    //     $availableMonths       = array_slice($monthNames, $currentMonth - 2, 2);
    //     $this->availableMonths = $availableMonths;

    //     $selects = [
    //         ['columns_name' => 'G', 'options' => $availableMonths],
    //     ];

    //     $this->selects      = $selects;
    //     $this->row_count    = 9999;
    //     $this->column_count = 5;
    // }
    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year  = $year;

        $this->fixedAllowances = SetAllowanceDeducation::where('type', 1)->get();

        $this->selectedMonthName = Carbon::create()->month($this->month)->format('M');

        $currentMonth = Carbon::now()->month;

        $monthNames = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
        ];

        $this->availableMonths = array_slice($monthNames, $currentMonth - 2, 2);

        $this->selects = [
            ['columns_name' => 'G', 'options' => $this->availableMonths],
        ];

        $this->row_count    = 9999;
        $this->column_count = 5;
    }

    public function query()
    {
        $query = User::with([
            'workDetail', 'salary',
        ])->where('status', 'active')
            ->whereDoesntHave('roles', function ($query) {

                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->where('settlement_status', 0);

        return $query;
    }

    // function map($user): array
    // {
    //     $data = [
    //         $user->employee_id ?? null,
    //         $user->first_name ?? null,
    //         $user->last_name ?? null,
    //         $user->email ?? null,
    //         date('Y-m-d'),
    //         date('Y'),
    //         null,
    //     ];

    //     foreach ($this->fixedAllowances as $allowance) {
    //         $data[] = null;    // amount
    //         $data[] = "fixed"; // type
    //         $data[] = "yes";   // monthly fixed
    //     }

    //     return $data;
    // }
    public function map($user): array
    {
        $data = [
            $user->employee_id ?? null,
            $user->first_name ?? null,
            $user->last_name ?? null,
            $user->email ?? null,
            date('Y-m-d'),
            $this->year,
            // $this->availableMonths[0],
            $this->selectedMonthName ?? $this->availableMonths[0],
        ];

        foreach ($this->fixedAllowances as $allowance) {

            $title = $allowance->name;

            $existing = \Modules\Payroll\Entities\UserSalaryAllowance::where([
                'user_id'    => $user->id,
                'salary_id'  => $user->salary->id ?? null,
                'title'      => $title,
                'month_code' => $this->month,
                'year'       => $this->year,
            ])->first();

            $data[] = $existing->amount ?? null;
            $data[] = $existing->allowance_type ?? 'fixed';

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

        foreach ($this->fixedAllowances as $allowance) {
            $headers[] = $allowance->name . ' Amount';
            $headers[] = $allowance->name . ' Type';
            $headers[] = $allowance->name . ' Monthly Fixed';
        }

        return $headers;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $row_count = $this->row_count;

                $allowance_type = ['fixed', 'percentage'];
                $monthly_fixed  = ['yes', 'no'];

                // $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $monthNames = $this->availableMonths;

                // MONTH DROPDOWN (COLUMN G)
                $monthValidation = $event->sheet->getCell("G2")->getDataValidation();
                $monthValidation->setType(DataValidation::TYPE_LIST);
                $monthValidation->setAllowBlank(false);
                $monthValidation->setShowDropDown(true);
                $monthValidation->setFormula1(sprintf('"%s"', implode(',', $monthNames)));

                for ($i = 3; $i <= $row_count; $i++) {
                    $event->sheet->getCell("G{$i}")
                        ->setDataValidation(clone $monthValidation);
                }

                $startColumn = 8; // H column

                foreach ($this->fixedAllowances as $allowance) {

                    $typeColumn    = Coordinate::stringFromColumnIndex($startColumn + 1);
                    $monthlyColumn = Coordinate::stringFromColumnIndex($startColumn + 2);

                    // TYPE dropdown
                    $validation = $event->sheet->getCell("{$typeColumn}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1(sprintf('"%s"', implode(',', $allowance_type)));

                    for ($i = 3; $i <= $row_count; $i++) {
                        $event->sheet->getCell("{$typeColumn}{$i}")
                            ->setDataValidation(clone $validation);
                    }

                    // MONTHLY FIXED dropdown
                    $validation2 = $event->sheet->getCell("{$monthlyColumn}2")->getDataValidation();
                    $validation2->setType(DataValidation::TYPE_LIST);
                    $validation2->setAllowBlank(false);
                    $validation2->setShowDropDown(true);
                    $validation2->setFormula1(sprintf('"%s"', implode(',', $monthly_fixed)));

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
