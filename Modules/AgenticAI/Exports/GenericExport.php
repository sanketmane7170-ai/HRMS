<?php

namespace Modules\AgenticAI\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $headers;

    public function __construct(array $data, array $headers = [])
    {
        $this->data = collect($data);
        
        // If headers not provided, attempt to pull keys from first item
        if (empty($headers) && !empty($data)) {
            $firstItem = (array) $data[0];
            $this->headers = array_keys($firstItem);
        } else {
            $this->headers = $headers;
        }
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        // Capitalize headers
        return array_map(function($h) {
            return ucwords(str_replace('_', ' ', $h));
        }, $this->headers);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
