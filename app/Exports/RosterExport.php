<?php

namespace  App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RosterExport implements FromArray, WithHeadings
{
    protected $roster;
    protected $headers;

    public function __construct(array $roster, $headers)
    {
        $this->roster = $roster;
        $this->headers = $headers;
    }

    public function array(): array
    {
        return $this->roster;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
