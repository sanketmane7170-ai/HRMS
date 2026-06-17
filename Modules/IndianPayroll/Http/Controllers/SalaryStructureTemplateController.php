<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Entities\SalaryStructureTemplate;
use Modules\IndianPayroll\Entities\SalaryStructureTemplateComponent;

class SalaryStructureTemplateController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.salary-templates');
    }

    public function index()
    {
        canPerform('Manage Salary Structures');

        $templates = SalaryStructureTemplate::with('components.component')->orderBy('name')->get();

        return view('indianpayroll::salary_structure.templates', compact('templates'));
    }

    public function create()
    {
        canPerform('Manage Salary Structures');

        $components = SalaryComponent::where('type', SalaryComponent::TYPE_EARNING)->where('is_active', true)->orderBy('display_order')->get();
        $html = view('indianpayroll::salary_structure.partials.template_form', ['template' => null, 'components' => $components])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function store(Request $request)
    {
        canPerform('Manage Salary Structures');

        $data = $request->validate([
            'name' => 'required|string|max:150|unique:ip_salary_structure_templates,name',
            'description' => 'nullable|string',
        ]);

        $data['is_active'] = true;
        $template = SalaryStructureTemplate::create($data);

        return redirect()->route('backend.indian-payroll.salary-templates.index')
            ->with('success', createFlashMessage('Salary Template', 'added'))
            ->with('open_template_id', $template->id);
    }

    public function edit(SalaryStructureTemplate $template)
    {
        canPerform('Manage Salary Structures');

        $components = SalaryComponent::where('type', SalaryComponent::TYPE_EARNING)->where('is_active', true)->orderBy('display_order')->get();
        $html = view('indianpayroll::salary_structure.partials.template_form', ['template' => $template, 'components' => $components])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function update(Request $request, SalaryStructureTemplate $template)
    {
        canPerform('Manage Salary Structures');

        $data = $request->validate([
            'name' => 'required|string|max:150|unique:ip_salary_structure_templates,name,'.$template->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        $template->update($data);

        return redirect()->route('backend.indian-payroll.salary-templates.index')
            ->with('success', createFlashMessage('Salary Template', 'updated'));
    }

    public function destroy(SalaryStructureTemplate $template)
    {
        canPerform('Manage Salary Structures');

        $template->delete();

        return redirect()->route('backend.indian-payroll.salary-templates.index')
            ->with('success', createFlashMessage('Salary Template', 'deleted'));
    }

    public function addComponent(Request $request, SalaryStructureTemplate $template)
    {
        canPerform('Manage Salary Structures');

        $data = $request->validate([
            'salary_component_id' => 'required|exists:ip_salary_components,id',
            'calculation_type' => 'required|in:flat,percentage_of_basic,percentage_of_ctc,remainder_of_ctc',
            'value' => 'required_unless:calculation_type,remainder_of_ctc|nullable|numeric|min:0',
        ]);

        SalaryStructureTemplateComponent::updateOrCreate(
            ['template_id' => $template->id, 'salary_component_id' => $data['salary_component_id']],
            ['calculation_type' => $data['calculation_type'], 'value' => $data['value'] ?? 0]
        );

        return redirect()->route('backend.indian-payroll.salary-templates.index')
            ->with('success', createFlashMessage('Template Component', 'added'));
    }

    public function removeComponent(SalaryStructureTemplate $template, SalaryStructureTemplateComponent $component)
    {
        canPerform('Manage Salary Structures');

        abort_if($component->template_id !== $template->id, 404);

        $component->delete();

        return redirect()->route('backend.indian-payroll.salary-templates.index')
            ->with('success', createFlashMessage('Template Component', 'removed'));
    }
}
