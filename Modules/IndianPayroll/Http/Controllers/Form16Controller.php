<?php

namespace Modules\IndianPayroll\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Exports\Form24QExport;
use Modules\IndianPayroll\Services\Tax\AnnualTaxProjectionBuilder;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;
use Modules\IndianPayroll\Services\Tax\IncomeTaxCalculator;

class Form16Controller extends Controller
{
    public function download(User $user, string $financialYear, AnnualTaxProjectionBuilder $builder, IncomeTaxCalculator $calculator)
    {
        canPerform('Manage Indian Payroll');

        $fyEnd = FinancialYearHelper::endDate($financialYear);

        $structure = EmployeeSalaryStructure::where('user_id', $user->id)
            ->where('effective_from', '<=', $fyEnd)
            ->orderByDesc('effective_from')
            ->with('components.component')
            ->firstOrFail();

        $declaration = EmployeeTaxDeclaration::where('user_id', $user->id)->where('financial_year', $financialYear)->firstOrFail();

        $input = $builder->build($user, $structure, $fyEnd);
        $slabs = IncomeTaxSlab::forRegime($financialYear, $input->regime);
        $surchargeSlabs = IncomeTaxSurchargeSlab::forRegime($financialYear, $input->regime);
        $result = $calculator->calculate($input, $slabs, $surchargeSlabs);

        $quarterlyTds = $this->quarterlyTdsBreakup($user->id, $financialYear);

        $pdf = Pdf::loadView('indianpayroll::tax_declaration.form16', compact('user', 'financialYear', 'result', 'quarterlyTds', 'declaration'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('form16_'.$financialYear.'_'.str_replace(' ', '_', strtolower($user->name)).'.pdf');
    }

    public function form24q(string $financialYear, int $quarter)
    {
        canPerform('Manage Indian Payroll');

        abort_unless(in_array($quarter, [1, 2, 3, 4]), 404);

        return Excel::download(new Form24QExport($financialYear, $quarter), "form24q_{$financialYear}_Q{$quarter}.xlsx");
    }

    private function quarterlyTdsBreakup(int $userId, string $financialYear): array
    {
        $fyStart = FinancialYearHelper::startDate($financialYear);

        $payslips = Payslip::where('user_id', $userId)
            ->whereHas('run', fn ($q) => $q->where('period_start', '>=', $fyStart)->where('period_start', '<=', FinancialYearHelper::endDate($financialYear)))
            ->with('run', 'components.component')
            ->get();

        $quarters = [1 => 0.0, 2 => 0.0, 3 => 0.0, 4 => 0.0];

        foreach ($payslips as $payslip) {
            $monthsFromFyStart = $fyStart->diffInMonths($payslip->run->period_start);
            $quarter = intdiv($monthsFromFyStart, 3) + 1;
            $tds = $payslip->components->first(fn ($c) => optional($c->component)->code === SalaryComponent::CODE_TDS)?->amount ?? 0;
            $quarters[$quarter] = ($quarters[$quarter] ?? 0) + (float) $tds;
        }

        return $quarters;
    }
}
