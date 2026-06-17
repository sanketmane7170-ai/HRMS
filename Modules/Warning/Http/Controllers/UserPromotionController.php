<?php

namespace Modules\Warning\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payroll\Traits\SalaryCalculation;
use Modules\Warning\Entities\UserPromotion;
use App\Models\UserPromotionType;
use App\Models\UserPromotionLetter;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\File;

class UserPromotionController extends Controller
{
    use SalaryCalculation;

    public function __construct()
    {
        view()->share('activeLink', 'user-promotion');
    }

    public function user_promotion(Request $request)
    {
        if ($request->ajax()) {
            $data = UserPromotionType::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Document Type')) {
                        $btn = createActionButton(route('backend.edit_promotion_letter_type', $row->id), 'Edit', 'btn-warning', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Document Type')) {
                        $btn .= createActionButton(route('backend.delete_promotion_letter_type', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('warning::user-promotion');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function add_promotion_letter_type()
    {
        return view('warning::promotion.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_promotion_letter_type(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:user_promotion_types,name',
            'template' => 'required'
        ]);

        $response = getErrorResponse();
        try {
            UserPromotionType::create($data);
            $response = getSuccessResponse(createFlashMessage('Promotion Letter Type', 'created'));
            $response['redirect'] = route('backend.user-promotion');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('warning::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit_promotion_letter_type($id)
    {
        canPerform('Edit Document Type');
        $documentType = UserPromotionType::find($id);

        return view('warning::promotion.edit', compact('documentType'))->render();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_promotion_letter_type(Request $request, $id)
    {
        canPerform('Edit Document Type');
        $data = $request->validate([
            'name' => 'required|unique:user_promotion_types,name,' . $id,
            'template' => 'required'
        ]);

        $response = getErrorResponse();
        try {
            $documentType = UserPromotionType::find($id);
            if ($documentType) {

                $documentType->update($data);
                $response = getSuccessResponse(createFlashMessage('Document Type', 'updated'));
                $response['redirect'] = route('backend.user-promotion');
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function delete_promotion_letter_type($id)
    {
        canPerform('Delete Document Type');
        $response = getErrorResponse();
        try {
            $documentType = UserPromotionType::find($id);
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

    public function user_promotion_letter(Request $request)
    {
        if ($request->ajax()) {
            $data = UserPromotionLetter::query()
                ->join('users', 'users.id', '=', 'user_promotion_letters.user_id')
                ->select('user_promotion_letters.*', 'users.name as user_name');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->user_name;
                })
                ->editColumn('letter_type', function ($row) {
                    $letters = UserPromotionType::where('id', $row->letter_type_id)->first();
                    return $letters ? $letters->name : '';
                })
                ->addColumn('old_position', fn($row) => $row->oldPosition->name ?? '-')
                ->addColumn('new_position', fn($row) => $row->newPosition->name ?? '-')
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Document Type')) {
                        $btn = createActionButton(route('backend.user_promotion_letter_edit', $row->id), 'Edit', 'btn-warning', 'fa fa-edit');
                    }
                    $btn .= createActionButton(route('backend.user_promotion_letter_details', $row->id), 'View', 'btn-info', 'fa fa-info');
                    if (hasPermission('Delete Document Type')) {
                        $btn .= createActionButton(route('backend.user_promotion_letter_delete', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('warning::user-promotion-letter');
    }

    public function user_promotion_letter_create(Request $request)
    {
        $users = User::query()->notAdmin()->where('status', User::STATUS_ACTIVE)->get();
        $letters = UserPromotionType::get();
        $designations = Designation::select('id', 'name')->get();
        return  view('warning::user-promotion.create', compact('users', 'letters', 'designations'))->render();
    }

    public function user_promotion_letter_store(Request $request)
    {

        $data = $request->validate([
            'user_id' => 'required',
            'letter_type_id' => 'required',
            // 'new_position' => 'required',
            'new_designation_id' => 'required|exists:designations,id',
        ]);
        $user = User::find($data['user_id']);
        if ($user) {
            $data['old_designation_id'] = $user->designation_id;
            $data['old_department_id'] = $user->department_id;
            $data['old_salary']        = $user->salary ? $user->salary->basic : 0;
            // $user->designation_id = $data['new_designation_id'];
            // $user->save();
        }
        $new_designation = Designation::find($data['new_designation_id']);
        if ($new_designation) {
            $data['new_position'] = $new_designation->name;
            $data['new_department_id'] = $new_designation->department_id;
        }

        $data['remarks'] = $request->remarks;
        $data['reason'] = "Promotion";
        $data['date'] = $request->promotion_date;
        $data['user_basic_salary'] = $request->user_basic_salary;
        $data['user_transportation_allowances'] = $request->user_transportation_allowances;
        $data['user_housing_allowances'] = $request->user_housing_allowances;
        $data['user_other_allowances'] = $request->user_other_allowances;
        $data['user_gross_salary'] = $request->user_gross_salary;

        $response = getErrorResponse();
        try {

            $letter = UserPromotionLetter::create($data);

            $user = User::where('id', $request->user_id)->with('department', 'designation')->first();
            $documentType = UserPromotionType::find($request->letter_type_id);
            // store pdf
            $file = $this->storePromotionLetterPDF($documentType->template, $user, $letter);
            $filePath = $file['filePath'];

            $email = $user->profile->personal_email;
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $message = "Dear " . $user->name . ",
                    Congratulations! We are pleased to inform you that you have been promoted to the position of " . $request->new_position . ", effective from " . $request->promotion_date . ".
                    Please find your official promotion letter attached for your reference.
                    We appreciate your continued contributions and look forward to your continued success in your new role.
                    Best regards,
                    " . getSetting('site_title');
                    $attachmentPath = $filePath;

                    Mail::raw($message, function ($msg) use ($email, $attachmentPath) {
                        $msg->to($email)
                            ->subject('Promotion Letter')
                            ->attach($attachmentPath, [
                                'as' => 'PromotionLetter.pdf',
                                'mime' => 'application/pdf'
                            ]);
                    });
                } catch (Exception $e) {
                    \Log::error('Failed to send email. Recipient: ' . $user->email);
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

    public function user_promotion_letter_edit(Request $request, $id)
    {
        canPerform('Edit Document Type');
        $documentType = UserPromotionLetter::find($id);
        $users = User::query()->notAdmin()->where('status', User::STATUS_ACTIVE)->get();
        $letters = UserPromotionType::get();
        $designations = Designation::select('id', 'name')->get();
        return view('warning::user-promotion.edit', compact('documentType', 'users', 'letters', 'designations'))->render();
    }

    public function user_promotion_letter_update(Request $request, $id)
    {
        $data = $request->validate([
            'user_id' => 'required|unique:user_increments,name,' . $id,
            'letter_type_id' => 'required',
            // 'new_position' => 'required',
            'new_designation_id' => 'required|exists:designations,id',

        ]);
        $user = User::find($data['user_id']);
        if ($user) {
            $data['old_department_id'] = $user->department_id;
            $data['old_department_id'] = $user->department_id;
            $data['old_salary']        = $user->salary ? $user->salary->basic : 0;
            // $user->designation_id = $data['new_designation_id'];
            // $user->save();
        }
        $new_designation = Designation::find($data['new_designation_id']);
        if ($new_designation) {
            $data['new_position'] = $new_designation->name;
            $data['new_department_id'] = $new_designation->department_id;
        }
        $data['date'] = $request->promotion_date;
        $data['remarks'] = $request->remarks;
        $data['reason'] = "Promotion";
        $data['user_basic_salary'] = $request->user_basic_salary;
        $data['user_transportation_allowances'] = $request->user_transportation_allowances;
        $data['user_housing_allowances'] = $request->user_housing_allowances;
        $data['user_other_allowances'] = $request->user_other_allowances;
        $data['user_gross_salary'] = $request->user_gross_salary;

        $response = getErrorResponse();
        try {
            $letter = UserPromotionLetter::find($id);
            $user = User::where('id', $request->user_id)->with('department', 'designation')->first();
            $documentType = UserPromotionType::find($request->letter_type_id);

            if ($letter) {
                $letter->update($data);
                // store pdf
                $file = $this->storePromotionLetterPDF($documentType->template, $user, $letter);
                $filePath = $file['filePath'];

                $email = $user->profile->personal_email;
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $message = "Dear " . $user->name . ",
                        Congratulations! We are pleased to inform you that you have been promoted to the position of " . $request->new_position . ", effective from " . $request->promotion_date . ".
                        Please find your official promotion letter attached for your reference.
                        We appreciate your continued contributions and look forward to your continued success in your new role.
                        Best regards,
                        " . getSetting('site_title');
                        $attachmentPath = $filePath;

                        Mail::raw($message, function ($msg) use ($email, $attachmentPath) {
                            $msg->to($email)
                                ->subject('Promotion Letter')
                                ->attach($attachmentPath, [
                                    'as' => 'PromotionLetter.pdf',
                                    'mime' => 'application/pdf'
                                ]);
                        });
                    } catch (Exception $e) {
                        \Log::error('Failed to send email. Recipient: ' . $user->email);
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

    public function user_promotion_letter_details(Request $request, $id)
    {
        canPerform('View Document Type');
        $letter = UserPromotionLetter::find($id);
        $user = User::where('id', $letter->user_id)->with('department', 'designation')->first();
        $documentType = UserPromotionType::find($letter->letter_type_id);
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
        $template = str_replace('[[new_position]]', $letter->new_position ? $letter->new_position : '', $template);
        $template = str_replace('[[department]]', $user->department ? $user->department?->name ?? 'NA' : '', $template);
        $template = str_replace('[[designation]]', $user->designation ? $user->designation->name : '', $template);
        $template = str_replace('[[user_gross_salary]]', $letter->user_gross_salary ? $letter->user_gross_salary : '', $template);
        $template = str_replace('[[user_basic_salary]]', $letter->user_basic_salary ? $letter->user_basic_salary : '', $template);
        $template = str_replace('[[user_transportation_allowances]]', $letter->user_transportation_allowances ? $letter->user_transportation_allowances : '', $template);
        $template = str_replace('[[user_housing_allowances]]', $letter->user_housing_allowances ? $letter->user_housing_allowances : '', $template);
        $template = str_replace('[[user_other_allowances]]', $letter->user_other_allowances ? $letter->user_other_allowances : '', $template);

        $template = str_replace('[[date]]', Carbon::parse($date)->format('d/m/Y'), $template);
        $template = str_replace('[[promotion_date]]', Carbon::parse($letter->date)->format('d/m/Y'), $template);
        $template = str_replace('[[sign]]', $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[small_logo]]', $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[logo]]', $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[header]]', $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[footer]]', $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 200px;">' : '', $template);
        $html = $template;

        return view('warning::user-promotion.details', compact('letter', 'html'));
    }

    public function storePromotionLetterPDF($template, $user, $letter)
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
        $template = str_replace('[[new_position]]', $letter->new_position ? $letter->new_position : '', $template);
        $template = str_replace('[[user_gross_salary]]', $letter->user_gross_salary ? $letter->user_gross_salary : '', $template);
        $template = str_replace('[[user_basic_salary]]', $letter->user_basic_salary ? $letter->user_basic_salary : '', $template);
        $template = str_replace('[[user_transportation_allowances]]', $letter->user_transportation_allowances ? $letter->user_transportation_allowances : '', $template);
        $template = str_replace('[[user_housing_allowances]]', $letter->user_housing_allowances ? $letter->user_housing_allowances : '', $template);
        $template = str_replace('[[user_other_allowances]]', $letter->user_other_allowances ? $letter->user_other_allowances : '', $template);
        $template = str_replace('[[date]]', Carbon::parse($date)->format('d/m/Y'), $template);
        $template = str_replace('[[promotion_date]]', Carbon::parse($letter->date)->format('d/m/Y'), $template);
        $template = str_replace('[[sign]]', $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[small_logo]]', $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[logo]]', $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[header]]', $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 200px;">' : '', $template);
        $template = str_replace('[[footer]]', $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 200px;">' : '', $template);

        $html = $template;

        $mpdf = new Mpdf(['tempDir' => public_path('uploads/mpdf/temp')]);
        $mpdf->WriteHTML($html);
        //call watermark content aand image
        $mpdf->SetWatermarkText(getSetting('site_title'));
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1;
        $filename = str()->slug('promotion_letter') . "_" . $letter->user_id . "_" . str()->slug($user->name) . ".pdf";
        $location = "uploads/users/$user->id/promotion-letter";
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

    public function user_promotion_letter_download($id)
    {
        $letter = UserPromotionLetter::find($id);
        $user = User::where('id', $letter->user_id)->with('department', 'designation')->first();
        $documentType = UserPromotionType::find($letter->letter_type_id);

        // store pdf
        $file = $this->storePromotionLetterPDF($documentType->template, $user, $letter);
        $filePath = $file['filePath'];
        $filename = $file['filename'];

        return response()->download($filePath, $filename);
    }

    public function user_promotion_letter_delete(Request $request, $id)
    {
        canPerform('Delete Document Type');
        $response = getErrorResponse();
        try {
            $documentType = UserPromotionLetter::find($id);
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
