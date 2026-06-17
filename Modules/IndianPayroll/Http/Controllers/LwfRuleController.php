<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\LwfRule;

class LwfRuleController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.lwf-rules');
    }

    public function index(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $states = IpState::where('lwf_applicable', true)->orderBy('name')->get();
        $stateId = $request->get('state_id', $states->first()?->id);

        $rules = $stateId
            ? LwfRule::where('state_id', $stateId)->orderByDesc('effective_from')->get()
            : collect();

        return view('indianpayroll::statutory_settings.lwf_rules', compact('states', 'stateId', 'rules'));
    }

    public function store(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $data = $request->validate([
            'state_id' => 'required|exists:ip_states,id',
            'frequency' => 'required|in:monthly,half_yearly,annual',
            'employee_contribution' => 'required|numeric|min:0',
            'employer_contribution' => 'required|numeric|min:0',
            'wage_ceiling' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $data['is_active'] = true;
        LwfRule::create($data);

        return redirect()->route('backend.indian-payroll.lwf-rules.index', ['state_id' => $data['state_id']])
            ->with('success', createFlashMessage('LWF Rule', 'added'));
    }

    public function destroy(LwfRule $rule)
    {
        canPerform('Manage Statutory Settings');

        $stateId = $rule->state_id;
        $rule->delete();

        return redirect()->route('backend.indian-payroll.lwf-rules.index', ['state_id' => $stateId])
            ->with('success', createFlashMessage('LWF Rule', 'deleted'));
    }
}
