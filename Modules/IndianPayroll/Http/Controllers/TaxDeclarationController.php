<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;
use Modules\IndianPayroll\Entities\InvestmentDeclaration;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Services\Tax\AnnualTaxProjectionBuilder;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;
use Modules\IndianPayroll\Services\Tax\IncomeTaxCalculator;

class TaxDeclarationController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.tax-declarations');
    }

    public function index(Request $request)
    {
        canPerform('Verify Tax Declarations');

        $financialYear = $request->get('financial_year', FinancialYearHelper::forDate(now()));

        $declarations = EmployeeTaxDeclaration::with('user', 'investmentDeclarations', 'hraExemptionInput')
            ->where('financial_year', $financialYear)
            ->orderBy('user_id')
            ->paginate(25);

        $pendingCount = InvestmentDeclaration::where('status', 'pending')
            ->whereHas('declaration', fn ($q) => $q->where('financial_year', $financialYear))
            ->count();

        return view('indianpayroll::tax_declaration.index', compact('declarations', 'financialYear', 'pendingCount'));
    }

    public function show(EmployeeTaxDeclaration $declaration, AnnualTaxProjectionBuilder $builder, IncomeTaxCalculator $calculator)
    {
        canPerform('Verify Tax Declarations');

        $declaration->load('user', 'investmentDeclarations', 'hraExemptionInput');

        // The declared investments/HRA above are only half the story. Compute the
        // same annual tax projection Form 16 shows so HR can see the actual tax
        // picture (gross/taxable income, TDS) on the page instead of only inside
        // the downloaded PDF. Null when the employee has no salary structure yet.
        $taxSummary = null;
        $quarterlyTds = null;

        $fyEnd = FinancialYearHelper::endDate($declaration->financial_year);
        $structure = EmployeeSalaryStructure::where('user_id', $declaration->user_id)
            ->where('effective_from', '<=', $fyEnd)
            ->orderByDesc('effective_from')
            ->with('components.component')
            ->first();

        if ($structure) {
            $input = $builder->build($declaration->user, $structure, $fyEnd);
            $slabs = IncomeTaxSlab::forRegime($declaration->financial_year, $input->regime);
            $surchargeSlabs = IncomeTaxSurchargeSlab::forRegime($declaration->financial_year, $input->regime);
            $taxSummary = $calculator->calculate($input, $slabs, $surchargeSlabs);
            $quarterlyTds = $this->quarterlyTdsBreakup($declaration->user_id, $declaration->financial_year);
        }

        return view('indianpayroll::tax_declaration.show', compact('declaration', 'taxSummary', 'quarterlyTds'));
    }

    /**
     * Sum of TDS deducted per financial-year quarter from the employee's
     * payslips. Mirrors the breakup used by the Form 16 PDF.
     */
    private function quarterlyTdsBreakup(int $userId, string $financialYear): array
    {
        $fyStart = FinancialYearHelper::startDate($financialYear);
        $fyEnd = FinancialYearHelper::endDate($financialYear);

        $payslips = Payslip::where('user_id', $userId)
            ->whereHas('run', fn ($q) => $q->where('period_start', '>=', $fyStart)->where('period_start', '<=', $fyEnd))
            ->with('run', 'components.component')
            ->get();

        $quarters = [1 => 0.0, 2 => 0.0, 3 => 0.0, 4 => 0.0];

        foreach ($payslips as $payslip) {
            $quarter = intdiv($fyStart->diffInMonths($payslip->run->period_start), 3) + 1;
            $tds = $payslip->components->first(fn ($c) => optional($c->component)->code === SalaryComponent::CODE_TDS)?->amount ?? 0;
            $quarters[$quarter] = ($quarters[$quarter] ?? 0) + (float) $tds;
        }

        return $quarters;
    }

    /**
     * Lets HR actually inspect the uploaded proof before verifying/rejecting it —
     * previously the UI only showed a "proof uploaded" icon with no way to open it,
     * so verification decisions were being made without ever seeing the document.
     */
    public function downloadProof(InvestmentDeclaration $investmentDeclaration)
    {
        canPerform('Verify Tax Declarations');

        abort_if(empty($investmentDeclaration->proof_path), 404, 'No proof has been uploaded for this declaration.');

        $disk = Storage::disk(config('indianpayroll.document_disk'));

        abort_unless($disk->exists($investmentDeclaration->proof_path), 404, 'The uploaded proof file is missing from storage.');

        return $disk->download($investmentDeclaration->proof_path);
    }

    public function verify(Request $request, InvestmentDeclaration $investmentDeclaration)
    {
        canPerform('Verify Tax Declarations');

        $data = $request->validate([
            'verified_amount' => 'required|numeric|min:0',
            'status' => 'required|in:verified,rejected',
        ]);

        $investmentDeclaration->update([
            'verified_amount' => $data['status'] === 'verified' ? $data['verified_amount'] : 0,
            'status' => $data['status'],
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return redirect()->route('backend.indian-payroll.tax-declarations.show', $investmentDeclaration->declaration_id)
            ->with('success', 'Investment declaration '.$data['status'].'.');
    }
}
