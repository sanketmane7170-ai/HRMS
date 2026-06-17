<?php

namespace Modules\Api\Http\Controllers\PayRoll;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Modules\Payroll\Entities\AdvanceRequest;

class AdvanceRequestController extends Controller
{
    /**
     * LIST – Employee Advance / Loan Requests
     */
    public function index()
    {
        $requests = AdvanceRequest::with('user')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Advance / Loan request list',
            'data'    => $requests,
        ], 200);
    }

    /**
     * CREATE
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'       => 'required|numeric|min:1',
            'loan_months'  => 'required|integer|min:1|max:12',
            'type'         => ['required', Rule::in(['Salary Advance', 'Loan'])],
            'start_month'  => 'required|date_format:Y-m',
            'reason'       => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request) {

            $installmentAmount = $request->amount / $request->loan_months;

            $advance = AdvanceRequest::create([
                'reference_number'     => str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'user_id'              => auth()->id(),
                'type'                 => $request->type,
                'reason'               => $request->reason,
                'amount'               => $request->amount,
                'loan_months'          => $request->loan_months,
                'instalments'          => $request->loan_months,
                'installment_amount'   => round($installmentAmount, 2),
                'installments_paid'    => 0,
                'installments_pending' => $request->loan_months,
                'start_month'          => Carbon::createFromFormat('Y-m', $request->start_month)->firstOfMonth(),
                'status'               => 'pending',
                'requested_date'       => now()->toDateString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Advance / Loan request submitted successfully',
                'data'    => $advance,
            ], 201);
        });
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $requestData = AdvanceRequest::with('user')
            ->where('user_id', auth()->id())
            ->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Advance / Loan request details',
            'data'    => $requestData,
        ], 200);
    }

    /**
     * UPDATE (pending only)
     */
    public function update(Request $request, $id)
    {
        $advance = AdvanceRequest::where('user_id', auth()->id())
            ->find($id);

        if (empty($advance)) {
            return response()->json([
                'success' => true,
                'message' => 'Advance / Loan request not found',
                'data'    => null,
            ], 200);
        }

        if ($advance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Approved or rejected request cannot be updated',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount'       => 'required|numeric|min:1',
            'loan_months'  => 'required|integer|min:1|max:12',
            'type'         => ['required', Rule::in(['Salary Advance', 'Loan'])],
            'start_month'  => 'required|date_format:Y-m',
            'reason'       => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $installmentAmount = $request->amount / $request->loan_months;

        $advance->update([
            'type'                 => $request->type,
            'reason'               => $request->reason,
            'amount'               => $request->amount,
            'loan_months'          => $request->loan_months,
            'instalments'          => $request->loan_months,
            'installment_amount'   => round($installmentAmount, 2),
            'installments_pending' => $request->loan_months,
            'start_month'          => Carbon::createFromFormat('Y-m', $request->start_month)->firstOfMonth(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Advance / Loan request updated successfully',
            'data'    => $advance,
        ], 200);
    }

    /**
     * DELETE (pending only)
     */
    public function destroy($id)
    {
        $advance = AdvanceRequest::where('user_id', auth()->id())
            ->find($id);
        if (empty($advance)) {
            return response()->json([
                'success' => true,
                'message' => 'Advance / Loan request not found',
                'data'    => null,
            ], 200);
        }

        if ($advance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Approved or rejected request cannot be deleted',
            ], 403);
        }

        $advance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Advance / Loan request deleted successfully',
        ], 200);
    }
}
