<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Recruitment\Entities\Offer;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\ApplicationLog;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Services\Recruitment\ApplicationStageService;
use Exception;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Recruitment\OfferCreatedNotification;
use App\Notifications\Recruitment\OfferSentNotification;

class OfferController extends Controller
{
    protected ApplicationStageService $stageService;

    public function __construct(ApplicationStageService $stageService)
    {
        $this->stageService = $stageService;
        view()->share('activeLink', 'recruitment-offers');
    }

    /**
     * Display a listing of offers.
     */
    public function index()
    {
        canPerform('View Offers');
        
        if (request()->ajax()) {
            return $this->datatable();
        }

        // Get offers for fallback display
        $offers = Offer::with(['application.job', 'application.user'])->get();
        
        // Calculate statistics
        $totalOffers = $offers->count();
        $acceptedOffers = $offers->where('status', 'accepted')->count();
        $declinedOffers = $offers->where('status', 'declined')->count();
        
        // Calculate sent offers by checking logs
        $sentOffers = 0;
        foreach ($offers as $offer) {
            $wasSent = ApplicationLog::where('application_id', $offer->application_id)
                ->where('new_stage', 'offer_sent')
                ->where('description', 'like', '%Offer ID: ' . $offer->id . '%')
                ->exists();
            if ($wasSent) {
                $sentOffers++;
            }
        }
        
        $statistics = compact('totalOffers', 'sentOffers', 'acceptedOffers', 'declinedOffers');
        
        return view('recruitment::offers.index', compact('offers', 'statistics'));
    }

    /**
     * DataTable for offers listing
     */
    public function datatable()
    {
        $offers = Offer::with(['application.job', 'application.user'])
            ->select(['id', 'application_id', 'salary', 'status', 'offer_date', 'response_deadline', 'created_at']);

        return DataTables::of($offers)
            ->addIndexColumn()
            ->addColumn('candidate', function ($offer) {
                $application = $offer->application;
                return $application->user ? $application->user->name : ($application->candidate_name ?? 'N/A');
            })
            ->addColumn('job_title', function ($offer) {
                return $offer->application->job->title ?? 'N/A';
            })
            ->addColumn('salary_display', function ($offer) {
                return $offer->salary ? '$' . number_format($offer->salary) : 'N/A';
            })
            ->addColumn('status_badge', function ($offer) {
                // Check if offer was sent by looking at logs
                $wasSent = ApplicationLog::where('application_id', $offer->application_id)
                    ->where('new_stage', 'offer_sent')
                    ->where('description', 'like', '%Offer ID: ' . $offer->id . '%')
                    ->exists();
                
                $badges = [
                    'pending' => 'badge-warning',
                    'accepted' => 'badge-success',
                    'declined' => 'badge-danger'
                ];
                $class = $badges[$offer->status] ?? 'badge-secondary';
                $statusText = ucfirst($offer->status);
                
                // Add sent indicator
                if ($wasSent && $offer->status === 'pending') {
                    $statusText = 'Sent (Awaiting Response)';
                    $class = 'badge-info';
                }
                
                return '<span class="badge ' . $class . '">' . $statusText . '</span>';
            })
            ->addColumn('offer_date_formatted', function ($offer) {
                return $offer->offer_date ? $offer->offer_date->format('M d, Y') : 'Not Created';
            })
            ->addColumn('sent_date', function ($offer) {
                // Get sent date from logs
                $sentLog = ApplicationLog::where('application_id', $offer->application_id)
                    ->where('new_stage', 'offer_sent')
                    ->where('description', 'like', '%Offer ID: ' . $offer->id . '%')
                    ->first();
                
                return $sentLog ? $sentLog->created_at->format('M d, Y g:i A') : '<span class="text-muted">Not Sent</span>';
            })
            ->addColumn('deadline_date', function ($offer) {
                if (!$offer->response_deadline) return 'No Deadline';
                
                $deadline = Carbon::parse($offer->response_deadline);
                $class = $deadline->isPast() ? 'text-danger' : ($deadline->diffInDays() <= 3 ? 'text-warning' : 'text-success');
                
                return '<span class="' . $class . '">' . $deadline->format('M d, Y') . '</span>';
            })
            ->addColumn('action', function ($offer) {
                $actions = '<div class="btn-group" role="group">';
                
                // View button
                $actions .= '<a href="' . route('recruitment.offers.show', $offer->id) . '" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>';
                
                // Edit button
                $actions .= '<a href="' . route('recruitment.offer-letters.create', ['application_id' => $offer->application_id, 'offer_id' => $offer->id]) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fa fa-edit"></i></a>';
                
                // Send offer button (only for pending offers that haven't been sent)
                if ($offer->status === 'pending') {
                    $wasSent = ApplicationLog::where('application_id', $offer->application_id)
                        ->where('new_stage', 'offer_sent')
                        ->where('description', 'like', '%Offer ID: ' . $offer->id . '%')
                        ->exists();
                    
                    if (!$wasSent) {
                        $actions .= '<button type="button" class="btn btn-sm btn-success send-offer" data-id="' . $offer->id . '" title="Send Offer"><i class="fa fa-paper-plane"></i></button>';
                    }
                }
                
                // Delete button (for all offers)
                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-offer" data-id="' . $offer->id . '" title="Delete"><i class="fa fa-trash"></i></button>';
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'sent_date', 'deadline_date', 'action'])
            ->make(true);
    }

