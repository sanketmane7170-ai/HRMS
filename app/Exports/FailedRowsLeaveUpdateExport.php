<?php


namespace  App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class FailedRowsLeaveUpdateExport implements WithHeadings, ShouldAutoSize
{
    protected $headers;

    public function __construct($headers)
    {
        $this->headers = $headers;
    }

    // public function array(): array
    // {
    //     // return $this->leave;
    // }

    public function headings(): array
    {
        return $this->headers;
    }
}



?>
