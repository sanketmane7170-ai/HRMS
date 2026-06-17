<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Novay\WordTemplate\WordTemplate;
use App\Models\User;
use Carbon\Carbon;
class ProbationController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'analytic-probation-ending-list');
    }

    public function index(Request $request)
    {
        // Only allow non-employees to access
        abort_if(auth()->user()->hasRole('employee'), 404);

        // AJAX Request - Return DataTable JSON
        if ($request->ajax()) {
            $data = getProbationEndQuery()
                ->with('department');
                
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('workDetail.probation_end_date', function ($user) {
                    return $user->workDetail->probation_end_date->format(config('project.date_format'));
                })
                // Dynamic action buttons based on letter status
                ->addColumn('action', function ($user) {
                    // PRIMARY ACTION: Confirm Probation Button (always shown)
                    $buttons = '<button type="button" class="btn btn-sm btn-outline-primary confirm-probation" data-id="' . $user->id . '" data-name="' . $user->name . '">
                                <i class="fas fa-check-circle me-1"></i> ' . (__trans('confirm') ?: 'Confirm') . '
                            </button>';
                    
                    // CONDITIONAL ACTIONS: Show download/email if letter exists
                    $letter = \App\Models\ProbationLetter::where('user_id', $user->id)->latest()->first();
                    if ($letter) {
                        // Download PDF button
                        $buttons .= ' <a href="' . route('backend.analytic.probation.download', $letter->id) . '" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-download"></i>
                                    </a>';
                        
                        // Send Email button - Red if already sent, Green if not
                        $emailSent = $letter->email_sent_at ? 'true' : 'false';
                        $emailBtnClass = $letter->email_sent_at ? 'btn-outline-danger' : 'btn-outline-success';
                        $buttons .= ' <a href="' . route('backend.analytic.probation.send.email', $letter->id) . '" 
                                        class="btn btn-sm ' . $emailBtnClass . ' send-email-btn" 
                                        data-email-sent="' . $emailSent . '"
                                        data-letter-id="' . $letter->id . '">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['action']) // Allow HTML in action column
                ->make(true);
        }

        // NORMAL REQUEST - Return View with Templates
        $templates = \App\Models\ProbationLetterTemplate::all();
        return view('analytic::probation.index', compact('templates'));
    }
    /**
     * Handle the DOCX upload and redirect to editor
     */
    public function processProbationUpload(Request $request)
    {
        \Log::info('Probation upload process started (Entering Editor)');
        
        try {
            // Author: Sanket - Fixed template selection validation
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'method' => 'required|in:upload,template',
                'docx_file' => 'nullable|required_if:method,upload|file|mimes:docx|max:5120',
                'template_id' => 'nullable|required_if:method,template|exists:probation_letter_templates,id',
                'confirmation_date' => 'nullable|string',
            ]);
            $confirmationDate = $request->confirmation_date ?? date('d-m-Y');
            $user = User::findOrFail($request->user_id);
            $htmlContent = '';
            $path = null;
            if ($request->method === 'upload') {
                $file = $request->file('docx_file');
                $path = $file->store('temp/probation', 'public');
                
                // Convert DOCX to HTML for editing
                $htmlContent = $this->convertToHtml(storage_path('app/public/' . $path), $user);
                
                // ADJUST VIEW PATH IF NEEDED
                return view('analytic::probation.edit-letter', [
                    'user' => $user,
                    'htmlContent' => $htmlContent,
                    'filePath' => $path,
                    'method' => 'upload',
                    'confirmationDate' => $confirmationDate
                ]);
            } else {
                $template = \App\Models\ProbationLetterTemplate::find($request->template_id);
                if (!$template) {
                    \Log::warning('Probation template not found: ' . $request->template_id);
                    // Provide a default fallback if template not found
                    $htmlContent = "<h1>Probation Confirmation Letter</h1><p>Dear {{NAME}},</p><p>We are pleased to confirm your employment...</p>";
                } else {
                    $htmlContent = $template->content;
                }
                
                $htmlContent = $this->replacePlaceholders($htmlContent, $user, $confirmationDate);
                
                // ADJUST VIEW PATH IF NEEDED
                return view('analytic::probation.edit-letter', [
                    'user' => $user,
                    'htmlContent' => $htmlContent,
                    'method' => 'template',
                    'template_id' => $template ? $template->id : null,
                    'confirmationDate' => $confirmationDate
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Probation upload failed: ' . $e->getMessage());
            return back()->with('error', 'Operation failed: ' . $e->getMessage());
        }
    }
    /**
     * Convert DOCX file to HTML
     */
    private function convertToHtml($filePath, $user = null)
    {
        try {
            $phpWord = IOFactory::load($filePath);
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            
            ob_start();
            $htmlWriter->save('php://output');
            $html = ob_get_clean();
            
            // Extract body content from HTML
            if (preg_match('/<body>(.*)<\/body>/is', $html, $matches)) {
                $html = $matches[1];
            }
            
            if ($user) {
                // $html = $this->replacePlaceholders($html, $user, $confirmationDate ?? null); // Fixed variable reference
                $html = $this->replacePlaceholders($html, $user);
            }
            
            return $html;
        } catch (\Exception $e) {
            \Log::error('DOCX to HTML conversion failed: ' . $e->getMessage());
            return '<p>Error converting document. You can still write your letter here.</p>';
        }
    }
    /**
     * Replace placeholders in content
     */
    private function replacePlaceholders($content, $user, $confirmationDate = null)
    {
        $displayDate = $confirmationDate ?: date('d-m-Y');
        
        // Fetch Reporting Manager(s)
        $managerNames = 'N/A';
        if ($user->workDetail && $user->workDetail->report_to_ids) {
            $managerIds = $user->workDetail->report_to_ids;
            if (is_string($managerIds)) {
                $managerIds = json_decode($managerIds, true);
            }
            if (is_array($managerIds) && !empty($managerIds)) {
                $managers = User::whereIn('id', $managerIds)->pluck('name')->toArray();
                $managerNames = implode(', ', $managers);
            }
        }

        // Fetch Place of Issue (from Passport or Visa)
        $placeOfIssue = 'N/A';
        $document = \App\Models\UserDocument::where('user_id', $user->id)
            ->whereIn('type', ['passport', 'visa'])
            ->whereNotNull('place_of_issue')
            ->latest()
            ->first();
        if ($document) {
            $placeOfIssue = $document->place_of_issue;
        }

        $placeholders = [
            '{{name}}' => $user->name,
            '{{NAME}}' => $user->name,
            '{{employee_name}}' => $user->name,
            '{{EMPLOYEE_NAME}}' => $user->name,
            '{{date}}' => $displayDate,
            '{{DATE}}' => $displayDate,
            '{{probation_end_date}}' => $user->workDetail?->probation_end_date?->format('d-m-Y') ?? '',
            '{{PROBATION_END_DATE}}' => $user->workDetail?->probation_end_date?->format('d-m-Y') ?? '',
            '{{joining_date}}' => $user->workDetail?->joining_date?->format('d-m-Y') ?? '',
            '{{JOINING_DATE}}' => $user->workDetail?->joining_date?->format('d-m-Y') ?? '',
            '{{designation}}' => $user->designation?->name ?? '',
            '{{DESIGNATION}}' => $user->designation?->name ?? '',
            '{{department}}' => $user->department?->name ?? '',
            '{{DEPARTMENT}}' => $user->department?->name ?? '',
            '{{employee_id}}' => $user->employee_id ?? '',
            '{{EMPLOYEE_ID}}' => $user->employee_id ?? '',
            '{{company_name}}' => (getSetting('site_title') ?? 'WorkPilot'),
            '{{COMPANY_NAME}}' => (getSetting('site_title') ?? 'WorkPilot'),
            '{{reporting_manager}}' => $managerNames,
            '{{REPORTING_MANAGER}}' => $managerNames,
            '{{manager}}' => $managerNames,
            '{{MANAGER}}' => $managerNames,
            '{{manager_name}}' => $managerNames,
            '{{MANAGER_NAME}}' => $managerNames,
            '{{reported_to}}' => $managerNames,
            '{{REPORTED_TO}}' => $managerNames,
            '{{place_of_issue}}' => $placeOfIssue,
            '{{PLACE_OF_ISSUE}}' => $placeOfIssue,
            '{{place_issue}}' => $placeOfIssue,
            '{{PLACE_ISSUE}}' => $placeOfIssue,
            '{{issue_place}}' => $placeOfIssue,
            '{{ISSUE_PLACE}}' => $placeOfIssue,
            '{{confirmation_status}}' => 'Confirmed',
            '{{CONFIRMATION_STATUS}}' => 'Confirmed',
        ];

        // Author signature name: Sanket
        return strtr($content, $placeholders);
    }
    /**
     * Save the edited letter and confirm probation
     */
    public function save(Request $request)
    {
        \Log::info('Probation save action triggered', $request->except(['content']));
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'required',
            'action' => 'required|in:save,pdf,download,email'
        ]);
        $user = User::findOrFail($request->user_id);
        $content = $request->content;
        $filename = 'probation_confirmation_' . str_replace(' ', '_', strtolower($user->name)) . '_' . time();
        $path = 'probation/confirmations/' . $filename . '.docx';
        
        try {
            // 1. Generate DOCX (for historical/editing purposes)
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection([
                'paperSize' => 'A4',
                'marginLeft' => 1133.858,
                'marginRight' => 1133.858,
                'marginTop' => 1133.858,
                'marginBottom' => 1133.858,
            ]);
            
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $content, false, false);
            
            if (!Storage::disk('public')->exists('probation/confirmations')) {
                Storage::disk('public')->makeDirectory('probation/confirmations');
            }
            
            $fullPath = storage_path('app/public/' . $path);
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($fullPath);
            // 2. Generate PDF (for sharing/security)
            $pdfFilename = 'probation_confirmation_' . str_replace(' ', '_', strtolower($user->name)) . '_' . time() . '.pdf';
            $pdfPath = 'probation/confirmations/' . $pdfFilename;
            
            try {
                \Log::info('Attempting PDF generation for letter save...');
                
                // Author: Sanket
                // Get company logo for PDF embedding
                $logoHtml = '';
                $logoPath = getSetting('site_logo');
                if ($logoPath) {
                    try {
                        // Convert storage URL to absolute path
                        $path = str_replace('/storage/', '', $logoPath);
                        $fullLogoPath = storage_path('app/public/' . $path);
                        
                        if (file_exists($fullLogoPath)) {
                            $logoExtension = pathinfo($fullLogoPath, PATHINFO_EXTENSION);
                            $logoBase64 = base64_encode(file_get_contents($fullLogoPath));
                            $logoHtml = '<div style="text-align: center; margin-bottom: 20px;">
                                            <img src="data:image/' . $logoExtension . ';base64,' . $logoBase64 . '" 
                                                 alt="Company Logo" 
                                                 style="max-height: 80px; max-width: 200px;">
                                         </div>';
                        }
                    } catch (\Exception $logoError) {
                        \Log::warning('Failed to embed logo in confirmation PDF: ' . $logoError->getMessage());
                    }
                }
                
                // Wrap content in a robust HTML structure for DomPDF with logo
                $pdfHtml = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
                            <style>body { font-family: sans-serif; line-height: 1.5; font-size: 14px; }</style>
                            </head><body>" . $logoHtml . $content . "</body></html>";
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($pdfHtml);
                $pdf->setPaper('a4', 'portrait');
                $pdfFullPath = storage_path('app/public/' . $pdfPath);
                
                // Use output() then file_put_contents for maximum control
                Storage::disk('public')->put($pdfPath, $pdf->output());
                
                \Log::info('PDF successfully generated and saved to storage: ' . $pdfPath);
            } catch (\Exception $pdfError) {
                \Log::error('PDF generation error during save: ' . $pdfError->getMessage(), [
                    'exception' => $pdfError,
                    'content_length' => strlen($content)
                ]);
                $pdfPath = null; // Mark as failed
            }
            // Record in database
            $letter = \App\Models\ProbationLetter::create([
                'user_id' => $user->id,
                'content' => $content,
                'file_path' => $path,
                'pdf_path' => $pdfPath,
                'status' => 'confirmed',
            ]);
            if ($request->action === 'pdf' || $request->action === 'download') {
                $pathToDownload = $pdfPath ? storage_path('app/public/' . $pdfPath) : $fullPath;
                $downloadName = ($pdfPath ? $pdfFilename : basename($fullPath));
                return response()->download($pathToDownload, $downloadName)->deleteFileAfterSend(false);
            }
            if ($request->action === 'email') {
                $pathToAttach = $pdfPath ?: $path;
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\ProbationConfirmationMail($user, $pathToAttach));
            }
            // ADJUST REDIRECT ROUTE AS NEEDED
            return redirect()->route('backend.analytic.probation.upcoming.list')
                ->with('success', 'Probation confirmed and action ' . ($request->action == 'pdf' ? 'PDF Download' : $request->action) . ' completed successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Saving probation letter failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to perform the action: ' . $e->getMessage());
        }
    }
    /**
     * Save current content as a template
     */
    public function templateStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required',
        ]);
        try {
            \App\Models\ProbationLetterTemplate::create([
                'name' => $request->name,
                'content' => $request->content,
                'created_by' => auth()->id(),
            ]);
            return response()->json(['success' => true, 'message' => 'Template saved successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to save template: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Download an existing letter
     */
    public function download(\App\Models\ProbationLetter $letter)
    {
        // Prioritize PDF download
        if ($letter->pdf_path) {
            $fullPath = storage_path('app/public/' . $letter->pdf_path);
            if (file_exists($fullPath)) {
                return response()->download($fullPath);
            }
        }
        
        // If PDF is missing but we have content, try to regenerate it on the fly
        if ($letter->content) {
            try {
                $pdfHtml = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/></head><body>" . $letter->content . "</body></html>";
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($pdfHtml);
                $pdf->setPaper('a4', 'portrait');
                $pdfFilename = basename($letter->file_path, '.docx') . '.pdf';
                $pdfPath = 'probation/confirmations/' . $pdfFilename;
                
                Storage::disk('public')->put($pdfPath, $pdf->output());
                
                $letter->update(['pdf_path' => $pdfPath]);
                return response()->download(storage_path('app/public/' . $pdfPath));
            } catch (\Exception $e) {
                \Log::error('On-the-fly PDF generation failed for download: ' . $e->getMessage());
            }
        }
        
        $fullPath = storage_path('app/public/' . $letter->file_path);
        if (file_exists($fullPath)) {
            return response()->download($fullPath);
        }
        return back()->with('error', 'File not found and could not regenerate PDF.');
    }
    /**
     * Send email for an existing letter
     */
    public function sendEmail(\App\Models\ProbationLetter $letter)
    {
        $user = $letter->user;
        try {
            // Prioritize PDF attachment
            $pathToAttach = $letter->pdf_path;
            
            // If PDF is missing but we have content, regenerate it
            if (!$pathToAttach && $letter->content) {
                \Log::info('PDF missing, attempting regeneration from content for letter ID: ' . $letter->id);
                try {
                    $pdfHtml = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/></head><body>" . $letter->content . "</body></html>";
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($pdfHtml);
                    $pdf->setPaper('a4', 'portrait');
                    $pdfFilename = basename($letter->file_path, '.docx') . '.pdf';
                    $pdfPath = 'probation/confirmations/' . $pdfFilename;
                    
                    Storage::disk('public')->put($pdfPath, $pdf->output());
                    
                    $letter->update(['pdf_path' => $pdfPath]);
                    $pathToAttach = $pdfPath;
                    \Log::info('PDF successfully regenerated: ' . $pdfPath);
                } catch (\Exception $e) {
                    \Log::error('On-the-fly PDF generation failed for email: ' . $e->getMessage(), [
                        'exception' => $e,
                        'letter_id' => $letter->id
                    ]);
                    $pathToAttach = $letter->file_path; // Fallback to docx if regeneration fails
                }
            } elseif (!$pathToAttach) {
                \Log::warning('PDF missing and no content available to regenerate for letter ID: ' . $letter->id);
                $pathToAttach = $letter->file_path; // Final fallback for very old records
            }
            
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\ProbationConfirmationMail($user, $pathToAttach));
            
            // Update email_sent_at timestamp
            $letter->update(['email_sent_at' => now()]);
            
            $msg = (str_ends_with($pathToAttach, '.pdf') ? 'Email sent successfully with PDF attachment.' : 'Email sent successfully with DOCX attachment (PDF conversion failed).');
            return back()->with('success', $msg . ' Sent to ' . $user->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
