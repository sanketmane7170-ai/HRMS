<?php

namespace Modules\Warning\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserIncrement;
use App\Models\UserIncrementLetter;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\File;
use Modules\Payroll\Traits\SalaryCalculation;
use Illuminate\Support\Facades\Mail;
use App\Services\FirebaseService;
use App\Notifications\Leave\GenerateNotification;
use Exception;
use Illuminate\Support\Facades\Log;

class UserIncrementgController extends Controller
{
    use SalaryCalculation;
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'user-increment');
        $this->fcmService = $fcmService;
    }

    public function user_increment(Request $request)
    {
        if ($request->ajax()) {
            $data = UserIncrement::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Document Type')) {
                        $btn = createActionButton(route('backend.increment_letter.edit', $row->id), 'Edit', 'btn-warning', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Document Type')) {
                        $btn .= createActionButton(route('backend.increment_letter.delete', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('warning::user-increment');
    }

    public function increment_letter_create(Request $request)
    {
        return  view('warning::increment.create')->render();
    }

    public function store_increment_letter(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|unique:user_increments,name',
            'template' => 'required'
        ]);

        $response = getErrorResponse();
        try {
            UserIncrement::create($data);
            $response = getSuccessResponse(createFlashMessage('Document Type', 'created'));
            $response['redirect'] = route('backend.user-increment');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function edit_increment_letter(Request $request, $id)
    {
        canPerform('Edit Document Type');
        $documentType = UserIncrement::find($id);

        return view('warning::increment.edit', compact('documentType'))->render();
    }

    public function update_increment_letter(Request $request, $id)
    {
        canPerform('Edit Document Type');
        $data = $request->validate([
            'name' => 'required|unique:user_increments,name,' . $id,
            'template' => 'required'
        ]);

        $response = getErrorResponse();
        try {
            $documentType = UserIncrement::find($id);
            if ($documentType) {

                $documentType->update($data);
                $response = getSuccessResponse(createFlashMessage('Document Type', 'updated'));
                $response['redirect'] = route('backend.user-increment');
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function increment_letter_delete(Request $request, $id)
    {
        canPerform('Delete Document Type');
        $response = getErrorResponse();
        try {
            $documentType = UserIncrement::find($id);
            if ($documentType) {
                $documentType->delete();
                $response = getSuccessResponse(createFlashMessage('Document Type', 'Deleted'));
            }
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "Cannot delete this document type because it is used by other records.";
                $response['error'] = "Cannot delete this document type it is used by other records.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }

    public function user_increment_letter(Request $request)
    {
        if ($request->ajax()) {
            $data = UserIncrementLetter::query()
                ->join('users', 'users.id', '=', 'user_increment_letters.user_id')
                ->select('user_increment_letters.*', 'users.name as user_name');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->user_name;
                })
                ->editColumn('letter_type', function ($row) {
                    $letters = UserIncrement::where('id', $row->letter_type_id)->first();
                    return $letters ? $letters->name : '';
                })
                ->addColumn('department_name', function ($row) {
                    $user = User::where('id', $row->user_id)->with('department')->first();
                    return $user->department ? $user->department?->name ?? 'NA' : '';
                })
                ->editColumn('amount', function ($row) {
                    return $row->salary_increment_amount;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Document Type')) {
                        $btn = createActionButton(route('backend.user_increment_letter.edit', $row->id), 'Edit', 'btn-warning', 'fa fa-edit');
                    }
                    $btn .= createActionButton(route('backend.user_increment_letter.details', $row->id), 'View', 'btn-info', 'fa fa-info');
                    if (hasPermission('Delete Document Type')) {
                        $btn .= createActionButton(route('backend.user_increment_letter.delete', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('warning::user-increment-letter');
    }

    public function user_increment_letter_create(Request $request)
    {
        $users = User::query()->notAdmin()->where('status', User::STATUS_ACTIVE)->get();
        $letters = UserIncrement::get();

        return  view('warning::user-increment.create', compact('users', 'letters'))->render();
    }

    public function storeIncrementLetterPDF($template, $user, $letter)
    {
        // store pdf
        $template = $template;
        $date = date('Y-m-d');
        $gross_salary = $this->getGrossSalary($user, date('m'), date('Y'), date('Y-m-01'), date('Y-m-t'));
        $basic_salary = $user->salary ? $user->salary->basic : 0;
        $allowances = $user->salary ? json_decode($user->salary->fixed_allowances, true) : 0;
        $otherAllowances = $allowances['other_allowance'] > 0 ? $allowances['other_allowance'] : 0;

        $transportation_allowances = $allowances ? $allowances['transportation_allowance'] : 0;
        $housing_allowances = $allowances ? $allowances['housing_allowance'] : 0;
        $other_allowances = $allowances ? ($otherAllowances + $letter->salary_increment_amount) : 0;

        $template = str_replace('[[name]]', $user->name, $template);
        $template = str_replace('[[department]]', $user->department ? $user->department?->name ?? 'NA' : '', $template);
        $template = str_replace('[[designation]]', $user->designation ? $user->designation->name : '', $template);
        $template = str_replace('[[salary_increment_amount]]', $letter ? $letter->salary_increment_amount : '', $template);
        $template = str_replace('[[updated_salary]]', $letter ? ($letter->salary_increment_amount + $gross_salary) : '', $template);
        $template = str_replace('[[user_gross_salary]]', $gross_salary ? $gross_salary : '', $template);
        $template = str_replace('[[user_basic_salary]]', $basic_salary ? $basic_salary : '', $template);
        $template = str_replace('[[user_transportation_allowances]]', $transportation_allowances ? $transportation_allowances : '', $template);
        $template = str_replace('[[user_housing_allowances]]', $housing_allowances ? $housing_allowances : '', $template);
        $template = str_replace('[[user_other_allowances]]', $other_allowances ? $other_allowances : '', $template);
        $template = str_replace('[[date]]', Carbon::parse($date)->format('d/m/Y'), $template);
        $template = str_replace('[[salary_increment_date]]', Carbon::parse($letter->date)->format('d/m/Y'), $template);
        $template = str_replace('[[sign]]', $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[small_logo]]', $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[logo]]', $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[header]]', $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[footer]]', $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 200px;">' : '', $template);


        $template = str_replace('[[user_new_basic_salary]]', $letter ? ($letter->user_basic_salary) : '', $template);
        $template = str_replace('[[user_new_transportation_allowances]]', $letter ? ($letter->user_transportation_allowances) : '', $template);
        $template = str_replace('[[user_new_housing_allowances]]', $letter ? ($letter->user_housing_allowances) : '', $template);
        $template = str_replace('[[user_new_other_allowances]]', $letter ? ($letter->user_other_allowances) : '', $template);
        $template = str_replace('[[user_new_gross_salary]]', $letter ? ($letter->user_gross_salary) : '', $template);


        $html = $template;

        $mpdf = new Mpdf(['tempDir' => public_path('uploads/mpdf/temp')]);
        $mpdf->WriteHTML($html);
        //call watermark content aand image
        $mpdf->SetWatermarkText(getSetting('site_title'));
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1;
        $filename = str()->slug('increment_letter') . "_" . $letter->user_id . "_" . str()->slug($user->name) . ".pdf";
        $location = "uploads/users/$user->id/increment-letter";
        $storagePath = public_path($location);
        if (!File::isDirectory($storagePath)) {
            File::makeDirectory($storagePath, 0777, true, true);
        }
        $mpdf->Output("$storagePath/$filename");
        $filePath = $storagePath . '/' . $filename;
        return [
            'filePath' => $filePath,
            'filename' => $filename,
        ];
        //end
    }

    public function user_store_increment_letter(Request $request)
    {

        $data = $request->validate([
            'user_id' => 'required',
            'letter_type_id' => 'required',
            'salary_increment_amount' => 'required',
            'salary_increment_date' => 'required',
        ]);
        $data['date'] = $request->salary_increment_date;
        $data['user_basic_salary'] = $request->user_basic_salary?? null;
        $data['user_transportation_allowances'] = $request->user_transportation_allowances?? null;
        $data['user_housing_allowances'] = $request->user_housing_allowances?? null;
        $data['user_other_allowances'] = $request->user_other_allowances?? null;
        $data['user_gross_salary'] = $request->user_gross_salary?? null;
        $data['remarks'] = $request->remarks?? null;
        $response = getErrorResponse();
        try {

            $letter = UserIncrementLetter::create($data);

            $user = User::where('id', $request->user_id)->with('department', 'designation')->first();
            $documentType = UserIncrement::find($request->letter_type_id);
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'message' => 'Generated an Increment Letter.',
                'route' => route('backend.user-increment'),
                // Add any other user data you want to pass...
            ];

            $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();

            $user->notify(new GenerateNotification($userData, $admin->id));
            $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Increment Letter Added', 'Increment Letter created', 1);

            // store pdf
            $file = $this->storeIncrementLetterPDF($documentType->template, $user, $letter);
            $filePath = $file['filePath'];

            $email = $user->profile->personal_email;
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $message = "This email is for increment salary";
                    $attachmentPath = $filePath;

                    Mail::raw($message, function ($msg) use ($email, $attachmentPath) {
                        $msg->to($email)
                            ->subject('Salary Increment')
                            ->attach($attachmentPath, [
                                'as' => 'IncrementLetter.pdf',
                                'mime' => 'application/pdf'
                            ]);
                    });
                } catch (Exception $e) {
                    Log::error('Failed to send email. Recipient: ' . $user->email);
                }
                $response = getSuccessResponse(createFlashMessage('Warning', 'raised'));
            } else {
                $response['error'] = 'Invalid recipient email address.';
            }
            // end
            $response = getSuccessResponse(createFlashMessage('Letter', 'created'));
            $response['redirect'] = route('backend.user_increment_letter');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function user_edit_increment_letter(Request $request, $id)
    {
        canPerform('Edit Document Type');
        $documentType = UserIncrementLetter::find($id);
        $users = User::query()->notAdmin()->where('status', User::STATUS_ACTIVE)->get();
        $letters = UserIncrement::get();
        return view('warning::user-increment.edit', compact('documentType', 'users', 'letters'))->render();
    }

    public function user_update_increment_letter(Request $request, $id)
    {
        $data = $request->validate([
            'user_id' => 'required|unique:user_increments,name,' . $id,
            'letter_type_id' => 'required',
            'salary_increment_amount' => 'required',
        ]);
        $data['date'] = $request->salary_increment_date;
        $data['user_basic_salary'] = $request->user_basic_salary?? null;
        $data['user_transportation_allowances'] = $request->user_transportation_allowances?? null;
        $data['user_housing_allowances'] = $request->user_housing_allowances?? null;
        $data['user_other_allowances'] = $request->user_other_allowances?? null;
        $data['user_gross_salary'] = $request->user_gross_salary?? null;
        $data['remarks'] = $request->remarks?? null;

        $response = getErrorResponse();
        try {
            $letter = UserIncrementLetter::find($id);
            $user = User::where('id', $request->user_id)->with('department', 'designation')->first();
            $documentType = UserIncrement::find($request->letter_type_id);

            if ($letter) {
                $letter->update($data);
                // store pdf
                $file = $this->storeIncrementLetterPDF($documentType->template, $user, $letter);
                $filePath = $file['filePath'];

                $email = $user->profile->personal_email;
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $message = "This email is for increment salary";
                        $attachmentPath = $filePath;

                        Mail::raw($message, function ($msg) use ($email, $attachmentPath) {
                            $msg->to($email)
                                ->subject('Salary Increment')
                                ->attach($attachmentPath, [
                                    'as' => 'IncrementLetter.pdf',
                                    'mime' => 'application/pdf'
                                ]);
                        });
                    } catch (Exception $e) {
                        Log::error('Failed to send email. Recipient: ' . $user->email);
                    }
                    $response = getSuccessResponse(createFlashMessage('Warning', 'raised'));
                } else {
                    $response['error'] = 'Invalid recipient email address.';
                }
                // end
                $response = getSuccessResponse(createFlashMessage('Letter', 'updated'));
                $response['redirect'] = route('backend.user_increment_letter');
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function user_increment_letter_details(Request $request, $id)
    {
        canPerform('View Document Type');
        $letter = UserIncrementLetter::find($id);
        $user = User::where('id', $letter->user_id)->with('department', 'designation')->first();
        $documentType = UserIncrement::find($letter->letter_type_id);
        $date = date('Y-m-d');
        $gross_salary = $this->getGrossSalary($user, date('m'), date('Y'), date('Y-m-01'), date('Y-m-t'));
        $basic_salary = $user->salary ? $user->salary->basic : 0;
        $allowances = $user->salary ? json_decode($user->salary->fixed_allowances, true) : 0;
        $otherAllowances = $allowances['other_allowance'] > 0 ? $allowances['other_allowance'] : 0;

        $transportation_allowances = $allowances ? $allowances['transportation_allowance'] : 0;
        $housing_allowances = $allowances ? $allowances['housing_allowance'] : 0;
        $other_allowances = $allowances ? ($otherAllowances + $letter->salary_increment_amount) : 0;

        $template = $documentType->template;
        $template = str_replace('[[name]]', $user->name, $template);
        $template = str_replace('[[department]]', $user->department ? $user->department?->name ?? 'NA' : '', $template);
        $template = str_replace('[[designation]]', $user->designation ? $user->designation->name : '', $template);
        $template = str_replace('[[salary_increment_amount]]', $letter ? $letter->salary_increment_amount : '', $template);
        $template = str_replace('[[updated_salary]]', $letter ? ($letter->salary_increment_amount + $gross_salary) : '', $template);
        $template = str_replace('[[user_gross_salary]]', $gross_salary ? $gross_salary : '', $template);

        $template = str_replace('[[user_basic_salary]]', $basic_salary ? $basic_salary : '', $template);
        $template = str_replace('[[user_transportation_allowances]]', $transportation_allowances ? $transportation_allowances : '', $template);
        $template = str_replace('[[user_housing_allowances]]', $housing_allowances ? $housing_allowances : '', $template);
        $template = str_replace('[[user_other_allowances]]', $other_allowances ? $other_allowances : '', $template);

        $template = str_replace('[[date]]', Carbon::parse($date)->format('d/m/Y'), $template);
        $template = str_replace('[[salary_increment_date]]', Carbon::parse($letter->date)->format('d/m/Y'), $template);
        $template = str_replace('[[sign]]', $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[small_logo]]', $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[logo]]', $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[header]]', $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[footer]]', $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 200px;">' : '', $template);

        $template = str_replace('[[user_new_basic_salary]]', $letter ? ($letter->user_basic_salary) : '', $template);
        $template = str_replace('[[user_new_transportation_allowances]]', $letter ? ($letter->user_transportation_allowances) : '', $template);
        $template = str_replace('[[user_new_housing_allowances]]', $letter ? ($letter->user_housing_allowances) : '', $template);
        $template = str_replace('[[user_new_other_allowances]]', $letter ? ($letter->user_other_allowances) : '', $template);
        $template = str_replace('[[user_new_gross_salary]]', $letter ? ($letter->user_gross_salary) : '', $template);



        $html = $template;

        return view('warning::user-increment.details', compact('letter', 'html'));
    }

    public function downloadIncrementLetter($id)
    {
        $letter = UserIncrementLetter::find($id);
        $user = User::where('id', $letter->user_id)->with('department', 'designation')->first();
        $documentType = UserIncrement::find($letter->letter_type_id);

        // store pdf
        $file = $this->storeIncrementLetterPDF($documentType->template, $user, $letter);
        $filePath = $file['filePath'];
        $filename = $file['filename'];

        return response()->download($filePath, $filename);
    }

    public function user_increment_letter_delete(Request $request, $id)
    {
        canPerform('Delete Document Type');
        $response = getErrorResponse();
        try {
            $documentType = UserIncrementLetter::find($id);
            if ($documentType) {
                $documentType->delete();
                $response = getSuccessResponse(createFlashMessage('Letter', 'Deleted'));
            }
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "Cannot delete this document type because it is used by other records.";
                $response['error'] = "Cannot delete this document type it is used by other records.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }
}
