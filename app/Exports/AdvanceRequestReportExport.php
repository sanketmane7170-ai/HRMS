<?php

namespace App\Exports;

use App\Models\AdvanceRequestHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
};

class AdvanceRequestReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return AdvanceRequestHistory::with('user', 'advanceRequest')
                ->when($this->request->start_date && $this->request->end_date, function ($q) {

                    $start = Carbon::createFromFormat('d-m-Y', $this->request->start_date)->startOfDay();
                    $end   = Carbon::createFromFormat('d-m-Y', $this->request->end_date)->endOfDay();

                    $q->whereBetween('action_date', [$start, $end]);
                })
                ->when($this->request->user_id, function ($q) {
                    $q->whereIn('user_id', (array) $this->request->user_id);
                })
                ->get();

    }

    public function headings(): array
    {
        return [
            'Employee',
            'Reference No',
            'Action Date',
            'Total Amount',
            'Approved Amount',
            'Installment Amount',
            'Loan Duration (In Month)',
            'Installment',
            'Installment Paid',
            'Installment Pending',
            'Payment Mode',
            'Description',
        ];
    }

    public function map($row): array
    {
        return [
            '(' . ($row->user->employee_id ?? 'N/A') . ') ' . ($row->user->name ?? 'N/A'),
            $row->advanceRequest->reference_number ?? 'N/A',
            Carbon::parse($row->action_date)->format('d-m-Y'),
            $row->advanceRequest->amount,
            $row->advanceRequest->approved_amount,
            $row->amount,
            $row->advanceRequest->loan_months,
            $row->advanceRequest->instalments,
            $row->installments_paid,
            $row->installments_pending,
            $row->advanceRequest->loan_mode,
            $row->description,
        ];
    }
}

