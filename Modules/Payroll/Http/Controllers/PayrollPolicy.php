<?php

namespace Modules\Payroll\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Entities\PayrollPolicy;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class PayrollPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:Fixed,Hourly',
            'hourly_charges' => 'required_if:type,Hourly',
            'max_hours_per_day' => 'required_without:max_hours_per_month|integer|min:1', // Either max_hours_per_day or max_hours_per_month is required
            'max_hours_per_month' => 'required_without:max_hours_per_day|integer|min:1',
            'formula' => 'required_if:type,Fixed',
            'fixed_amount' => 'nullable|numeric', // Optional fixed amount
            'overtime_formula' => 'nullable|string', // Optional overtime formula
            'min_hours_per_day' => 'nullable|integer|min:1', // Optional minimum hours per day
            'min_hours_per_month' => 'nullable|integer|min:1', // Optional minimum hours per month
            // Add validation for other fields as needed
        ]);

        $policyData = $request->all();

        // If it's a fixed policy, calculate hourly rate based on the formula
        if ($request->input('type') === 'Fixed') {
            $fixedSalary = 1000; // Replace this with the actual fixed salary
            $hourlyRate = $this->calculateHourlyRate($fixedSalary, $request->input('formula'));
            $policyData['hourly_charges'] = $hourlyRate;
        }

        $policy = PayrollPolicy::create($policyData);

        // If hourly policy, create subpolicies
        if ($request->input('type') === 'Hourly') {
            // $policy->hourlySubpolicies()->create([
            //     'hourly_charges' => $request->input('hourly_charges'),
            //     'max_hours_per_day' => $request->input('max_hours_per_day'),
            //     'max_hours_per_month' => $request->input('max_hours_per_month'),
            // ]);
        }

        return redirect()->route('payroll.index')->with('success', 'Payroll policy created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(PayrollPolicy $yourModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayrollPolicy $yourModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayrollPolicy $yourModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollPolicy $yourModel)
    {
        //
    }

    public function calculateHourlyRate($fixedSalary, $formula)
    {
        $language = new ExpressionLanguage();
        $expression = $language->parse($formula, ['fixedSalary']);
        return $language->evaluate($expression, ['fixedSalary' => $fixedSalary]);
    }

}
