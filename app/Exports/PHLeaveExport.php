<?php

namespace App\Exports;

use App\Models\PHLeaveReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PHLeaveExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {
    //     return PHLeaveReport::all();
    // }

    protected $holiday;
    protected $headers;

    public function __construct(array $holiday, $headers)
    {
        $this->holiday = $holiday;
        $this->headers = $headers;
    }

    public function array(): array
    {
        return $this->holiday;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
