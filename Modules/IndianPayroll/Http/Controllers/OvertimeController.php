<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\OvertimeEntry;

class OvertimeController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.overtime');
    }

    public function index()
    {
        canPerform('Manage Indian Payroll');

        $entries = OvertimeEntry::with(['user', 'run'])->orderByDesc('id')->paginate(20);

        return view('indianpayroll::overtime.index', compact('entries'));
    }

    public function create()
    {
        canPerform('Manage Indian Payroll');

        $employees = EmployeeProfile::with('user')->get();

        return view('indianpayroll::overtime.create', compact('employees'));
    }

    public function store(Request $request)
    {
        canPerform('Manage Indian Payroll');

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'entry_type' => 'required|in:overtime,comp_off',
            'hours' => 'required|numeric|min:0',
            'rate_per_unit' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $data['amount'] = round($data['hours'] * $data['rate_per_unit'], 2);
        $data['status'] = OvertimeEntry::STATUS_PENDING;
        $data['created_by'] = auth()->id();

        OvertimeEntry::create($data);

        return redirect()->route('backend.indian-payroll.overtime.index')
            ->with('success', 'Overtime entry recorded. Approve it to have it paid in that month\'s payroll run.');
    }

    public function approve(OvertimeEntry $overtime)
    {
        canPerform('Manage Indian Payroll');

        abort_unless($overtime->status === OvertimeEntry::STATUS_PENDING, 403, 'Only pending entries can be actioned.');

        $overtime->update(['status' => OvertimeEntry::STATUS_APPROVED]);

        return back()->with('success', 'Overtime approved — it will be paid in that month\'s payroll run.');
    }

    public function reject(OvertimeEntry $overtime)
    {
        canPerform('Manage Indian Payroll');

        abort_unless($overtime->status === OvertimeEntry::STATUS_PENDING, 403, 'Only pending entries can be actioned.');

        $overtime->update(['status' => OvertimeEntry::STATUS_REJECTED]);

        return back()->with('success', 'Overtime entry rejected.');
    }
}
