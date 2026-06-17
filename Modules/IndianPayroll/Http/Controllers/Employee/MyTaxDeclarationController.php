<?php

namespace Modules\IndianPayroll\Http\Controllers\Employee;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\HraExemptionInput;
use Modules\IndianPayroll\Entities\InvestmentDeclaration;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;

class MyTaxDeclarationController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'my-indian-payroll.tax-declaration');
    }

    public function index()
    {
        $financialYear = FinancialYearHelper::forDate(now());

        $declaration = EmployeeTaxDeclaration::firstOrCreate(
            ['user_id' => auth()->id(), 'financial_year' => $financialYear],
            ['regime_choice' => 'new']
        );

        $declaration->load('investmentDeclarations', 'hraExemptionInput');

        $sections = config('indianpayroll.investment_sections');

        return view('indianpayroll::tax_declaration.my_declaration', compact('declaration', 'sections', 'financialYear'));
    }

    public function chooseRegime(Request $request)
    {
        $data = $request->validate(['regime' => 'required|in:old,new']);

        $declaration = $this->currentDeclaration();

        if ($declaration->isRegimeLocked()) {
            return back()->with('error', 'Your regime choice is locked for this financial year. Contact HR to change it.');
        }

        $declaration->update(['regime_choice' => $data['regime']]);

        return back()->with('success', 'Tax regime updated.');
    }

    public function storeInvestment(Request $request)
    {
        $data = $request->validate([
            'section_code' => 'required|string|max:10',
            'declared_amount' => 'required|numeric|min:0',
            'proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $declaration = $this->currentDeclaration();

        // Re-declaring resets HR verification — a previously verified amount no longer
        // applies once the employee changes the declared figure or uploads a new proof.
        $updateData = [
            'declared_amount' => $data['declared_amount'],
            'status' => 'pending',
            'verified_amount' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];

        if ($request->hasFile('proof')) {
            $updateData['proof_path'] = $request->file('proof')->store(
                config('indianpayroll.document_path').'/'.$declaration->user_id,
                config('indianpayroll.document_disk')
            );
        }

        InvestmentDeclaration::updateOrCreate(
            ['declaration_id' => $declaration->id, 'section_code' => $data['section_code']],
            $updateData
        );

        return back()->with('success', 'Investment declaration saved. HR will verify the proof before it applies to your TDS.');
    }

    public function storeHra(Request $request)
    {
        $data = $request->validate([
            'monthly_rent' => 'required|numeric|min:0',
            'is_metro' => 'boolean',
            'landlord_pan' => 'nullable|string|size:10|required_if:monthly_rent_over_threshold,true',
            'landlord_name' => 'nullable|string|max:150',
        ]);

        $declaration = $this->currentDeclaration();

        if ($data['monthly_rent'] * 12 > 100000 && empty($data['landlord_pan'])) {
            return back()->withErrors(['landlord_pan' => 'Landlord PAN is mandatory when annual rent exceeds Rs. 1,00,000.']);
        }

        HraExemptionInput::updateOrCreate(['declaration_id' => $declaration->id], $data);

        return back()->with('success', 'HRA details saved.');
    }

    private function currentDeclaration(): EmployeeTaxDeclaration
    {
        return EmployeeTaxDeclaration::firstOrCreate(
            ['user_id' => auth()->id(), 'financial_year' => FinancialYearHelper::forDate(now())],
            ['regime_choice' => 'new']
        );
    }
}
