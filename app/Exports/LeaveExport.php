<?php

namespace  App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeaveExport implements FromArray, WithHeadings
{
    protected $leave;
    protected $headers;

    public function __construct(array $leave, $headers)
    {
        $this->leave = $leave;
        $this->headers = $headers;
    }

    public function array(): array
    {
        return $this->leave;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
