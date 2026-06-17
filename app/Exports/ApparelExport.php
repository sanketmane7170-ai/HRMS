<?php

namespace App\Exports;

use App\Models\Apparel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ApparelExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $apparel;
    protected $headers;

    public function __construct(array $apparel, $headers)
    {
        $this->apparel = $apparel;
        $this->headers = $headers;
    }

    public function array(): array
    {
        return $this->apparel;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
