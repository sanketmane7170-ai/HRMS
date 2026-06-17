<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;
use Modules\Apparel\Entities\ApparelRequest;
use Modules\GeneralRequest\Entities\GeneralRequest;
use Modules\Document\Entities\DocumentRequest;
use Modules\Document\Enums\DocumentRequestStatus;
use Modules\Payroll\Entities\AdvanceRequest;
use Modules\Expense\Entities\Expense;

class RequestsQueue extends Component
{

    public Collection $leavePending;
    public Collection $apparelRequests;
    public Collection $generalRequests;
    public Collection $documentRequest;
    public Collection $advanceRequest;
    public Collection $expenseRequest;

    public array $urls;

    public function __construct()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $this->urls = [
            'leave' => route('backend.leaves.index'),
            'apparel' => route('backend.apparel-request'),
            'general' => route('backend.admin.generalRequest'),
            'document' => route('backend.document-requests.index'),
            'advance' => route('backend.payroll.advance-request.index'),
            'expense' => route('backend.expense.index'),
        ];

        $this->leavePending = Leave::where('status', LeaveStatus::Pending)
            ->where(function ($query) use ($startOfWeek, $endOfWeek) {
                $query->whereBetween('start_date', [$startOfWeek, $endOfWeek])
                    ->orWhereBetween('end_date', [$startOfWeek, $endOfWeek]);
            })
            ->with('user')
            ->get();

        $this->apparelRequests = ApparelRequest::with('user')->where('status', 0)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->orderBy('created_at', 'desc')->get();

        $this->generalRequests = GeneralRequest::with('user')->where('status', 0)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->orderBy('created_at', 'desc')->get();
            
        $this->documentRequest = DocumentRequest::with('user')->where('status', DocumentRequestStatus::Pending)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
       
        $this->advanceRequest = AdvanceRequest::with('user')->where('status', 'pending')->whereBetween('created_at', [$startOfWeek, $endOfWeek])->orderBy('id','desc')->get();

        $this->expenseRequest = Expense::with('user')->where('status', 'pending')->whereBetween('created_at', [$startOfWeek, $endOfWeek])->orderBy('id','desc')->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.requests-queue');
    }
}
