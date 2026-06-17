<?php

namespace Modules\IndianPayroll\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructureComponent;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Entities\SalaryStructureTemplate;
use Modules\IndianPayroll\Services\SalaryStructureResolver;

class EmployeeSalaryStructureController extends Controller
{
    public function __construct(private SalaryStructureResolver $resolver)
    {
        view()->share('activeLink', 'indian-payroll.employee-salary-structures');
    }

    public function index()
    {
        canPerform('Manage Salary Structures');

        $structures = EmployeeSalaryStructure::with('user', 'template')
            ->where('is_active', true)
            ->orderByDesc('effective_from')
            ->paginate(25);

        return view('indianpayroll::salary_structure.employee_index', compact('structures'));
    }

    public function create(User $user)
    {
        canPerform('Manage Salary Structures');

        $templates = SalaryStructureTemplate::where('is_active', true)->orderBy('name')->get();
        $current = EmployeeSalaryStructure::where('user_id', $user->id)->where('is_active', true)->with('components.component')->first();

        return view('indianpayroll::salary_structure.assign', compact('user', 'templates', 'current'));
    }

    public function store(Request $request, User $user)
    {
        canPerform('Manage Salary Structures');

        $data = $request->validate([
            'template_id' => 'required|exists:ip_salary_structure_templates,id',
            'annual_ctc' => 'required|numeric|min:1',
            'effective_from' => 'required|date',
        ]);

        $current = EmployeeSalaryStructure::where('user_id', $user->id)->where('is_active', true)->first();
        if ($current && \Carbon\Carbon::parse($data['effective_from'])->lte($current->effective_from)) {
            return back()->withInput()->with('error', 'The effective date must be after the start date of the current structure (' . $current->effective_from->format('d-M-Y') . ').');
        }

        $template = SalaryStructureTemplate::with('components.component')->findOrFail($data['template_id']);
        $resolvedComponents = $this->resolver->resolve($template, (float) $data['annual_ctc']);

        $reconciliationError = $this->resolver->reconciliationError($resolvedComponents, round($data['annual_ctc'] / 12, 2));
        if ($reconciliationError) {
            return back()->withInput()->with('error', $reconciliationError);
        }

        DB::transaction(function () use ($user, $data, $template, $resolvedComponents) {
            EmployeeSalaryStructure::where('user_id', $user->id)
                ->where('is_active', true)
                ->update(['is_active' => false, 'effective_to' => \Carbon\Carbon::parse($data['effective_from'])->subDay()]);

            $structure = EmployeeSalaryStructure::create([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'annual_ctc' => $data['annual_ctc'],
                'monthly_ctc' => round($data['annual_ctc'] / 12, 2),
                'effective_from' => $data['effective_from'],
                'is_active' => true,
            ]);

            $componentIds = SalaryComponent::pluck('id', 'code');

            foreach ($resolvedComponents as $code => $monthlyAmount) {
                if (! isset($componentIds[$code])) {
                    continue;
                }
                EmployeeSalaryStructureComponent::create([
                    'structure_id' => $structure->id,
                    'salary_component_id' => $componentIds[$code],
                    'monthly_amount' => $monthlyAmount,
                    'annual_amount' => round($monthlyAmount * 12, 2),
                ]);
            }
        });

        return redirect()->route('backend.indian-payroll.employee-salary-structures.show', $user)
            ->with('success', createFlashMessage('Salary Structure', 'assigned'));
    }

    public function show(User $user)
    {
        canPerform('Manage Salary Structures');

        $structure = EmployeeSalaryStructure::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('components.component', 'template')
            ->first();

        $history = EmployeeSalaryStructure::where('user_id', $user->id)
            ->where('is_active', false)
            ->orderByDesc('effective_from')
            ->get();

        return view('indianpayroll::salary_structure.show', compact('user', 'structure', 'history'));
    }

    public function revise(User $user)
    {
        return $this->create($user);
    }
}
