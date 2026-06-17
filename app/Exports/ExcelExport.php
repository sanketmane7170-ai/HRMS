<?php

namespace  App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExcelExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $data;
    protected $headers;

    public function __construct(array $data, $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
