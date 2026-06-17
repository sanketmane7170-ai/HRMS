<?php

namespace Modules\Recruitment\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\Offer;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\ApplicationLog;
use Carbon\Carbon;

class OfferPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        view()->share('activeLink', 'my-offers');
    }

    /**
     * Display employee's job offers
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(403, 'User not authenticated');
        }
        
        // Get all applications for this user that have offers
        $applications = Application::where('user_id', $user->id)
            ->with(['job', 'offers' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->whereHas('offers')
            ->get();

        // Get all offers for this user
        $offers = Offer::whereHas('application', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['application.job'])
        ->orderBy('created_at', 'desc')
        ->get();

        // Calculate offer statistics
        $totalOffers = $offers->count();
        $pendingOffers = $offers->where('status', 'pending')->count();
        $acceptedOffers = $offers->where('status', 'accepted')->count();
        $declinedOffers = $offers->where('status', 'declined')->count();

        // Check which offers have been sent
        foreach ($offers as $offer) {
            $wasSent = ApplicationLog::where('application_id', $offer->application_id)
                ->where('new_stage', 'offer_sent')
                ->where('description', 'like', '%Offer ID: ' . $offer->id . '%')
                ->exists();
            $offer->is_sent = $wasSent;
            
            // Calculate days remaining for response
            if ($offer->response_deadline) {
                $deadline = Carbon::parse($offer->response_deadline);
                $offer->days_remaining = $deadline->diffInDays(now(), false);
                $offer->is_expired = $deadline->isPast();
            }
        }

        return view('recruitment::employee.offers.index', compact(
            'offers', 
            'applications', 
            'totalOffers', 
            'pendingOffers', 
            'acceptedOffers', 
            'declinedOffers'
        ));
    }

    /**
     * Show specific offer details
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $offer = Offer::whereHas('application', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['application.job'])
        ->findOrFail($id);

        // Check if offer was sent
        $sentLog = ApplicationLog::where('application_id', $offer->application_id)
            ->where('new_stage', 'offer_sent')
            ->where('description', 'like', '%Offer ID: ' . $offer->id . '%')
            ->first();

        $offer->sent_at = $sentLog ? $sentLog->created_at : null;
        $offer->is_sent = (bool) $sentLog;

        // Calculate deadline info
        if ($offer->response_deadline) {
            $deadline = Carbon::parse($offer->response_deadline);
            $offer->days_remaining = $deadline->diffInDays(now(), false);
            $offer->is_expired = $deadline->isPast();
            $offer->deadline_color = $deadline->isPast() ? 'danger' : ($deadline->diffInDays() <= 3 ? 'warning' : 'success');
        }

        return view('recruitment::employee.offers.show', compact('offer'));
    }

    /**
     * Accept an offer
     */
    public function accept(Request $request, $id)
    {
        $user = Auth::user();
        
        $offer = Offer::whereHas('application', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        // Check if offer is still valid
        if ($offer->response_deadline && Carbon::parse($offer->response_deadline)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'This offer has expired and can no longer be accepted.'
            ], 400);
        }

        if ($offer->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This offer has already been responded to.'
            ], 400);
        }

        $offer->update([
            'status' => 'accepted',
            'responded_at' => now()
        ]);

        // Log the acceptance
        ApplicationLog::create([
            'application_id' => $offer->application_id,
            'previous_stage' => 'offer_sent',
            'new_stage' => 'offer_accepted',
            'action' => 'offer_accepted',
            'changed_by' => $user->id,
            'description' => 'Candidate accepted job offer (Offer ID: ' . $offer->id . ')',
            'created_at' => now()
        ]);

        // Update application stage
        // Update application stage
        $offer->application()->update(['stage' => 'hired']); // Aligned with ApplicationStageService (Sanket - REC-DATA-017)

        // Create Onboarding Record
        try {
            if (class_exists(\Modules\Onboarding\Entities\OnboardingRecord::class)) {
                \Modules\Onboarding\Entities\OnboardingRecord::create([
                    'application_id' => $offer->application_id,
                    'user_id' => $user->id,
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'department_id' => $offer->application->job->department_id ?? null,
                    'joining_date' => $offer->joining_date,
                    'status' => 'pending',
                    'progress_percent' => 0
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create onboarding record: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Congratulations! You have successfully accepted this job offer.'
        ]);
    }

    /**
     * Decline an offer
     */
    public function decline(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000'
        ]);

        $user = Auth::user();
        
        $offer = Offer::whereHas('application', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        if ($offer->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This offer has already been responded to.'
            ], 400);
        }

        $offer->update([
            'status' => 'declined',
            'responded_at' => now()
        ]);

        // Log the decline with reason
        $description = 'Candidate declined job offer (Offer ID: ' . $offer->id . ')';
        if ($request->reason) {
            $description .= ' - Reason: ' . $request->reason;
        }

        ApplicationLog::create([
            'application_id' => $offer->application_id,
            'previous_stage' => 'offer_sent',
            'new_stage' => 'offer_declined',
            'action' => 'offer_declined',
            'changed_by' => $user->id,
            'description' => $description,
            'metadata' => ['offer_id' => $offer->id, 'reason' => $request->reason],
            'created_at' => now()
        ]);

        // Update application stage
        // Update application stage
        $offer->application()->update(['stage' => 'rejected']); // Aligned with ApplicationStageService (Sanket - REC-DATA-017)

        return response()->json([
            'success' => true,
            'message' => 'You have declined this job offer. Thank you for your response.'
        ]);
    }

    /**
     * Download offer letter
     */
    public function download($id)
    {
        $user = Auth::user();
        
        $offer = Offer::whereHas('application', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        if (!$offer->offer_letter_url || !file_exists(storage_path('app/' . $offer->offer_letter_url))) {
            abort(404, 'Offer letter not found.');
        }

        return response()->download(
            storage_path('app/' . $offer->offer_letter_url),
            'Job_Offer_Letter_' . $offer->id . '.pdf'
        );
    }
}