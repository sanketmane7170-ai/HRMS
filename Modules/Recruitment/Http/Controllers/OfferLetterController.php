<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\Offer;
use App\Notifications\Recruitment\OfferCreatedNotification;
use App\Notifications\Recruitment\OfferSentNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Modules\Recruitment\Entities\OfferLetterTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Blade;

class OfferLetterController extends Controller
{
    protected $stageService;

    public function __construct()
    {
        view()->share('activeLink', 'recruitment-offers');
        
        // Try to inject ApplicationStageService if it exists
        try {
            $this->stageService = app(\App\Services\Recruitment\ApplicationStageService::class);
        } catch (\Exception $e) {
            $this->stageService = null;
        }
    }

    /**
     * Selection page for offer letter generation method
     */
    public function selection(Request $request)
    {
        $application_id = $request->application_id;
        $candidates = Application::where('stage', '!=', 'hired')
            ->where('stage', '!=', 'rejected')
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'name' => $app->candidate_name,
                    'job_title' => $app->job->title ?? 'N/A',
                    'department' => $app->job->department ?? 'N/A',
                    'location' => $app->job->location ?? 'N/A',
                    'min_salary' => $app->job->salary_min ?? 0,
                    'job_id' => $app->job_id,
                    'email' => $app->candidate_email
                ];
            });

        return view('recruitment::offer-letters.selection', compact('candidates', 'application_id'));
    }

    /**
     * Handle DOCX upload for offer letter
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:recruitment_applications,id',
            'docx_file' => 'required|file|mimes:docx|max:5120',
        ]);

        try {
            $application = Application::findOrFail($request->application_id);
            $file = $request->file('docx_file');
            $path = $file->store('temp/offers', 'public');
            
            // Convert DOCX to HTML for editing
            $htmlContent = $this->convertToHtml(storage_path('app/public/' . $path), $application);
            
            $templates = OfferLetterTemplate::all();

            return view('recruitment::offer-letters.edit-custom', [
                'application' => $application,
                'htmlContent' => $htmlContent,
                'method' => 'upload',
                'templates' => $templates,
                'application_id' => $application->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Offer upload failed: ' . $e->getMessage());
            return back()->with('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Edit custom offer letter from template
     */
    public function editFromTemplate(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:recruitment_applications,id',
            'template_id' => 'required|exists:recruitment_offer_letter_templates,id',
        ]);

        try {
            $application = Application::findOrFail($request->application_id);
            $template = OfferLetterTemplate::findOrFail($request->template_id);
            
            $htmlContent = $this->replacePlaceholders($template->content, $application);
            
            $templates = OfferLetterTemplate::all();

            return view('recruitment::offer-letters.edit-custom', [
                'application' => $application,
                'htmlContent' => $htmlContent,
                'method' => 'template',
                'template_id' => $template->id,
                'templates' => $templates,
                'application_id' => $application->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Offer template load failed: ' . $e->getMessage());
            return back()->with('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Store new template from editor
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        OfferLetterTemplate::create([
            'name' => $request->name,
            'content' => $request->content,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully!'
        ]);
    }

    /**
     * Helper: Convert DOCX to HTML
     */
    private function convertToHtml($filePath, $application = null)
    {
        try {
            $phpWord = IOFactory::load($filePath);
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            
            ob_start();
            $htmlWriter->save('php://output');
            $html = ob_get_clean();
            
            if (preg_match('/<body>(.*)<\/body>/is', $html, $matches)) {
                $html = $matches[1];
            }
            
            if ($application) {
                $html = $this->replacePlaceholders($html, $application);
            }
            
            return $html;
        } catch (\Exception $e) {
            \Log::error('DOCX Conversion error: ' . $e->getMessage());
            return "Error converting document: " . $e->getMessage();
        }
    }

    /**
     * Helper: Replace placeholders
     * Author: Sanket - extended to cover bracket-style and all data fields
     */
    private function replacePlaceholders($html, $application)
    {
        $job = $application->job;
        $offer = Offer::where('application_id', $application->id)->latest()->first();

        // Resolve department name regardless of whether it's an object or string
        $departmentName = 'N/A';
        if ($job && $job->department) {
            $departmentName = is_object($job->department) ? ($job->department->name ?? 'N/A') : $job->department;
        }

        $candidateName  = $application->candidate_name ?? ($application->user->name ?? 'Candidate');
        $jobTitle       = $job->title ?? 'N/A';
        $location       = $job->location ?? 'Office Location';
        $salary         = $offer ? ($offer->salary ?? '_________________') : '_________________';
        $currency       = $offer ? ($offer->currency ?? 'USD') : 'USD';
        $startDate      = $offer && $offer->start_date
                            ? \Carbon\Carbon::parse($offer->start_date)->format('d F Y')
                            : \Carbon\Carbon::now()->addWeeks(2)->format('d F Y');
        $companyName    = config('app.name', 'MOM Digital');
        $todayDate      = date('d F Y');
        $reportingTo    = 'Department Manager';

        // Salary formatted
        $salaryFormatted = $salary !== '_________________' ? $currency . ' ' . number_format($salary) : '_________________';

        // All replacement maps – covering [Bracket], {{Curly}}, and common variants
        $placeholders = [
            // ---- Bracket style: [Field Name] ----
            '[Candidate Name]'             => $candidateName,
            '[Candidate Address]'          => $location,
            '[Job Title]'                  => $jobTitle,
            '[Department]'                 => $departmentName,
            '[Department Name]'            => $departmentName,
            '[Office Location / Remote / Hybrid]' => $location,
            '[Office Location]'            => $location,
            '[Location]'                   => $location,
            '[Reporting Manager / Department Head]' => $reportingTo,
            '[Reporting Manager]'          => $reportingTo,
            '[Joining Date]'               => $startDate,
            '[Start Date]'                 => $startDate,
            '[DD/MM/YYYY]'                 => $todayDate,
            '[Date]'                       => $todayDate,
            '[Company Name]'               => $companyName,
            '[Salary]'                     => $salaryFormatted,
            '[Salary Amount]'              => $salaryFormatted,
            '[Annual Salary]'              => $salaryFormatted,
            '[HR Name]'                    => auth()->user()->name ?? 'HR Manager',
            '[Issuer Name]'                => auth()->user()->name ?? 'HR Manager',
            // ---- Curly style: {{Field}} ----
            '{{NAME}}'                     => $candidateName,
            '{{CANDIDATE_NAME}}'           => $candidateName,
            '{{JOB_TITLE}}'                => $jobTitle,
            '{{DEPARTMENT}}'               => $departmentName,
            '{{DATE}}'                     => $todayDate,
            '{{COMPANY_NAME}}'             => $companyName,
            '{{LOCATION}}'                 => $location,
            '{{START_DATE}}'               => $startDate,
            '{{SALARY}}'                   => $salaryFormatted,
            '{{REPORTING_TO}}'             => $reportingTo,
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $html);
    }

    /**
     * Show the offer letter generator form
     */
    public function create(Request $request)
    {
        // Check authentication and permissions
        if (!auth()->check()) {
            abort(401, 'Authentication required');
        }
        
        $user = auth()->user();
        $hasAccess = $user->hasRole(['admin', 'hr']) || 
                    $user->can('Create Offers') || 
                    $user->can('Manage Offers');
                    
        if (!$hasAccess) {
            abort(403, 'Insufficient permissions to create offer letters');
        }

        // Get application data if application_id is provided
        $application = null;
        $job = null;
        $existingOffer = null;
        
        if ($request->has('offer_id')) {
            $existingOffer = Offer::with(['application.job', 'application.user'])->find($request->offer_id);
            if ($existingOffer) {
                $application = $existingOffer->application;
                $job = $application ? $application->job : null;
            }
        } elseif ($request->has('application_id')) {
            $application = Application::with(['job', 'user'])->find($request->application_id);
            $job = $application ? $application->job : null;
        }

        // Get all jobs for dropdown
        $jobs = Job::where('status', 'active')->orderBy('title')->get();
        
        // Get all candidates with applications for dropdown
        $candidates = Application::with(['job.department', 'user'])
            ->whereNotNull('job_id')
            ->whereNotIn('stage', ['rejected', 'hired']) // Filter out rejected and hired applications
            ->get()
            ->map(function($app) {
                // Author: Sanket
                // Simplified department handling using relationship
                $department = 'N/A';
                if ($app->job && $app->job->department) {
                    $department = $app->job->department->name ?? 'N/A';
                }
                
                // Author: Sanket
                // Robust name resolution
                $candidateName = 'Unknown Candidate';
                if ($app->user && !empty($app->user->name)) {
                    $candidateName = $app->user->name;
                } elseif (!empty($app->candidate_name)) {
                    $candidateName = $app->candidate_name;
                } elseif (!empty($app->candidate_link) && !empty($app->candidate_link->name)) {
                     // Added check for linked candidate profile
                    $candidateName = $app->candidate_link->name;
                } elseif (!empty($app->candidate_email)) {
                    $candidateName = $app->candidate_email;
                }
                
                return [
                    'id' => $app->id,
                    'name' => $candidateName,
                    'email' => $app->user ? $app->user->email : ($app->candidate_email ?? ''),
                    'job_title' => $app->job ? $app->job->title : 'N/A',
                    'department' => $department,
                    'job_id' => $app->job_id,
                    'min_salary' => $app->job ? ($app->job->min_salary ?? 0) : 0,
                    'salary_max' => $app->job ? ($app->job->salary_max ?? 0) : 0,
                    'location' => $app->job ? ($app->job->location ?? 'Office Location') : 'Office Location',
                    'stage' => $app->stage ?? 'N/A',
                ];
            })
            ->filter(function($candidate) {
                // Author: Sanket
                // Improved filter: Only exclude if truly missing essential data
                // Allow external candidates with email as name
                $hasValidName = !empty($candidate['name']) && 
                               trim($candidate['name']) !== '' &&
                               $candidate['name'] !== 'Unknown Candidate';
                
                $hasValidJob = !empty($candidate['job_title']) && 
                              $candidate['job_title'] !== 'N/A';
                
                return $hasValidName && $hasValidJob;
            })
            ->values();
        
        // Prepare default data - use existing offer data if editing
        if ($existingOffer) {
            // If the offer has custom content, redirect to the custom editor
            if ($existingOffer->content) {
                $templates = OfferLetterTemplate::all();
                // Author: Sanket - Run placeholder replacement even on existing content
                // so any un-replaced fields are filled in on reopen
                $htmlContent = $this->replacePlaceholders($existingOffer->content, $application);
                return view('recruitment::offer-letters.edit-custom', [
                    'application' => $application,
                    'htmlContent' => $htmlContent,
                    'method' => 'edit',
                    'offer_id' => $existingOffer->id,
                    'templates' => $templates,
                    'application_id' => $application->id
                ]);
            }
            
            $defaultData = [
                'company_name' => config('app.name', 'MOM Digital'),
                'candidate_name' => $application ? ($application->user->name ?? $application->candidate_name) : '',
                'job_title' => $job ? $job->title : '',
                'department' => $job ? $job->department : '',
                'location' => $job ? $job->location : 'Office Location',
                'start_date' => $existingOffer->start_date ? $existingOffer->start_date->format('Y-m-d') : ($existingOffer->joining_date ? $existingOffer->joining_date->format('Y-m-d') : Carbon::now()->addWeeks(2)->format('Y-m-d')),
                'salary_amount' => $existingOffer->salary ?? '',
                'currency' => $existingOffer->currency ?? 'USD',
                'payment_period' => $existingOffer->payment_period ?? 'Year',
                'pay_frequency' => $existingOffer->pay_frequency ?? 'Monthly',
                'work_schedule' => 'Full-time, Monday to Friday, 9:00 AM - 5:00 PM',
                'reporting_to' => 'Department Manager',
                'benefits' => $existingOffer->benefits ?? "Health Insurance\nPaid Time Off\n401(k) Retirement Plan\nProfessional Development\nFlexible Work Hours",
                'contingencies' => $existingOffer->terms_conditions ?? 'This offer is contingent upon successful completion of background check and reference verification.',
                'expiration_date' => $existingOffer->response_deadline ? $existingOffer->response_deadline->format('Y-m-d') : Carbon::now()->addDays(7)->format('Y-m-d'),
                'sender_name' => auth()->user()->name,
                'sender_title' => 'HR Manager'
            ];
        } else {
            $defaultData = [
                'company_name' => config('app.name', 'MOM Digital'),
                'candidate_name' => $application ? ($application->user->name ?? $application->candidate_name) : '',
                'job_title' => $job ? $job->title : '',
                'department' => $job ? $job->department : '',
                'location' => $job ? $job->location : 'Office Location',
                'start_date' => Carbon::now()->addWeeks(2)->format('Y-m-d'),
                'salary_amount' => $job ? $job->min_salary : '',
                'payment_period' => 'Year',
                'pay_frequency' => 'Monthly',
                'work_schedule' => 'Full-time, Monday to Friday, 9:00 AM - 5:00 PM',
                'reporting_to' => 'Department Manager',
                'benefits' => "Health Insurance\nPaid Time Off\n401(k) Retirement Plan\nProfessional Development\nFlexible Work Hours",
                'contingencies' => 'This offer is contingent upon successful completion of background check and reference verification.',
                'expiration_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'sender_name' => auth()->user()->name,
                'sender_title' => 'HR Manager'
            ];
        }

        return view('recruitment::offer-letters.create', compact('application', 'job', 'jobs', 'candidates', 'defaultData', 'existingOffer'));
    }

    /**
     * Generate and download PDF offer letter
     */
    public function generatePdf(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'candidate_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'salary_amount' => 'required|numeric|min:0',
            'payment_period' => 'required|in:Year,Month,Hour',
            'pay_frequency' => 'required|in:Weekly,Bi-weekly,Monthly,Annually',
            'work_schedule' => 'required|string',
            'reporting_to' => 'required|string|max:255',
            'benefits' => 'nullable|string',
            'contingencies' => 'nullable|string',
            'expiration_date' => 'required|date|after_or_equal:today',
            'sender_name' => 'required|string|max:255',
            'sender_title' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'currency' => 'nullable|string|in:USD,EUR,GBP,INR,CAD,AUD,JPY,CHF,AED'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $logoName = 'company_logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
                $logoPath = $logoFile->storeAs('public/logos', $logoName); // Stores in storage/app/public/logos
                // For PDF view, we pass the relative path, but view will convert to absolute system path
                // $logoPath value here is 'public/logos/...'
            }

            // Currency Map
            $currencySymbols = [
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                'INR' => '₹',
                'CAD' => 'C$',
                'AUD' => 'A$',
                'JPY' => '¥',
                'CHF' => 'CHF',
                // Author: Sanket - Mapped AED symbol with a space for PDF and preview clarity
                'AED' => 'AED '
            ];
            $currencyCode = $request->currency ?? 'USD';
            $currencySymbol = $currencySymbols[$currencyCode] ?? '$';

            // Prepare logo base64
            $logoBase64 = null;
            $logoMime = null;
            if ($logoPath) {
                try {
                    $path = str_replace('/storage/', '', $logoPath);
                    // Handle full URLs if present
                    if (strpos($path, 'http') === 0) {
                        $parsed = parse_url($path);
                        $path = $parsed['path'] ?? '';
                        $path = str_replace('/storage/', '', $path);
                    }
                    
                    $fullLogoPath = storage_path('app/public/' . $path);
                    
                    if (file_exists($fullLogoPath)) {
                        $logoMime = pathinfo($fullLogoPath, PATHINFO_EXTENSION);
                        $logoBase64 = base64_encode(file_get_contents($fullLogoPath));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Logo processing failed: ' . $e->getMessage());
                }
            }

            // Prepare data for PDF
            $data = [
                'company_name' => $request->company_name,
                'candidate_name' => $request->candidate_name,
                'job_title' => $request->job_title,
                'department' => $request->department,
                'location' => $request->location,
                'start_date' => Carbon::parse($request->start_date)->format('F j, Y'),
                'salary_amount' => number_format($request->salary_amount, 2),
                'payment_period' => $request->payment_period,
                'pay_frequency' => $request->pay_frequency,
                'work_schedule' => $request->work_schedule,
                'reporting_to' => $request->reporting_to,
                'benefits' => $request->benefits ? explode("\n", $request->benefits) : [],
                'contingencies' => $request->contingencies,
                'expiration_date' => Carbon::parse($request->expiration_date)->format('F j, Y'),
                'sender_name' => $request->sender_name,
                'sender_title' => $request->sender_title,
                'logo_base64' => $logoBase64,
                'logo_mime' => $logoMime,
                'generated_date' => Carbon::now()->format('F j, Y'),
                'currency_symbol' => $currencySymbol
            ];

            // Generate PDF
            // Author: Sanket - Added error logging for debugging blank PDF issues
            try {
                $pdf = PDF::loadView('recruitment::offer-letters.pdf', $data);
                $pdf->setPaper('A4', 'portrait');
                
                $filename = 'offer_letter_' . str_replace(' ', '_', strtolower($request->candidate_name)) . '_' . date('Y_m_d') . '.pdf';
                
                return $pdf->download($filename);
            } catch (\Exception $pdfError) {
                \Log::error('PDF Generation Error', [
                    'error' => $pdfError->getMessage(),
                    'data_keys' => array_keys($data),
                    'candidate_name' => $data['candidate_name'] ?? 'N/A',
                    'logo_present' => isset($data['logo_base64']) ? 'Yes' : 'No'
                ]);
                throw $pdfError;
            }

        } catch (\Exception $e) {
            \Log::error('Offer Letter Generation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save offer letter data to database and create official offer record
     */
    public function store(Request $request)
    {
        \Log::info('📥 Offer store request received', $request->all());

        // Security Check (Sanket - REC-SEC-012)
        if (!auth()->user()->can('Create Offers') && !auth()->user()->hasRole('admin')) {
             abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'application_id' => 'nullable|exists:recruitment_applications,id',
            'job_id' => 'nullable|exists:recruitment_jobs,id',
            'selected_candidate_id' => 'nullable|exists:recruitment_applications,id',
            'candidate_name' => 'required|string|max:255',
            'candidate_email' => 'nullable|email|max:255',
            'candidate_phone' => 'nullable|string|max:20',
            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'currency' => 'required|string|in:USD,EUR,GBP,INR,CAD,AUD,JPY,CHF,AED',
            'salary_amount' => 'required|numeric|min:0',
            'payment_period' => 'required|string|in:Year,Month,Hour',
            'pay_frequency' => 'required|string|in:Weekly,Bi-weekly,Monthly,Annually',
            'start_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:today',
            'benefits' => 'nullable|string',
            'contingencies' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::warning('❌ Offer store validation failed', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if we're updating an existing offer
            if ($request->offer_id) {
                $existingOffer = Offer::find($request->offer_id);
                if ($existingOffer) {
                    // Update existing offer
                    $existingOffer->update([
                        'salary' => $request->salary_amount,
                        'currency' => $request->currency,
                        'payment_period' => $request->payment_period,
                        'pay_frequency' => $request->pay_frequency,
                        'benefits' => $request->benefits,
                        'start_date' => $request->start_date,
                        'response_deadline' => $request->expiration_date,
                        'terms_conditions' => $request->contingencies,
                        'content' => $request->content,
                        'notes' => "Updated via Offer Letter Generator\nDepartment: {$request->department}\nJob Title: {$request->job_title}\nLocation: {$request->location}",
                    ]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Offer updated successfully! You can now view it in Offer Management.',
                        'offer_id' => $existingOffer->id,
                        'redirect' => route('recruitment.offers.index')
                    ]);
                }
            }

            // Create or get application
            $application = null;
            if ($request->application_id) {
                $application = Application::find($request->application_id);
            } elseif ($request->selected_candidate_id) {
                // Use the selected candidate's existing application
                $application = Application::find($request->selected_candidate_id);
                /** @var Application|null $application */
            } else {
                // For new applications, we need a valid job_id
                $jobId = $request->job_id;
                
                // If no job_id provided but we have selected_candidate_id, get job from that application
                if (!$jobId && $request->selected_candidate_id) {
                    $candidateApp = Application::find($request->selected_candidate_id);
                    if ($candidateApp) {
                        $jobId = $candidateApp->job_id;
                    }
                }
                
                // If still no job_id, try to find a suitable job based on job_title
                if (!$jobId && $request->job_title) {
                    $job = Job::where('title', 'like', '%' . $request->job_title . '%')
                             ->where('status', 'active')
                             ->first();
                    if ($job) {
                        $jobId = $job->id;
                    }
                }
                
                // If still no job_id, create a default job record
                if (!$jobId) {
                    $job = Job::create([
                        'title' => $request->job_title,
                        'department' => $request->department,
                        'status' => 'active',
                        'description' => 'Auto-created for offer letter',
                        'requirements' => 'See offer letter details',
                        'created_by' => auth()->id(),
                    ]);
                    $jobId = $job->id;
                }
                
                // Create a new application record for the offer
                $application = Application::create([
                    'job_id' => $jobId,
                    'candidate_name' => $request->candidate_name,
                    'candidate_email' => $request->candidate_email ?? '',
                    'candidate_phone' => $request->candidate_phone ?? '',
                    'stage' => 'offer_extended',
                    'applied_on' => now(),
                ]);
            }

            // Create the offer record
            $offerLetterUrl = null;
            if ($request->content) {
                try {
                    $pdf = PDF::loadHTML($request->content);
                    $pdf->setPaper('A4', 'portrait');
                    $pdfFilename = 'offer_letter_' . time() . '.pdf';
                    $pdfPath = 'public/offers/' . $pdfFilename;
                    
                    if (!Storage::exists('public/offers')) {
                        Storage::makeDirectory('public/offers');
                    }
                    
                    Storage::put($pdfPath, $pdf->output());
                    $offerLetterUrl = $pdfPath;
                } catch (\Exception $e) {
                    \Log::error('Custom PDF generation failed: ' . $e->getMessage());
                }
            }

            $offerData = [
                'application_id' => $application->id,
                'salary' => $request->salary_amount,
                'currency' => $request->currency,
                'payment_period' => $request->payment_period,
                'pay_frequency' => $request->pay_frequency,
                'benefits' => $request->benefits,
                'start_date' => $request->start_date,
                'offer_date' => now(),
                'response_deadline' => $request->expiration_date,
                'responded_at' => null, // Reset response if updated
                'status' => 'pending', // Reset to pending if updated via generator
                'terms_conditions' => $request->contingencies,
                'notes' => "Generated via Offer Letter Generator\nDepartment: {$request->department}\nJob Title: {$request->job_title}\nLocation: {$request->location}",
                'created_by' => auth()->id(),
                'position' => $request->job_title,
                'department' => $request->department,
                'content' => $request->content,
                'offer_letter_url' => $offerLetterUrl,
            ];
            
            // Add other fields if they exist in schema and request
            if ($request->currency) $offerData['salary_currency'] = $request->currency;
            if ($request->payment_period) $offerData['salary_period'] = $request->payment_period;
            if ($request->pay_frequency) $offerData['salary_type'] = $request->pay_frequency;

            $offer = Offer::create($offerData);

            // Notify candidate about the new offer
            if ($offer->application->user) {
                $offer->application->user->notify(new OfferCreatedNotification($offer));
            }

            // Update application stage if service is available
            if ($application && $this->stageService) {
                try {
                    $this->stageService->progressToStage($application, 'offer', 'Offer letter generated and extended to candidate.');
                } catch (\Throwable $e) {
                    // Continue if stage service fails - log but don't stop offer creation
                    \Log::warning('Failed to update application stage: ' . $e->getMessage());
                }
            } else {
                // Fallback: Update application stage directly
                try {
                    $application->update([
                        'stage' => 'offer_extended',
                        'status' => 'offer_extended'
                    ]);
                } catch (\Throwable $e) {
                    // Log but don't fail offer creation
                    \Log::warning('Failed to update application stage directly: ' . $e->getMessage());
                }
            }

            DB::commit();
            \Log::info('✅ Offer created successfully', ['offer_id' => $offer->id]);

            return response()->json([
                'success' => true,
                'message' => 'Offer created successfully! You can now view it in Offer Management.',
                'offer_id' => $offer->id,
                'redirect' => route('recruitment.offers.index')
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            
            // Log the error for debugging
            \Log::error('❌ Offer creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the offer: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get candidate details for auto-population
     */
    public function getCandidateDetails(Request $request)
    {
        $applicationId = $request->get('application_id');
        
        if (!$applicationId) {
            return response()->json(['success' => false, 'message' => 'Application ID required']);
        }
        
        $application = Application::with(['job', 'user'])->find($applicationId);
        
        if (!$application) {
            return response()->json(['success' => false, 'message' => 'Application not found']);
        }
        
        $candidateData = [
            'candidate_name' => $application->user ? $application->user->name : $application->candidate_name,
            'candidate_email' => $application->user ? $application->user->email : $application->candidate_email,
            'job_title' => $application->job ? $application->job->title : '',
            'department' => $application->job ? $application->job->department : '',
            'location' => $application->job ? $application->job->location : 'Office Location',
            'salary_amount' => $application->job ? $application->job->min_salary : '',
            'job_id' => $application->job_id,
            'application_id' => $application->id
        ];
        
        return response()->json(['success' => true, 'data' => $candidateData]);
    }

    /**
     * Preview offer letter (AJAX endpoint)
     */
    public function preview(Request $request)
    {
        $data = $request->all();
        
        // Format data for preview
        $previewData = [
            'company_name' => $data['company_name'] ?? 'Company Name',
            'candidate_name' => $data['candidate_name'] ?? 'Candidate Name',
            'job_title' => $data['job_title'] ?? 'Job Title',
            'department' => $data['department'] ?? 'Department',
            'location' => $data['location'] ?? 'Location',
            'start_date' => isset($data['start_date']) ? Carbon::parse($data['start_date'])->format('F j, Y') : 'Start Date',
            'salary_amount' => isset($data['salary_amount']) ? number_format($data['salary_amount'], 2) : '0.00',
            'payment_period' => $data['payment_period'] ?? 'Year',
            'pay_frequency' => $data['pay_frequency'] ?? 'Monthly',
            'work_schedule' => $data['work_schedule'] ?? 'Work Schedule',
            'reporting_to' => $data['reporting_to'] ?? 'Manager Name',
            'benefits' => isset($data['benefits']) ? explode("\n", $data['benefits']) : [],
            'contingencies' => $data['contingencies'] ?? '',
            'expiration_date' => isset($data['expiration_date']) ? Carbon::parse($data['expiration_date'])->format('F j, Y') : 'Expiration Date',
            'sender_name' => $data['sender_name'] ?? 'Sender Name',
            'sender_title' => $data['sender_title'] ?? 'Sender Title',
            'generated_date' => Carbon::now()->format('F j, Y')
        ];

        return response()->json($previewData);
    }
}