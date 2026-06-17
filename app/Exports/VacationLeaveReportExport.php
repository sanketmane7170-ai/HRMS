<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class VacationLeaveReportExport implements FromView
{
    protected $data;
    protected $months;

    public function __construct($data, $months)
    {
        $this->data = $data;
        $this->months = $months;
    }

    public function view(): View
    {
        return view('backend.reports.vacation_leave_excel', [
            'data' => $this->data,
            'months' => $this->months,
        ]);
    }
}
