<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;

class IncomeTaxSlabController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.tax-slabs');
    }

    public function index(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $financialYear = $request->get('financial_year', \Modules\IndianPayroll\Services\Tax\FinancialYearHelper::forDate(now()));

        $newRegimeSlabs = IncomeTaxSlab::forRegime($financialYear, 'new');
        $oldRegimeSlabs = IncomeTaxSlab::forRegime($financialYear, 'old');
        $surchargeSlabs = IncomeTaxSurchargeSlab::where('financial_year', $financialYear)->orderBy('regime')->orderBy('income_from')->get();
        $financialYears = IncomeTaxSlab::select('financial_year')->distinct()->orderByDesc('financial_year')->pluck('financial_year');

        return view('indianpayroll::statutory_settings.tax_slabs', compact(
            'financialYear', 'newRegimeSlabs', 'oldRegimeSlabs', 'surchargeSlabs', 'financialYears'
        ));
    }

    public function store(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $data = $request->validate([
            'financial_year' => 'required|string|size:9',
            'regime' => 'required|in:old,new',
            'slab_from' => 'required|numeric|min:0',
            'slab_to' => 'nullable|numeric|gt:slab_from',
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        IncomeTaxSlab::create($data);

        return redirect()->route('backend.indian-payroll.tax-slabs.index', ['financial_year' => $data['financial_year']])
            ->with('success', createFlashMessage('Tax Slab', 'added'));
    }

    public function destroy(IncomeTaxSlab $slab)
    {
        canPerform('Manage Statutory Settings');

        $fy = $slab->financial_year;
        $slab->delete();

        return redirect()->route('backend.indian-payroll.tax-slabs.index', ['financial_year' => $fy])
            ->with('success', createFlashMessage('Tax Slab', 'deleted'));
    }
}
