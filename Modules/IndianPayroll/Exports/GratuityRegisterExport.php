<?php

namespace Modules\IndianPayroll\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\IndianPayroll\Entities\FullFinalSettlement;

class GratuityRegisterExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return FullFinalSettlement::with('user')
            ->where('gratuity_amount', '>', 0)
            ->where('status', '!=', 'draft')
            ->orderByDesc('last_working_day');
    }

    public function headings(): array
    {
        return ['Employee Name', 'Last Working Day', 'Gratuity Amount (Gross)', 'Exempt Amount', 'Taxable Amount', 'Status'];
    }

    public function map($settlement): array
    {
        return [
            $settlement->user->name,
            $settlement->last_working_day->format('d-M-Y'),
            number_format((float) $settlement->gratuity_amount, 2, '.', ''),
            number_format((float) $settlement->gratuity_amount - (float) $settlement->gratuity_taxable_amount, 2, '.', ''),
            number_format((float) $settlement->gratuity_taxable_amount, 2, '.', ''),
            ucfirst($settlement->status),
        ];
    }
}
