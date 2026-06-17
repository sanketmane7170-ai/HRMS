<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\InvestmentDeclaration;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;

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

    public function show(EmployeeTaxDeclaration $declaration)
    {
        canPerform('Verify Tax Declarations');

        $declaration->load('user', 'investmentDeclarations', 'hraExemptionInput');

        return view('indianpayroll::tax_declaration.show', compact('declaration'));
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
