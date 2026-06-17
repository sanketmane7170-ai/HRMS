<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Interview;
use Modules\Recruitment\Http\Requests\InterviewRequest;
use App\Notifications\Recruitment\InterviewScheduledNotification;
use App\Notifications\Recruitment\InterviewReminderNotification;
use App\Notifications\Recruitment\InterviewCancelledNotification;
use App\Notifications\Recruitment\InterviewRescheduledNotification;
use App\Services\Recruitment\CalendarService;
use App\Models\Recruitment\ExternalCandidate;
use Illuminate\Support\Facades\Notification;

class InterviewSchedulingController extends Controller
{
    protected $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
        view()->share('activeLink', 'recruitment-interviews');
    }

    /**
     * Display interview calendar/schedule.
     */
    public function index(Request $request): Renderable
    {
        $query = Interview::with(['application.job', 'application.user', 'interviewer']);

        // Apply filters
        if ($request->filled('interviewer_id')) {
            $query->where('interviewer_id', $request->interviewer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('scheduled_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('scheduled_at', '<=', $request->date_to);
        }

        // Default to current week if no date filter
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereBetween('scheduled_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        }

        $interviews = $query->orderBy('scheduled_at')->get();
        
        // Get available interviewers
        $interviewers = \App\Models\User::permission('conduct_interviews')
            ->orWhereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'hr', 'Hiring Manager']);
            })
            ->orderBy('name')
            ->get();

        return view('recruitment::interviews.index', compact('interviews', 'interviewers'));
    }

    /**
     * Show form for scheduling a new interview.
     */
    public function create(Request $request, ?int $applicationId = null): Renderable
    {
        $application = null;
        
        if ($applicationId) {
            $application = Application::with(['job', 'user'])->findOrFail($applicationId);
            $this->authorize('schedule_interview', $application);
        }

        // Get available interviewers
        $interviewers = \App\Models\User::permission('conduct_interviews')
            ->orWhereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'hr', 'Hiring Manager']);
            })
            ->orderBy('name')
            ->get();

        // Get suggested time slots
        $suggestedSlots = $this->getSuggestedTimeSlots();

        return view('recruitment::interviews.create', compact(
            'application', 
            'interviewers', 
            'suggestedSlots'
        ));
    }

    /**
     * Schedule a new interview.
     */
    public function store(InterviewRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $application = Application::findOrFail($validatedData['application_id']);
            
            // Create the interview
            $interview = Interview::create($validatedData);
            
            // Check for conflicts
            if ($interview->hasConflict()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview time conflicts with another scheduled interview.',
                    'conflicts' => $this->getConflicts($interview)
                ], 422);
            }

            // Create calendar events
            $calendarEventIds = $this->calendarService->createInterviewEvent($interview);
            if ($calendarEventIds) {
                $interview->update(['calendar_event_ids' => $calendarEventIds]);
            }

            // Update application stage if needed
            if ($application->stage === 'applied' || $application->stage === 'screening') {
                $application->update(['stage' => 'interview']);
            }

            // Send notifications
            $this->sendInterviewNotifications($interview);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Interview scheduled successfully.',
                'data' => [
                    'interview_id' => $interview->id,
                    'scheduled_at' => $interview->scheduled_at->toISOString(),
                    'interviewer' => $interview->interviewer->name,
                    'candidate' => $application->candidate_name ?? $application->user->name,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule interview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific interview.
     */
    public function show(int $interviewId): Renderable
    {
        $interview = Interview::with([
            'application.job',
            'application.user',
            'interviewer',
            'scheduler',
            'feedback'
        ])->findOrFail($interviewId);

        $this->authorize('view_interview', $interview);

        return view('recruitment::interviews.show', compact('interview'));
    }

    /**
     * Show form for editing an interview.
     */
    public function edit(int $interviewId): Renderable
    {
        $interview = Interview::with(['application.job', 'application.user'])
            ->findOrFail($interviewId);

        $this->authorize('schedule_interview', $interview->application);

        // Get available interviewers
        $interviewers = \App\Models\User::permission('conduct_interviews')
            ->orWhereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'hr', 'Hiring Manager']);
            })
            ->orderBy('name')
            ->get();

        return view('recruitment::interviews.edit', compact('interview', 'interviewers'));
    }

    /**
     * Update an existing interview.
     */
    public function update(InterviewRequest $request, int $interviewId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $interview = Interview::findOrFail($interviewId);
            $this->authorize('schedule_interview', $interview->application);

            $originalScheduledAt = $interview->scheduled_at;
            $validatedData = $request->validated();
            
            $interview->update($validatedData);

            // Check for conflicts if time changed
            if ($interview->hasConflict()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interview time conflicts with another scheduled interview.',
                    'conflicts' => $this->getConflicts($interview)
                ], 422);
            }

            // Update calendar events if time changed
            if ($originalScheduledAt != $interview->scheduled_at) {
                $this->calendarService->updateInterviewEvent($interview);
                
                // Reset reminder if time changed
                $interview->update(['reminder_sent_at' => null]);
            }

            // Send update notifications
            $this->sendInterviewUpdateNotifications($interview, $originalScheduledAt);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Interview updated successfully.',
                'data' => [
                    'interview_id' => $interview->id,
                    'scheduled_at' => $interview->scheduled_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update interview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an interview.
     */
    public function cancel(Request $request, int $interviewId): JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            $interview = Interview::findOrFail($interviewId);
            $this->authorize('schedule_interview', $interview->application);

            $interview->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->reason
            ]);

            // Cancel calendar events
            $this->calendarService->cancelInterviewEvent($interview);

            // Send cancellation notifications
            $this->sendCancellationNotifications($interview, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Interview cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel interview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark interview as completed.
     */
    public function markCompleted(int $interviewId): JsonResponse
    {
        try {
            $interview = Interview::findOrFail($interviewId);
            $this->authorize('conduct_interview', $interview);

            $interview->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Interview marked as completed.',
                'redirect_url' => route('recruitment.scoring.create', $interview->application_id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark interview as completed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get interviewer availability.
     */
    public function getAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'interviewer_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'duration' => 'nullable|integer|min:15|max:480'
        ]);

        $interviewerId = $request->interviewer_id;
        $date = $request->date;
        $duration = $request->get('duration', 60);

        $availability = $this->calculateAvailability($interviewerId, $date, $duration);

        return response()->json([
            'success' => true,
            'data' => $availability
        ]);
    }

    /**
     * Send interview reminders.
     */
    public function sendReminders(): JsonResponse
    {
        try {
            $sentCount = 0;
            
            // Send 24-hour reminders
            $interviews24h = Interview::needingReminders('24_hours')->with(['application', 'interviewer'])->get();
            foreach ($interviews24h as $interview) {
                $this->sendReminderNotification($interview, '24_hours');
                $sentCount++;
            }
            
            // Send 2-hour reminders
            $interviews2h = Interview::needingReminders('2_hours')->with(['application', 'interviewer'])->get();
            foreach ($interviews2h as $interview) {
                $this->sendReminderNotification($interview, '2_hours');
                $sentCount++;
            }
            
            // Send 30-minute reminders
            $interviews30m = Interview::needingReminders('30_minutes')->with(['application', 'interviewer'])->get();
            foreach ($interviews30m as $interview) {
                $this->sendReminderNotification($interview, '30_minutes');
                $sentCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Sent {$sentCount} interview reminders.",
                'details' => [
                    '24_hour_reminders' => $interviews24h->count(),
                    '2_hour_reminders' => $interviews2h->count(),
                    '30_minute_reminders' => $interviews30m->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send reminder notification for a specific interview and reminder type.
     */
    private function sendReminderNotification(Interview $interview, string $reminderType): void
    {
        try {
            // Send to candidate
            $candidate = $interview->application->user ?: new ExternalCandidate(
                $interview->application->candidate_email,
                $interview->application->candidate_name
            );
            
            $candidate->notify(new InterviewReminderNotification($interview, $reminderType));
            
            // Send to interviewer
            $interview->interviewer->notify(new InterviewReminderNotification($interview, $reminderType));
            
            // Send to additional interviewers if any
            if ($interview->additional_interviewers) {
                $additionalInterviewers = json_decode($interview->additional_interviewers, true);
                foreach ($additionalInterviewers as $interviewerId) {
                    if ($interviewer = \App\Models\User::find($interviewerId)) {
                        $interviewer->notify(new InterviewReminderNotification($interview, $reminderType));
                    }
                }
            }
            
            // Mark reminder as sent (this would need to be enhanced to track different reminder types)
            $interview->markReminderSent($reminderType);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send interview reminder', [
                'interview_id' => $interview->id,
                'reminder_type' => $reminderType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get interview statistics for dashboard.
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $dateFrom = $request->input('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $stats = [
            'total_interviews' => Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])->count(),
            'completed_interviews' => Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])
                ->where('status', 'completed')->count(),
            'cancelled_interviews' => Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])
                ->where('status', 'cancelled')->count(),
            'upcoming_interviews' => Interview::where('status', 'scheduled')
                ->where('scheduled_at', '>', now())->count(),
            'today_interviews' => Interview::today()->count(),
            'by_type' => Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])
                ->groupBy('type')
                ->selectRaw('type, count(*) as count')
                ->pluck('count', 'type'),
            'by_status' => Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])
                ->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get suggested time slots for interviews.
     */
    private function getSuggestedTimeSlots(): array
    {
        $slots = [];
        $workingHours = [9, 10, 11, 14, 15, 16]; // 9 AM to 5 PM, skip lunch
        
        // Next 7 business days
        for ($i = 1; $i <= 7; $i++) {
            $date = now()->addDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            foreach ($workingHours as $hour) {
                $slots[] = [
                    'datetime' => $date->setHour($hour)->setMinute(0)->setSecond(0),
                    'formatted' => $date->format('M j, Y \a\t g:i A')
                ];
            }
        }

        return array_slice($slots, 0, 10); // Return first 10 slots
    }

    /**
     * Calculate interviewer availability for a specific date.
     */
    private function calculateAvailability(int $interviewerId, string $date, int $duration): array
    {
        $workingHours = collect(range(9, 17))->map(function ($hour) use ($date) {
            return \Carbon\Carbon::parse($date)->setHour($hour)->setMinute(0)->setSecond(0);
        });

        $existingInterviews = Interview::where('interviewer_id', $interviewerId)
            ->whereDate('scheduled_at', $date)
            ->where('status', 'scheduled')
            ->get();

        $availableSlots = [];

        foreach ($workingHours as $slot) {
            $endTime = $slot->copy()->addMinutes($duration);
            
            // Check if slot conflicts with existing interviews
            $hasConflict = $existingInterviews->contains(function ($interview) use ($slot, $endTime) {
                $interviewStart = $interview->scheduled_at;
                $interviewEnd = $interview->end_time;
                
                return ($slot >= $interviewStart && $slot < $interviewEnd) ||
                       ($endTime > $interviewStart && $endTime <= $interviewEnd) ||
                       ($slot <= $interviewStart && $endTime >= $interviewEnd);
            });

            if (!$hasConflict && $slot->isFuture()) {
                $availableSlots[] = [
                    'datetime' => $slot->toISOString(),
                    'formatted' => $slot->format('g:i A')
                ];
            }
        }

        return $availableSlots;
    }

    /**
     * Get conflicts for an interview.
     */
    private function getConflicts(Interview $interview): array
    {
        $conflicts = Interview::where('interviewer_id', $interview->interviewer_id)
            ->where('status', 'scheduled')
            ->where('id', '!=', $interview->id)
            ->where(function ($query) use ($interview) {
                $startTime = $interview->scheduled_at;
                $endTime = $interview->end_time;
                
                $query->whereBetween('scheduled_at', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('scheduled_at', '<=', $startTime)
                            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$startTime]);
                      });
            })
            ->with(['application.job', 'application.user'])
            ->get();

        return $conflicts->map(function ($conflict) {
            return [
                'interview_id' => $conflict->id,
                'scheduled_at' => $conflict->scheduled_at->toISOString(),
                'duration' => $conflict->duration_text,
                'candidate' => $conflict->application->candidate_name ?? $conflict->application->user->name,
                'job_title' => $conflict->application->job->title ?? 'Unknown Position'
            ];
        })->toArray();
    }

    /**
     * Send interview scheduling notifications.
     */
    private function sendInterviewNotifications(Interview $interview): void
    {
        $application = $interview->application;
        
        // Notify candidate
        $candidate = $application->user ?: new ExternalCandidate(
            $application->candidate_email,
            $application->candidate_name
        );
        
        $candidate->notify(new InterviewScheduledNotification($interview));
        
        // Notify interviewer
        $interview->interviewer->notify(new InterviewScheduledNotification($interview));
        
        // Notify HR team
        $hrUsers = \App\Models\User::role(['admin', 'hr'])->get();
        Notification::send($hrUsers, new InterviewScheduledNotification($interview));
    }

    /**
     * Send interview update notifications.
     */
    private function sendInterviewUpdateNotifications(Interview $interview, $originalScheduledAt): void
    {
        if ($interview->scheduled_at != $originalScheduledAt) {
            $application = $interview->application;
            
            // Notify candidate about rescheduling
            $candidate = $application->user ?: new ExternalCandidate(
                $application->candidate_email,
                $application->candidate_name
            );
            
            $candidate->notify(new InterviewRescheduledNotification($interview, $originalScheduledAt));
            
            // Notify interviewer about rescheduling
            $interview->interviewer->notify(new InterviewRescheduledNotification($interview, $originalScheduledAt));
            
            // Notify additional interviewers if any
            if ($interview->additional_interviewers) {
                $additionalInterviewers = json_decode($interview->additional_interviewers, true);
                foreach ($additionalInterviewers as $interviewerId) {
                    if ($interviewer = \App\Models\User::find($interviewerId)) {
                        $interviewer->notify(new InterviewRescheduledNotification($interview, $originalScheduledAt));
                    }
                }
            }
        }
    }

    /**
     * Send interview cancellation notifications.
     */
    private function sendCancellationNotifications(Interview $interview, string $reason): void
    {
        $application = $interview->application;
        
        // Notify candidate
        $candidate = $application->user ?: new ExternalCandidate(
            $application->candidate_email,
            $application->candidate_name
        );
        
        $candidate->notify(new InterviewCancelledNotification($interview, $reason));
        
        // Notify interviewer
        $interview->interviewer->notify(new InterviewCancelledNotification($interview, $reason));
    }
}