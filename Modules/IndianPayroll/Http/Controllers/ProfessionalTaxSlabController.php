<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\ProfessionalTaxSlab;

class ProfessionalTaxSlabController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.professional-tax');
    }

    public function index(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $states = IpState::where('pt_applicable', true)->orderBy('name')->get();
        $stateId = $request->get('state_id', $states->first()?->id);

        $slabs = $stateId
            ? ProfessionalTaxSlab::where('state_id', $stateId)->orderBy('salary_from')->get()
            : collect();

        return view('indianpayroll::statutory_settings.professional_tax', compact('states', 'stateId', 'slabs'));
    }

    public function store(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $data = $request->validate([
            'state_id' => 'required|exists:ip_states,id',
            'gender' => 'required|in:all,male,female',
            'salary_from' => 'required|numeric|min:0',
            'salary_to' => 'nullable|numeric|gt:salary_from',
            'monthly_tax' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,annual',
            'effective_from' => 'required|date',
        ]);

        $data['is_active'] = true;
        ProfessionalTaxSlab::create($data);

        return redirect()->route('backend.indian-payroll.professional-tax.index', ['state_id' => $data['state_id']])
            ->with('success', createFlashMessage('Professional Tax Slab', 'added'));
    }

    public function destroy(ProfessionalTaxSlab $slab)
    {
        canPerform('Manage Statutory Settings');

        $stateId = $slab->state_id;
        $slab->delete();

        return redirect()->route('backend.indian-payroll.professional-tax.index', ['state_id' => $stateId])
            ->with('success', createFlashMessage('Professional Tax Slab', 'deleted'));
    }
}
