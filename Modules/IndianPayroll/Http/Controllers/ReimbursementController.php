<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\Reimbursement;

class ReimbursementController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.reimbursements');
    }

    public function index()
    {
        canPerform('Manage Indian Payroll');

        $reimbursements = Reimbursement::with(['user', 'run'])->orderByDesc('id')->paginate(20);

        return view('indianpayroll::reimbursement.index', compact('reimbursements'));
    }

    public function create()
    {
        canPerform('Manage Indian Payroll');

        $employees = EmployeeProfile::with('user')->get();

        return view('indianpayroll::reimbursement.create', compact('employees'));
    }

    public function store(Request $request)
    {
        canPerform('Manage Indian Payroll');

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reimbursement_type' => 'required|in:travel,hotel,mobile,fuel,internet,medical,other',
            'claim_amount' => 'required|numeric|min:1',
            'taxable_amount' => 'nullable|numeric|min:0|lte:claim_amount',
            'claim_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data['taxable_amount'] = $data['taxable_amount'] ?? 0;
        $data['status'] = Reimbursement::STATUS_PENDING;

        if ($request->hasFile('proof')) {
            $data['proof_path'] = $request->file('proof')->store('reimbursements', config('indianpayroll.document_disk'));
        }
        unset($data['proof']);

        Reimbursement::create($data);

        return redirect()->route('backend.indian-payroll.reimbursements.index')
            ->with('success', 'Reimbursement claim recorded. Approve it to have it paid in the next payroll run.');
    }

    public function approve(Request $request, Reimbursement $reimbursement)
    {
        canPerform('Manage Indian Payroll');

        abort_unless($reimbursement->status === Reimbursement::STATUS_PENDING, 403, 'Only pending claims can be actioned.');

        $data = $request->validate([
            'taxable_amount' => 'nullable|numeric|min:0',
        ]);

        $taxable = min((float) ($data['taxable_amount'] ?? $reimbursement->taxable_amount), (float) $reimbursement->claim_amount);

        $reimbursement->update([
            'taxable_amount' => $taxable,
            'status' => Reimbursement::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Reimbursement approved — it will be paid in the next payroll run for this employee.');
    }

    public function reject(Reimbursement $reimbursement)
    {
        canPerform('Manage Indian Payroll');

        abort_unless($reimbursement->status === Reimbursement::STATUS_PENDING, 403, 'Only pending claims can be actioned.');

        $reimbursement->update([
            'status' => Reimbursement::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Reimbursement rejected.');
    }

    public function downloadProof(Reimbursement $reimbursement)
    {
        canPerform('Manage Indian Payroll');

        abort_if(empty($reimbursement->proof_path), 404, 'No proof uploaded.');

        $disk = Storage::disk(config('indianpayroll.document_disk'));
        abort_unless($disk->exists($reimbursement->proof_path), 404, 'Proof file missing from storage.');

        return $disk->download($reimbursement->proof_path);
    }
}
