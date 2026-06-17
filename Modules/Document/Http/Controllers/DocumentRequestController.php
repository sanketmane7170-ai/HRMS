<?php

namespace Modules\Document\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Modules\Document\Entities\DocumentRequest;
use Modules\Document\Enums\DocumentRequestStatus;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Models\User;
use App\Services\FirebaseService;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserSalaryAllowance;

class DocumentRequestController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'document-requests');
        $this->fcmService = $fcmService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        canPerform('Manage Document Request');
        if ($request->ajax()) {
            $data = DocumentRequest::with(['type', 'user'])->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($data) {
                    return  $data->status->getHtml();
                })
                ->editColumn('created_at', function ($data) {
                    return  formatDate($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    $btn = '';
                    $btn = createActionButton(route('backend.document-requests.show', $data), 'View', 'btn-warning', 'fa fa-eye');
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('document::request.index');
    }

    /**
     * Show the specified resource.
     */
    public function show(DocumentRequest $documentRequest)
    {
        canPerform('View Document Request');
        $documentRequest->load(['user', 'type']);
        return view('document::request.show', compact('documentRequest'));
    }

    /**
     * Generate Documenet Requested by the user;
     */
    public function preview(DocumentRequest $documentRequest)
    {


        canPerform('Generate Document Request');
        $documentRequest->load(['user', 'type']);
        $template = $documentRequest->type->template;

        $todayDate = \Carbon\Carbon::now()->format('d/m/Y');
        $template = str_replace('[[today]]', $todayDate, $template);
        $html =  $documentRequest->parseHtml($template);
        $html = parsehtml($html, $documentRequest);

        // dd($html);


        return view('document::request.preview', compact('html', 'documentRequest'));
    }

    public function reject(DocumentRequest $documentRequest)
    {

        $user = User::find($documentRequest->user_id);
        if ($user) {
            $documentRequest->status = DocumentRequestStatus::Rejected;
            $documentRequest->save();
            successMessage(createFlashMessage('Document', 'Rejected'));
            return back();
        } else {
            errorMessage(createFlashMessage('User is not available', 'currently'));
            return back();
        }
    }

    /**
     * Generate Documenet Requested by the user;
     */
    public function generate(DocumentRequest $documentRequest, Request $request)
    {
        canPerform('Generate Document Request');
        $html = $request->html;
        // $documentRequest->generateDocumentPdf($html);
        $user = User::find($documentRequest->user_id);
        if ($user) {
            // $existallowance = SetAllowanceDeducation::where('type', 2)->where('document_request_id', $documentRequest->id)->get();
            // $existallowance = UserSalaryAllowance::where('title', $documentRequest->type->name)->where('user_id', $user->id)->where('document_request_id', $documentRequest->id)->get();
            // if ($existallowance->count() == 0) {
            //     if ($documentRequest->amount > 0) {
            //         // $allow = SetAllowanceDeducation::create([
            //         //     'type' => 2,
            //         //     'name' => $documentRequest->type->name,
            //         //     'amount' => $documentRequest->amount,
            //         //     'document_request_id' => $documentRequest->id,
            //         // ]);
            //         $allowance = UserSalaryAllowance::create([
            //             'title' => $documentRequest->type->name,
            //             'amount' => $documentRequest->amount,
            //             'user_id' => $user->id,
            //             'allowance_type' => 'fixed',
            //             'salary_id' => 0, //$salaryid->id,
            //             'percentage_amount' => 0.00,
            //             'date' => now()->toDateString(),
            //             'month_code' => date('m'),
            //             'year' => date('Y'),
            //             'is_fixed_for_current_month' => 1,
            //             'document_request_id' => $documentRequest->id,
            //         ]);
            //     }
            // }
            // $existallowance = SetAllowanceDeducation::where('type', 2)->where('document_request_id', $documentRequest->id)->get();
            $existallowance = UserDeduction::where('title', $documentRequest->type->name)->where('user_id', $user->id)->where('document_request_id', $documentRequest->id)->get();

            if ($existallowance->count() == 0) {
                $work = $user->workDetail;
                $userFree   = $work->free_document_request ?? null;
                $userCharge = $work->document_request_charge ?? null;

                $defaultFree   = Setting::where('key', 'free_document_request')->value('value') ?? 0;
                $defaultCharge = Setting::where('key', 'document_request_charge')->value('value') ?? 0;

                $freeLimit = ($userFree > 0) ? $userFree : $defaultFree;
                $charge    = ($userCharge > 0) ? $userCharge : $defaultCharge;

                // Count for selected document type
                $generatedCount = DocumentRequest::where('user_id', $user->id)
                    ->where('document_type_id', $documentRequest->document_type_id)
                    ->where('status', 'completed')
                    ->count();
                // Calculate charge amount
                $amount = ($generatedCount >= $freeLimit) ? $charge : 0;

                if ($amount > 0) {
                    // $allowance = UserSalaryAllowance::create([
                    //     'title' => $documentRequest->type->name,
                    //     'amount' => $amount,
                    //     'user_id' => $user->id,
                    //     'allowance_type' => 'fixed',
                    //     'salary_id' => 0, //$salaryid->id,
                    //     'percentage_amount' => 0.00,
                    //     'date' => now()->toDateString(),
                    //     'month_code' => date('m'),
                    //     'year' => date('Y'),
                    //     'is_fixed_for_current_month' => 1,
                    //     'document_request_id' => $documentRequest->id,
                    // ]);
                    $addOvertime = UserDeduction::create([
                            'title' => $documentRequest->type->name,
                            'amount' => $amount,
                            'user_id' => $user->id,
                            'allowance_type' => 'fixed',
                            'salary_id' => 0, //$salaryid->id,
                            'percentage_amount' => 0.00,
                             'deduction_type' => 'fixed',
                            'date' => now()->toDateString(),
                            'month_code' => date('m'),
                            'year' => date('Y'),
                            'is_fixed_for_current_month' => 1,
                            'document_request_id' => $documentRequest->id,
                    ]);
                }
            }

            $documentRequest->generateDocumentPdf($html);
            $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'message' => 'Your Document Has Been Generated ',
                'route' => route('backend.employee.document-requests.index'),
                // Add any other user data you want to pass...
            ];
            $user->notify(new GenerateNotification($userData, $admin->id));
            if ($user && $user->ftoken !== null) {
                $response = $this->fcmService->sendFcmMessage($user->ftoken, 'Document Request', $userData['message'], 5);
            }
            // send notification manager
            $managers = User::permission('Document Request Manager Access')->where('id', '!=',  $user->id)->get();
            foreach ($managers as $manager) {
                if ($manager->ftoken) {
                    $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Document Request was Generated', 'Document Request was Generated', 15);
                }
            }
            //end
            successMessage(createFlashMessage('Document', 'Generated'));
            return back();
        } else {
            errorMessage(createFlashMessage('User is not available', 'currently'));
            return back();
            // $response = getErrorResponse(__trans('user_is_not_available_currently'));
            // return response()->json($response);
        }
    }

    /**
     * Download generate document for the request
     */
    public function download(DocumentRequest $documentRequest): BinaryFileResponse
    {
        canPerform('Manage Document Request');
        return response()->download(public_path($documentRequest->file_path), $documentRequest->getFileName());
    }
}
