<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\LeaveEncashment;

class LeaveEncashmentController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.leave-encashment');
    }

    public function index()
    {
        canPerform('Manage Indian Payroll');

        $entries = LeaveEncashment::with(['user', 'run'])->orderByDesc('id')->paginate(20);

        return view('indianpayroll::leave_encashment.index', compact('entries'));
    }

    public function create()
    {
        canPerform('Manage Indian Payroll');

        $employees = EmployeeProfile::with('user')->get();

        return view('indianpayroll::leave_encashment.create', compact('employees'));
    }

    public function store(Request $request)
    {
        canPerform('Manage Indian Payroll');

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'days' => 'required|numeric|min:0.5',
            'per_day_rate' => 'required|numeric|min:0',
            'taxable_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $data['amount'] = round($data['days'] * $data['per_day_rate'], 2);
        // Mid-service encashment is fully taxable by default; HR can override.
        $data['taxable_amount'] = $data['taxable_amount'] ?? $data['amount'];
        $data['status'] = LeaveEncashment::STATUS_PENDING;
        $data['created_by'] = auth()->id();

        LeaveEncashment::create($data);

        return redirect()->route('backend.indian-payroll.leave-encashment.index')
            ->with('success', 'Leave encashment recorded. Approve it to pay in that month\'s payroll run.');
    }

    public function approve(LeaveEncashment $encashment)
    {
        canPerform('Manage Indian Payroll');

        abort_unless($encashment->status === LeaveEncashment::STATUS_PENDING, 403, 'Only pending entries can be actioned.');

        $encashment->update(['status' => LeaveEncashment::STATUS_APPROVED]);

        return back()->with('success', 'Leave encashment approved.');
    }

    public function reject(LeaveEncashment $encashment)
    {
        canPerform('Manage Indian Payroll');

        abort_unless($encashment->status === LeaveEncashment::STATUS_PENDING, 403, 'Only pending entries can be actioned.');

        $encashment->update(['status' => LeaveEncashment::STATUS_REJECTED]);

        return back()->with('success', 'Leave encashment rejected.');
    }
}