    /**
     * Get offer statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => Offer::count(),
            'pending' => Offer::where('status', 'pending')->count(),
            'sent' => Offer::whereIn('status', ['sent', 'active'])->count(),
            'accepted' => Offer::where('status', 'accepted')->count(),
            'declined' => Offer::where('status', 'declined')->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Show the form for creating a new offer.
     */
    public function create()
    {
        canPerform('Create Offers');
        
        // Get applications that are eligible for offers
        // Include candidates who have completed interviews, are in offer stage, or are ready for hiring
        $applications = Application::with(['job', 'user', 'interviews'])
            ->whereIn('stage', ['interview', 'offer', 'hired', 'offer_pending', 'interview_completed'])
            ->orWhereHas('interviews', function($query) {
                $query->where('status', 'completed');
            })
            ->whereDoesntHave('offers', function($query) {
                $query->whereIn('status', ['sent', 'accepted']);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('recruitment::offers.create', compact('applications'));
    }

    /**
     * Store a newly created offer.
     */
    public function store(Request $request): RedirectResponse
    {
        canPerform('Create Offers');
        
        $validator = Validator::make($request->all(), [
        // BUG-REC-012 Fix: Add foreign key validation - Author: Sanket
        'application_id' => 'required|exists:recruitment_applications,id',
        // BUG-REC-011 Fix: Add salary validation - Author: Sanket
        'salary' => 'required|numeric|min:0',
        'salary_currency' => 'nullable|string|size:3',
        'salary_period' => 'nullable|string|in:monthly,annual,hourly',
        // BUG-REC-008 Fix: Ensure joining date is in future and response deadline is before joining date - Author: Sanket
        'joining_date' => 'nullable|date|after:today',
        'response_deadline' => 'nullable|date|after:today|before:joining_date',
        'terms_conditions' => 'nullable|string',
        'offer_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120'
    ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Initialize variable before try block
        $offerLetterPath = null;

        try {
            DB::beginTransaction();

            // Handle file upload
            if ($request->hasFile('offer_letter')) {
                $offerLetterPath = $request->file('offer_letter')->store('recruitment/offers', 'public');
            }

            // Get application details to populate offer fields
            $application = Application::with(['job', 'user'])->find($request->application_id);
            
            $offer = Offer::create([
                'application_id' => $request->application_id,
                'position' => $application->job->title,
                'department' => $application->job->department->name ?? null,
                'salary' => $request->salary,
                'currency' => $request->salary_currency ?? 'USD',
                'salary_type' => $request->salary_period ?? 'monthly',
                'joining_date' => $request->joining_date,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->additional_notes,
                'offer_letter_url' => $offerLetterPath,
                'offer_date' => now(),
                'response_deadline' => $request->response_deadline,
                'status' => 'pending'
            ]);

            // Auto-progress application stage to 'offer'
            $this->stageService->progressToStage($application, 'offer', 'Offer creation');

            // Log the offer creation
            ApplicationLog::create([
                'application_id' => $request->application_id,
                'action' => 'offer_created',
                'description' => 'Job offer created with salary $' . number_format($request->salary),
                'changed_by' => auth()->id(),
                'metadata' => [
                    'offer_id' => $offer->id,
                    'salary' => $request->salary
                ]
            ]);

            // Notify the employee about the offer (Decoupled from transaction - Sanket)
            // Only notify if NOT sending immediately (to avoid double notification)
            $shouldNotifyCreation = !$request->has('send_immediately') && $application->user;

            DB::commit();

            // Send notification safely
            if ($shouldNotifyCreation) {
                try {
                    $application->user->notify(new OfferCreatedNotification($offer));
                } catch (Exception $e) {
                    \Log::error('Failed to send offer creation notification: ' . $e->getMessage());
                }
            }

            // Handle "Send Immediately" option
            if ($request->has('send_immediately')) {
                // Call the send method logic physically or redirect to it?
                // Calling logic directly is safer to avoid another request cycle
                try {
                    // Reuse the send logic (extracted or localized)
                    // Logic from send():
                    
                    // 1. Update status
                    $offer->update(['status' => 'sent']); // This works because we are out of previous transaction

                    // 2. Send email
                    Mail::to($offer->application->candidate_email)->send(new \App\Mail\Recruitment\OfferLetter($offer));

                    // 3. Notify candidate (User or External)
                    if ($offer->application->user) {
                        $offer->application->user->notify(new OfferSentNotification($offer));
                    } elseif ($offer->application->candidate_email) {
                        $candidate = new \App\Models\Recruitment\ExternalCandidate($offer->application->candidate_email, $offer->application->candidate_name);
                        $candidate->notify(new OfferSentNotification($offer));
                    }

                    // 4. Log
                    ApplicationLog::create([
                        'application_id' => $offer->application_id,
                        'action' => 'offer_sent',
                        'description' => 'Job offer sent immediately after creation',
                        'changed_by' => auth()->id(),
                        'metadata' => ['offer_id' => $offer->id]
                    ]);

                     return redirect()->route('recruitment.offers.index')
                        ->with('success', 'Offer created and sent to candidate successfully!');

                } catch (Exception $e) {
                    \Log::error('Failed to send offer immediately: ' . $e->getMessage());
                    return redirect()->route('recruitment.offers.show', $offer->id)
                        ->with('warning', 'Offer created but failed to send email. Please try sending manually.');
                }
            }

            return redirect()->route('recruitment.offers.show', $offer->id)
                ->with('success', 'Offer created successfully! You can now review and send the offer.');

        } catch (Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if ($offerLetterPath && Storage::disk('public')->exists($offerLetterPath)) {
                Storage::disk('public')->delete($offerLetterPath);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to create offer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified offer.
     */
    public function show($id)
    {
        canPerform('View Offers');
        
        $offer = Offer::with(['application.job', 'application.user', 'application.logs'])
            ->findOrFail($id);
            
        return view('recruitment::offers.show', compact('offer'));
    }

    /**
     * Show the form for editing the specified offer.
     */
    public function edit($id)
    {
        canPerform('Create Offers'); // Enforce permission (Sanket - REC-SEC-015)
        $offer = Offer::findOrFail($id);
        $applications = Application::with(['job', 'user'])
            ->where('stage', 'offer')
            ->orWhere('stage', 'interview')
            ->orWhere('id', $offer->application_id)
            ->get();
            
        return view('recruitment::offers.edit', compact('offer', 'applications'));
    }

    /**
     * Update the specified offer.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        canPerform('Create Offers'); // Enforce permission (Sanket - REC-SEC-015)
        $offer = Offer::findOrFail($id);

        // Check if offer can be edited
        if (in_array($offer->status, ['accepted', 'declined'])) {
            return redirect()->back()
                ->with('error', 'Cannot edit an offer that has been accepted or declined.');
        }

        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:recruitment_applications,id',
            'offered_salary' => 'required|numeric|min:0',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'start_date' => 'nullable|date|after:today',
            'response_deadline' => 'nullable|date|after:today', // Fixed: expires_at -> response_deadline
            'benefits' => 'nullable|array',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'offer_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldOfferLetterPath = $offer->offer_letter_url; // Fixed: offer_letter_path -> offer_letter_url

            // Handle file upload
            if ($request->hasFile('offer_letter')) {
                $offer->offer_letter_url = $request->file('offer_letter')->store('recruitment/offers', 'public');
            }

            $offer->update([
                'application_id' => $request->application_id,
                'salary' => $request->offered_salary,
                'position' => $request->job_title,
                'department' => $request->department,
                'start_date' => $request->start_date,
                'response_deadline' => $request->response_deadline, // Fixed: expires_at -> response_deadline
                'benefits' => $request->benefits ? json_encode($request->benefits) : null,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->notes
            ]);

            // Log the update
            ApplicationLog::create([
                'application_id' => $offer->application_id,
                'action' => 'offer_updated',
                'description' => 'Job offer updated',
                'changed_by' => auth()->id(),
                'metadata' => $request->only(['offered_salary', 'job_title', 'department'])
            ]);

            DB::commit();

            // Delete old offer letter only after successful commit - Sanket
            if ($request->hasFile('offer_letter') && $oldOfferLetterPath && Storage::disk('public')->exists($oldOfferLetterPath)) {
                Storage::disk('public')->delete($oldOfferLetterPath);
            }

            return redirect()->route('recruitment.offers.show', $offer->id)
                ->with('success', 'Offer updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to update offer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Send offer to candidate
     */
    public function send(Request $request, $id)
    {
        canPerform('Create Offers'); // Enforce permission (Sanket - REC-SEC-015)
        $offer = Offer::with(['application.job', 'application.user'])->findOrFail($id);
        
        if ($offer->status !== 'pending') {
            return response()->json([
                'success' => false, 
                'message' => "Only pending offers can be sent. Current status: {$offer->status}"
            ], 422);
        }

        // Note: Duplicate send check removed for now due to ApplicationLog schema limitations

        try {
            DB::beginTransaction();

            // Update offer status
            $offer->update(['status' => 'sent']);

            // Log the send action
            ApplicationLog::create([
                'application_id' => $offer->application_id,
                'action' => 'offer_sent',
                'description' => 'Job offer sent to candidate (Offer ID: ' . $offer->id . ')',
                'changed_by' => auth()->id(),
                'metadata' => ['offer_id' => $offer->id]
            ]);

            DB::commit();

            // Send email notification with offer letter - Author: Sanket
            // Decoupled from transaction to prevent rollback on network failure
            try {
                Mail::to($offer->application->candidate_email)->send(new \App\Mail\Recruitment\OfferLetter($offer));

                // Notify the candidate about the sent offer (Enhanced - Sanket)
                if ($offer->application->user) {
                    $offer->application->user->notify(new OfferSentNotification($offer));
                } elseif ($offer->application->candidate_email) {
                    $candidate = new \App\Models\Recruitment\ExternalCandidate($offer->application->candidate_email, $offer->application->candidate_name);
                    $candidate->notify(new OfferSentNotification($offer));
                }
            } catch (Exception $e) {
                \Log::error('Failed to send offer letter email: ' . $e->getMessage());
                return response()->json([
                    'success' => true, 
                    'message' => 'Offer status updated to Sent, but email failed to send. Please contact candidate manually.'
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Offer sent successfully']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to send offer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Accept offer (candidate action)
     */
    public function accept(Request $request, $id)
    {
        canPerform('Create Offers'); // Admin override permission (Sanket - REC-SEC-016)
        $offer = Offer::with(['application'])->findOrFail($id);
        
        if ($offer->status !== 'sent') {
            return response()->json(['success' => false, 'message' => 'Only sent offers can be accepted'], 422);
        }

        if ($offer->response_deadline && Carbon::parse($offer->response_deadline)->isPast()) {
            return response()->json(['success' => false, 'message' => 'This offer has expired'], 422);
        }

        try {
            DB::beginTransaction();

            $offer->update([
                'status' => 'accepted',
                'responded_at' => now()
            ]);

            // Auto-progress application stage to 'hired'
            $this->stageService->progressToStage($offer->application, 'hired', 'Offer acceptance');

            // Log the acceptance
            ApplicationLog::create([
                'application_id' => $offer->application_id,
                'action' => 'offer_accepted',
                'description' => 'Job offer accepted by candidate',
                'changed_by' => auth()->id(),
                'metadata' => ['offer_id' => $offer->id]
            ]);

            // Create Onboarding Record - Moved inside transaction by Sanket
            \Modules\Onboarding\Entities\OnboardingRecord::create([
                'application_id' => $offer->application_id,
                'user_id' => $offer->application->user_id,
                'full_name' => $offer->application->user ? $offer->application->user->name : $offer->application->candidate_name,
                'email' => $offer->application->user ? $offer->application->user->email : $offer->application->candidate_email,
                'department_id' => $offer->application->job->department_id ?? null,
                'joining_date' => $offer->start_date,
                'status' => 'pending',
                'progress_percent' => 0
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Offer accepted successfully']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to accept offer'], 500);
        }
    }

    /**
     * Decline offer (candidate action)
     */
    public function decline(Request $request, $id)
    {
        canPerform('Create Offers'); // Admin override permission (Sanket - REC-SEC-016)
        $offer = Offer::with(['application'])->findOrFail($id);
        
        if ($offer->status !== 'sent') {
            return response()->json(['success' => false, 'message' => 'Only sent offers can be declined'], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $offer->update([
                'status' => 'declined',
                'responded_at' => now(),
                'decline_reason' => $request->reason
            ]);

            // Update application stage
            $offer->application->update(['stage' => 'rejected']);

            // Log the decline
            ApplicationLog::create([
                'application_id' => $offer->application_id,
                'action' => 'offer_declined',
                'description' => 'Job offer declined by candidate' . ($request->reason ? ': ' . $request->reason : ''),
                'changed_by' => auth()->id(),
                'metadata' => [
                    'offer_id' => $offer->id,
                    'reason' => $request->reason
                ]
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Offer declined']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to decline offer'], 500);
        }
    }

    /**
     * Withdraw offer
     */
    public function withdraw(Request $request, $id)
    {
        canPerform('Create Offers'); // Enforce permission (Sanket - REC-SEC-015)
        $offer = Offer::findOrFail($id);
        
        if (in_array($offer->status, ['accepted', 'declined', 'withdrawn'])) {
            return response()->json(['success' => false, 'message' => 'Cannot withdraw this offer'], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $offer->update([
                'status' => 'withdrawn',
                'withdraw_reason' => $request->reason
            ]);

            // Log the withdrawal
            ApplicationLog::create([
                'application_id' => $offer->application_id,
                'action' => 'offer_withdrawn',
                'description' => 'Job offer withdrawn: ' . $request->reason,
                'changed_by' => auth()->id(),
                'metadata' => [
                    'offer_id' => $offer->id,
                    'reason' => $request->reason
                ]
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Offer withdrawn successfully']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to withdraw offer'], 500);
        }
    }

    /**
     * Remove the specified offer.
     */
    public function destroy($id)
    {
        try {
            canPerform('Create Offers'); // Enforce permission (Sanket - REC-SEC-015)
            $offer = Offer::findOrFail($id);
            
            // Delete offer letter file
            if ($offer->offer_letter_url && Storage::disk('public')->exists($offer->offer_letter_url)) {
                Storage::disk('public')->delete($offer->offer_letter_url);
            }
            
            // Log the deletion
            ApplicationLog::create([
                'application_id' => $offer->application_id,
                'action' => 'offer_deleted',
                'description' => 'Job offer deleted',
                'changed_by' => auth()->id(),
                'metadata' => ['offer_id' => $offer->id]
            ]);
            
            $offer->delete();

            return response()->json(['success' => true, 'message' => 'Offer deleted successfully']);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete offer'], 500);
        }
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $offers = Offer::with(['application.job', 'application.user'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->application_id, function ($query, $applicationId) {
                    return $query->where('application_id', $applicationId);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json(['success' => true, 'data' => $offers]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch offers'], 500);
        }
    }

    public function apiShow($id): JsonResponse
    {
        try {
            $offer = Offer::with(['application.job', 'application.user', 'createdBy'])
                ->findOrFail($id);
            
            return response()->json(['success' => true, 'data' => $offer]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Offer not found'], 404);
        }
    }

    public function apiStore(Request $request): JsonResponse
    {
        canPerform('Create Offers');
        
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:recruitment_applications,id',
            'salary' => 'required|numeric|min:0',
            'salary_currency' => 'nullable|string|size:3',
            'salary_period' => 'nullable|string|in:monthly,annual,hourly',
            'joining_date' => 'nullable|date|after:today',
            'response_deadline' => 'nullable|date|after:today|before:joining_date',
            'terms_conditions' => 'nullable|string',
            'offer_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $offerLetterPath = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('offer_letter')) {
                $offerLetterPath = $request->file('offer_letter')->store('recruitment/offers', 'public');
            }

            $application = Application::with(['job', 'user'])->find($request->application_id);
            
            $offer = Offer::create([
                'application_id' => $request->application_id,
                'position' => $application->job->title,
                'department' => $application->job->department->name ?? null,
                'salary' => $request->salary,
                'currency' => $request->salary_currency ?? 'USD',
                'salary_type' => $request->salary_period ?? 'monthly',
                'joining_date' => $request->joining_date,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->additional_notes,
                'offer_letter_url' => $offerLetterPath,
                'offer_date' => now(),
                'response_deadline' => $request->response_deadline,
                'status' => 'pending'
            ]);

            $this->stageService->progressToStage($application, 'offer', 'Offer creation');

            ApplicationLog::create([
                'application_id' => $request->application_id,
                'action' => 'offer_created',
                'description' => 'Job offer created with salary $' . number_format($request->salary),
                'changed_by' => auth()->id(),
                'metadata' => ['offer_id' => $offer->id, 'salary' => $request->salary]
            ]);

            DB::commit();

            // Handle Send Immediately if requested
            if ($request->has('send_immediately')) {
               try {
                    $offer->update(['status' => 'sent']);
                    Mail::to($offer->application->candidate_email)->send(new \App\Mail\Recruitment\OfferLetter($offer));

                    if ($offer->application->user) {
                        $offer->application->user->notify(new OfferSentNotification($offer));
                    } elseif ($offer->application->candidate_email) {
                        $candidate = new \App\Models\Recruitment\ExternalCandidate($offer->application->candidate_email, $offer->application->candidate_name);
                        $candidate->notify(new OfferSentNotification($offer));
                    }

                    ApplicationLog::create([
                        'application_id' => $offer->application_id,
                        'action' => 'offer_sent',
                        'description' => 'Job offer sent immediately after creation',
                        'changed_by' => auth()->id(),
                        'metadata' => ['offer_id' => $offer->id]
                    ]);
                    
                    return response()->json(['success' => true, 'message' => 'Offer created and sent successfully', 'data' => $offer], 201);
               } catch (Exception $e) {
                   \Log::error('API: Failed to send offer immediately: ' . $e->getMessage());
                   return response()->json(['success' => true, 'message' => 'Offer created but failed to send email', 'data' => $offer], 201);
               }
            } else {
                 // Notify creation if not sending immediately
                 if ($application->user) {
                    try {
                        $application->user->notify(new OfferCreatedNotification($offer));
                    } catch (Exception $e) {
                         \Log::error('API: Failed to send offer creation notification: ' . $e->getMessage());
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Offer created successfully', 'data' => $offer], 201);

        } catch (Exception $e) {
            DB::rollBack();
            if ($offerLetterPath && Storage::disk('public')->exists($offerLetterPath)) {
                Storage::disk('public')->delete($offerLetterPath);
            }
            return response()->json(['success' => false, 'message' => 'Failed to create offer: ' . $e->getMessage()], 500);
        }
    }

    public function apiUpdate(Request $request, $id): JsonResponse
    {
        canPerform('Create Offers');
        $offer = Offer::findOrFail($id);

        if (in_array($offer->status, ['accepted', 'declined'])) {
            return response()->json(['success' => false, 'message' => 'Cannot edit an offer that has been accepted or declined.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:recruitment_applications,id',
            'offered_salary' => 'required|numeric|min:0',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'start_date' => 'nullable|date|after:today',
            'response_deadline' => 'nullable|date|after:today',
            'benefits' => 'nullable|array',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'offer_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $oldOfferLetterPath = $offer->offer_letter_url;

            if ($request->hasFile('offer_letter')) {
                $offer->offer_letter_url = $request->file('offer_letter')->store('recruitment/offers', 'public');
            }

            $offer->update([
                'application_id' => $request->application_id,
                'salary' => $request->offered_salary,
                'position' => $request->job_title,
                'department' => $request->department,
                'start_date' => $request->start_date,
                'response_deadline' => $request->response_deadline,
                'benefits' => $request->benefits ? json_encode($request->benefits) : null,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->notes
            ]);

            ApplicationLog::create([
                'application_id' => $offer->application_id,
                'action' => 'offer_updated',
                'description' => 'Job offer updated',
                'changed_by' => auth()->id(),
                'metadata' => json_encode($request->only(['offered_salary', 'job_title', 'department']))
            ]);

            DB::commit();

            if ($request->hasFile('offer_letter') && $oldOfferLetterPath && Storage::disk('public')->exists($oldOfferLetterPath)) {
                Storage::disk('public')->delete($oldOfferLetterPath);
            }

            return response()->json(['success' => true, 'message' => 'Offer updated successfully', 'data' => $offer]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update offer: ' . $e->getMessage()], 500);
        }
    }

    public function apiDestroy($id): JsonResponse
    {
        return $this->destroy($id);
    }

    public function apiSend(Request $request, $id): JsonResponse
    {
        return $this->send($request, $id);
    }

    public function apiAccept(Request $request, $id): JsonResponse
    {
        return $this->accept($request, $id);
    }

    public function apiDecline(Request $request, $id): JsonResponse
    {
        return $this->decline($request, $id);
    }

    public function apiWithdraw(Request $request, $id): JsonResponse
    {
        return $this->withdraw($request, $id);
    }

    public function apiPendingOffers(): JsonResponse
    {
        try {
            canPerform('View Offers'); // Enforce permission (Sanket - REC-SEC-016)
            $offers = Offer::with(['application.job', 'application.user'])
                ->where('status', 'sent')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return response()->json(['success' => true, 'data' => $offers]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch pending offers'], 500);
        }
    }



}
