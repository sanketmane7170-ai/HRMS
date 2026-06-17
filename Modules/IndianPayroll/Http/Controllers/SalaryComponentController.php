<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\SalaryComponent;

class SalaryComponentController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.salary-components');
    }

    public function index()
    {
        canPerform('Manage Salary Structures');

        $components = SalaryComponent::orderBy('display_order')->get();

        return view('indianpayroll::salary_structure.components', compact('components'));
    }

    public function create()
    {
        canPerform('Manage Salary Structures');

        $html = view('indianpayroll::salary_structure.partials.component_form', ['component' => null])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function store(Request $request)
    {
        canPerform('Manage Salary Structures');

        $response = getErrorResponse();

        $data = $request->validate([
            'code' => 'required|string|max:50|unique:ip_salary_components,code',
            'name' => 'required|string|max:150',
            'type' => 'required|in:earning,deduction,employer_contribution',
            'is_taxable' => 'boolean',
            'is_part_of_ctc' => 'boolean',
            'considered_for_pf_wage' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        $data['is_taxable'] = $request->has('is_taxable');
        $data['is_part_of_ctc'] = $request->has('is_part_of_ctc');
        $data['considered_for_pf_wage'] = $request->has('considered_for_pf_wage');

        try {
            $data['code'] = strtoupper(str_replace(' ', '_', $data['code']));
            $data['is_statutory'] = false; // statutory codes are seeded by the engine, never created via this form
            $data['is_active'] = true;
            SalaryComponent::create($data);
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();

            return response()->json($response);
        }

        return response()->json(getSuccessResponse(createFlashMessage('Salary Component', 'added')));
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        canPerform('Manage Salary Structures');

        $html = view('indianpayroll::salary_structure.partials.component_form', ['component' => $salaryComponent])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        canPerform('Manage Salary Structures');

        if ($salaryComponent->is_statutory) {
            return response()->json(getErrorResponse('Statutory components are managed by the payroll engine and cannot be edited here.'));
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'is_taxable' => 'boolean',
            'is_part_of_ctc' => 'boolean',
            'considered_for_pf_wage' => 'boolean',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $data['is_taxable'] = $request->has('is_taxable');
        $data['is_part_of_ctc'] = $request->has('is_part_of_ctc');
        $data['considered_for_pf_wage'] = $request->has('considered_for_pf_wage');
        $data['is_active'] = $request->has('is_active');

        $salaryComponent->update($data);

        return response()->json(getSuccessResponse(createFlashMessage('Salary Component', 'updated')));
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        canPerform('Manage Salary Structures');

        if ($salaryComponent->is_statutory) {
            return response()->json(getErrorResponse('Statutory components cannot be deleted.'));
        }

        $salaryComponent->delete();

        return response()->json(getSuccessResponse(createFlashMessage('Salary Component', 'deleted')));
    }
}
