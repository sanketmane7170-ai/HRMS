<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\EmployeeLoan;
use Modules\IndianPayroll\Entities\EmployeeProfile;

class EmployeeLoanController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.loans');
    }

    public function index()
    {
        canPerform('Manage Indian Payroll');

        $loans = EmployeeLoan::with(['user', 'recoveries'])->orderByDesc('id')->paginate(20);

        return view('indianpayroll::loan.index', compact('loans'));
    }

    public function create()
    {
        canPerform('Manage Indian Payroll');

        $employees = EmployeeProfile::with('user')->get();

        return view('indianpayroll::loan.create', compact('employees'));
    }

    public function store(Request $request)
    {
        canPerform('Manage Indian Payroll');

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'loan_type' => 'required|in:salary_advance,personal_loan,emergency_loan',
            'principal_amount' => 'required|numeric|min:1',
            'emi_amount' => 'required|numeric|min:1|lte:principal_amount',
            'start_month' => 'required|integer|min:1|max:12',
            'start_year' => 'required|integer|min:2000|max:2100',
            'disbursed_on' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['created_by'] = auth()->id();
        EmployeeLoan::create($data);

        return redirect()->route('backend.indian-payroll.loans.index')
            ->with('success', 'Loan / advance recorded. EMI will be recovered automatically in payroll runs from the start month.');
    }

    public function show(EmployeeLoan $loan)
    {
        canPerform('Manage Indian Payroll');

        $loan->load(['user', 'recoveries.run']);

        return view('indianpayroll::loan.show', compact('loan'));
    }

    /**
     * Stop further recovery without deleting history. Past recoveries stay on the
     * ledger (and on already-processed payslips); no new EMI is taken.
     */
    public function cancel(EmployeeLoan $loan)
    {
        canPerform('Manage Indian Payroll');

        $loan->update(['status' => EmployeeLoan::STATUS_CANCELLED]);

        return redirect()->route('backend.indian-payroll.loans.index')
            ->with('success', 'Loan recovery stopped.');
    }
}
