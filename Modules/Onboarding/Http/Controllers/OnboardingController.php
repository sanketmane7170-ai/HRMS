<?php

namespace Modules\Onboarding\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Onboarding\Entities\OnboardingRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Onboarding\Imports\NewHiresImport;
use Illuminate\Support\Facades\Validator;
use App\Models\Department; // Added by Sanket
use App\Models\Division;   // Added by Sanket
use Spatie\Permission\Models\Role;

class OnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Manage Onboarding');
    }

    /**
     * Sync Hired Applications from Recruitment Module
     * BUG-ONB-018 Fix: Improved error handling and notification
     */
    private function syncRecruitmentData()
    {
        try {
            if (class_exists(\Modules\Recruitment\Entities\Application::class)) {
                $hiredApplications = \Modules\Recruitment\Entities\Application::with('job')
                    ->where('stage', 'hired')
                    ->get();

                $syncedCount = 0;
                foreach ($hiredApplications as $app) {
                    $onboardingRecord = OnboardingRecord::firstOrCreate(
                        ['application_id' => $app->id],
                        [
                            'user_id' => $app->user_id,
                            'full_name' => $app->candidate_name,
                            'email' => $app->candidate_email,
                            'department_id' => $app->job->department_id ?? null,
                            'joining_date' => $app->availability_date ?? \Carbon\Carbon::now(),
                            'status' => 'pending',
                            'progress_percent' => 0
                        ]
                    );
                    
                    // BUG-ONB-004 Fix: Send notification for newly synced records
                    // BUG-ONB-014 Fix: Queue notification to prevent blocking
                    if ($onboardingRecord->wasRecentlyCreated) {
                        $syncedCount++;
                        dispatch(function() use ($onboardingRecord) {
                            try {
                                if ($onboardingRecord->user_id) {
                                    $user = User::find($onboardingRecord->user_id);
                                    if ($user) {
                                        $user->notify(new \Modules\Onboarding\Notifications\OnboardingStartedNotification($onboardingRecord));
                                    }
                                } else {
                                    \Illuminate\Support\Facades\Notification::route('mail', $onboardingRecord->email)
                                        ->notify(new \Modules\Onboarding\Notifications\OnboardingStartedNotification($onboardingRecord));
                                }
                            } catch (\Exception $notifError) {
                                \Log::error('Failed to send onboarding notification during sync: ' . $notifError->getMessage());
                            }
                        })->afterResponse();
                    }
                }
                
                if ($syncedCount > 0) {
                    \Log::info('Onboarding sync completed', ['synced_count' => $syncedCount]);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Onboarding Sync Error: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // BUG-ONB-018 Fix: Store sync failure for admin notification
            cache()->put('onboarding_sync_failed', true, now()->addHours(24));
        }
    }

    /**
     * Display the onboarding dashboard.
     */
    public function index()
    {
        // BUG-ONB-016 Fix: Sync now runs via scheduled job (hourly)
        // Only run on-demand if explicitly needed or in development
        if (config('app.env') === 'local' || request()->has('force_sync')) {
            $this->syncRecruitmentData();
        }

        view()->share('activeLink', 'onboarding-dashboard');

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // 1. Top Cards Stats
        $stats = [
            'new_hires_today' => OnboardingRecord::whereDate('created_at', $today)->count(),
            'new_hires_this_month' => OnboardingRecord::where('created_at', '>=', $startOfMonth)->count(),
            'new_hires_last_month' => OnboardingRecord::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count(),
            'employees_on_file' => User::count(),
        ];

        // 2. Onboarding Status (Right Panel)
        $statusCounts = [
            'new_hires_in_queue' => OnboardingRecord::where('status', 'pending')->count(),
            'incomplete_records' => OnboardingRecord::where('status', 'in_progress')->count(),
            'past_due' => OnboardingRecord::where('joining_date', '<', $today)->where('status', '!=', 'converted')->count(),
        ];

        // 3. New Hires List (Bottom Table)
        $recentHires = OnboardingRecord::with(['department', 'division'])->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        $departments = Department::all(); // Branch level
        $divisions = Division::all();     // Department level, as per user requirement

        // 4. Chart Data (Monthly Offers & Hires) - Last 6 months
        $months = collect([]);
        for ($i = 5; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i)->format('M'));
        }
        
        // BUG-ONB-006 Fix: Implement real chart data instead of mock data
        $chartData = [
            'labels' => $months->toArray(),
            'hired' => [],
            'joined' => []
        ];
        
        // Get actual data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $chartData['hired'][] = OnboardingRecord::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $chartData['joined'][] = OnboardingRecord::where('status', 'converted')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->count();
        }

        return view('onboarding::index', compact('stats', 'statusCounts', 'recentHires', 'chartData', 'departments', 'divisions'));
    }

    public function newHires(Request $request)
    {
        // BUG-ONB-016 Fix: Sync now runs via scheduled job (hourly)
        // Only run on-demand if explicitly needed or in development
        if (config('app.env') === 'local' || request()->has('force_sync')) {
            $this->syncRecruitmentData();
        }
        
        view()->share('activeLink', 'onboarding-new-hires');
        
        $query = OnboardingRecord::with(['department', 'division'])->orderBy('created_at', 'desc');

        // Apply filters if present - Added by Sanket
        if ($request->has('status')) {
            if ($request->status == 'incomplete') {
                $query->where('status', 'in_progress');
            } else {
                $query->where('status', $request->status);
            }
        }
        
        $newHires = $query->paginate(10);
        // BUG-ONB-012 Fix: Optimize user query - only select needed fields
        $employees = User::select('id', 'name', 'email')->get();
        $departments = Department::all();
        $divisions = Division::all(); // Added by Sanket
        
        return view('onboarding::new_hires', compact('newHires', 'employees', 'departments', 'divisions'));
    }

    /**
     * Store a manually added new hire.
     */
    public function storeNewHire(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:onboarding_records,email',
            'joining_date' => 'required|date|after_or_equal:today',
            'department_id' => 'nullable|integer',
            'division_id' => 'nullable|integer', // Added by Sanket
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $onboardingRecord = OnboardingRecord::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'joining_date' => $request->joining_date,
            'department_id' => $request->department_id,
            'division_id' => $request->division_id, // Added by Sanket
            'status' => 'pending',
            'progress_percent' => 0,
            'application_id' => null, // Manual entry
            'user_id' => null // Can link later
        ]);

        // BUG-ONB-014 Fix: Queue notification to prevent blocking - Author: Sanket
        dispatch(function() use ($onboardingRecord) {
            try {
                // If user_id exists, send to user, otherwise send to email
                if ($onboardingRecord->user_id) {
                    $user = User::find($onboardingRecord->user_id);
                    if ($user) {
                        $user->notify(new \Modules\Onboarding\Notifications\OnboardingStartedNotification($onboardingRecord));
                    }
                } else {
                    // Send to email directly for external candidates
                    \Illuminate\Support\Facades\Notification::route('mail', $onboardingRecord->email)
                        ->notify(new \Modules\Onboarding\Notifications\OnboardingStartedNotification($onboardingRecord));
                }
                
                \Log::info('Onboarding started notification sent', [
                    'onboarding_id' => $onboardingRecord->id,
                    'email' => $onboardingRecord->email
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send onboarding started notification: ' . $e->getMessage());
            }
        })->afterResponse();

        return redirect()->back()->with('success', 'New hire added successfully.');
    }

    /**
     * Import new hires from Excel.
     * Modified by Sanket to provide accurate feedback count.
     */
    public function importNewHires(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new NewHiresImport;
            Excel::import($import, $request->file('file'));
            
            if ($import->importedCount > 0) {
                return redirect()->back()->with('success', $import->importedCount . ' new hires imported successfully.');
            } else {
                return redirect()->back()->with('error', 'No new records were imported. Please check if headers match or if records already exist.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Download a sample Excel template for importing new hires.
     * Added by Sanket for better user experience.
     */
    public function downloadImportTemplate()
    {
        $headers = ['full_name', 'email', 'joining_date'];
        $data = [
            ['John Doe', 'john.doe@example.com', '2026-02-15'],
            ['Jane Smith', 'jane.smith@example.com', '2026-03-01'],
        ];

        $callback = function() use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'new_hires_template.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="new_hires_template.csv"',
        ]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        view()->share('activeLink', 'onboarding-new-hires');
        $record = OnboardingRecord::findOrFail($id);
        
        $departments = Department::all();
        $divisions = Division::all();

        // Fetch documents from linked user
        $documents = $record->user ? $record->user->documents : [];
        
        return view('onboarding::show', compact('record', 'departments', 'divisions', 'documents'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, $id)
    {
        $record = OnboardingRecord::findOrFail($id);
        
        // Custom validation for joining_date - Author: Sanket (BUG-ONB-002 Fix)
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:onboarding_records,email,' . $id, // BUG-ONB-005, BUG-ONB-011 Fix
            'department_id' => 'nullable|integer|exists:departments,id', // BUG-ONB-013 Fix
            'division_id' => 'nullable|integer|exists:divisions,id', // BUG-ONB-013 Fix
            'joining_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($record) {
                    // Only validate future dates if the date is being changed
                    if ($record->joining_date != $value && \Carbon\Carbon::parse($value)->isPast()) {
                        $fail('The joining date must be today or a future date when changing it.');
                    }
                }
            ],
            'status' => 'required|in:pending,in_progress,completed,converted', // BUG-ONB-017 Fix
            'progress_percent' => 'required|integer|min:0|max:100',
        ]);
        
        // Store old status for comparison - Author: Sanket
        $oldStatus = $record->status;
        
        // BUG-ONB-001 Fix: Use only() to prevent mass assignment vulnerability
        $record->update($request->only([
            'full_name',
            'email',
            'department_id',
            'division_id',
            'joining_date',
            'status',
            'progress_percent'
        ]));

        // Send notifications based on status changes - Author: Sanket
        // BUG-ONB-014 Fix: Queue notification to prevent blocking
        if ($oldStatus !== 'in_progress' && $request->status === 'in_progress') {
            dispatch(function() use ($record) {
                try {
                    if ($record->user_id) {
                        $user = User::find($record->user_id);
                        if ($user) {
                            $user->notify(new \Modules\Onboarding\Notifications\DocumentUploadRequiredNotification($record));
                        }
                    } else {
                        \Illuminate\Support\Facades\Notification::route('mail', $record->email)
                            ->notify(new \Modules\Onboarding\Notifications\DocumentUploadRequiredNotification($record));
                    }
                    
                    \Log::info('Document upload notification sent', [
                        'onboarding_id' => $record->id,
                        'email' => $record->email
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send onboarding notification: ' . $e->getMessage());
                }
            })->afterResponse();
        }

        return redirect()->back()->with('success', 'Employee details updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     */
    public function destroy($id)
    {
        // BUG-ONB-010 Fix: Add authorization check
        $record = OnboardingRecord::findOrFail($id);
        
        // Only admins and HR managers can delete
        if (!auth()->user()->hasAnyRole(['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized: Only admins can delete onboarding records.');
        }
        
        // BUG-ONB-009 Fix: Add transaction and validation
        DB::beginTransaction();
        try {
            // Check for related data
            if ($record->user_id) {
                $user = User::find($record->user_id);
                if ($user && $user->documents()->count() > 0) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Cannot delete: Employee has uploaded documents. Please remove documents first.');
                }
            }
            
            // BUG-ONB-015 Fix: Add logging
            \Log::info('Onboarding record deleted', [
                'record_id' => $id,
                'deleted_by' => auth()->id(),
                'record_data' => $record->toArray()
            ]);
            
            $record->delete();
            DB::commit();
            
            return redirect()->route('onboarding.new-hires')->with('success', 'Employee removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete onboarding record: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }

    /**
     * Upload a document for the employee (Admin).
     */
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240',
            'document_name' => 'required|string|max:255',
            'type' => 'required|string', // Added for Bug 7
        ]);

        $record = OnboardingRecord::findOrFail($id);
        
        // Ensure User exists
        $user = $record->user ?? User::find($record->user_id);
        if (!$user) {
            // If user missing (legacy manual entry issue), try to create/find logic or error
            return redirect()->back()->withErrors(['msg' => 'No user account linked to this record.']);
        }

        $path = $request->file('document')->store('onboarding-documents');

        \App\Models\UserDocument::create([
            'user_id' => $user->id,
            'original_name' => $request->document_name,
            'path' => $path,
            'type' => $request->type,
            'is_verified' => true // Admin uploads are auto-verified
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Toggle Document Verification (Bug 8).
     */
    public function verifyDocument($id)
    {
        $doc = \App\Models\UserDocument::findOrFail($id);
        $doc->is_verified = !$doc->is_verified;
        $doc->save();

        return redirect()->back()->with('success', 'Document verification status updated.');
    }

    /**
     * Delete Document (Bug 17 Admin).
     */
    public function deleteDocument($id)
    {
        $doc = \App\Models\UserDocument::findOrFail($id);
        
        // Defer file deletion until after DB commit (Sanket - ONB-DATA-004)
        $filePath = $doc->path;
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($doc) {
            $doc->delete();
        });
        
        // Delete file AFTER successful DB commit
        if ($filePath && file_exists(storage_path('app/' . $filePath))) {
            unlink(storage_path('app/' . $filePath));
        }

        return redirect()->back()->with('success', 'Document deleted.');
    }

    /**
     * Provide portal access to a new hire.
     */
    public function providePortalAccess($id)
    {
        $record = OnboardingRecord::findOrFail($id);
        
        // BUG-ONB-008 Fix: Generate secure random password instead of predictable one
        $firstName = explode(' ', trim($record->full_name))[0];
        $plainPassword = $firstName . \Illuminate\Support\Str::random(8) . date('y');

        // Check if role exists (Self-healing for demo/dev env)
        if (!Role::where('name', 'new-hire')->exists()) {
            Role::create(['name' => 'new-hire', 'guard_name' => 'web']);
            Role::create(['name' => 'new-hire', 'guard_name' => 'portal']);
        }

        // Check if user already exists
        $user = User::where('email', $record->email)->first();

        if (!$user) {
            // Create New User
            $user = User::create([
                'name' => $record->full_name,
                'email' => $record->email,
                'password' => bcrypt($plainPassword),
                'status' => User::STATUS_ACTIVE,
            ]);
            $user->assignRole('new-hire');
            $record->update(['user_id' => $user->id]);
        } else {
            // Update existing user password
            $user->update([
                'password' => bcrypt($plainPassword),
            ]);
            $user->syncRoles(['new-hire']);
            
            if (!$record->user_id) {
                $record->update(['user_id' => $user->id]);
            }
        }

        // Send Email Invitation
        try {
            \Illuminate\Support\Facades\Mail::to($record->email)->send(
                new \Modules\Onboarding\Emails\PortalInvitation($record->full_name, $record->email, $plainPassword)
            );
            // BUG-ONB-007 Fix: Don't display password in success message
            // BUG-ONB-015 Fix: Log password securely instead
            \Log::info('Portal access created', [
                'email' => $record->email,
                'created_by' => auth()->id(),
                'password_sent' => true
            ]);
            return redirect()->back()->with('success', 'Invitation sent to ' . $record->email . '. Login credentials have been emailed.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Portal Invitation Error: ' . $e->getMessage(), [
                'email' => $record->email,
                'error' => $e->getMessage()
            ]);
            // BUG-ONB-007 Fix: Don't expose password even on error
            return redirect()->back()->with('error', 'Portal access created but email failed to send. Please contact IT support.');
        }
    }
}
