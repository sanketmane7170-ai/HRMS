<?php

namespace Modules\Warning\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Modules\Warning\Entities\UserWarning;
use Modules\Warning\Enums\WarningType;
use Mpdf\Mpdf;
use Yajra\DataTables\Facades\DataTables;
use App\Mail\WarningEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\FirebaseService;
use App\Models\UserAppreciation;
use App\Mail\AppreciationEmail;

class WarningController extends Controller
{

    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'user-warnings');
        $this->fcmService = $fcmService;
    }



    /**
     * Display a listing of the warning.
     */

     public function showReviews(Request $request){

        return view('warning::showReviews');
     }

    public function index(Request $request): View|JsonResponse
    {
        canPerform('Manage Warning');
        if ($request->ajax()) {
            $data = UserWarning::with('user');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('type', function ($data) {
                    return $data->type->getHtml();
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Warning')) {
                        $btn = createActionButton(route('backend.user-warnings.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Edit Warning')) {
                        $btn .= createActionButton(route('backend.user-warnings.show', $row), 'View', 'btn-success', 'fa fa-eye');
                    }

                    if (hasPermission('Edit Warning')) {
                        if ($row->document) {
                            $path = "/uploads/users/$row->user_id/warnings/$row->document";
                            $btn .= createActionButton($path, 'Document', 'btn-success', 'fa fa-download', 'target="_blank"');
                        }
                    }
                    if (hasPermission('Edit Warning')) {
                        if ($row->ack_document) {
                            $path = "/uploads/users/$row->user_id/warnings/$row->ack_document";
                            $btn .= createActionButton($path, 'Acknowledgement', 'btn-success', 'fa fa-download', 'target="_blank"');
                        }
                    }


                    return $btn;
                })
                ->rawColumns(['action', 'type'])
                ->make(true);
        }
        return view('warning::warning.index');
    }

    /**
     * Show the form for creating a new warning.
     */
    public function create()
    {
        canPerform('Create Warning');
        $types = WarningType::cases();
        if (request()->getHost() !== config('domain.for_warning_letter')) {
            // Filter out 'performance' and 'notice_of_termination'
            $types = array_filter($types, function ($type) {
                return !in_array($type->value, ['performance', 'notice_of_termination']);
            });
        }
        if (request()->getHost() !== 'burro.momdigital.io') {
            $types = array_filter($types, function ($type) {
                return !in_array($type->value, ['termination']);
            });
        }
        $html = view('warning::warning.create', compact('types'))->render();
        $response = ['success' => true, 'html' => $html];

        return  response()->json($response);
    }

    /**
     * Store a newly created warning in storage.
     */
    public function store(Request $request)
    {
        canPerform('Create Warning');

        // my_dd($request->all());
        $input = $request->all();
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'type' => ['required', new Enum(WarningType::class)],
            'detail' => 'required',
            // 'acknowledgement' => 'required',
        ]);
        // dd($request->all());
        $response = getErrorResponse();
        if (isset($input['acknowledgement'])) {
            $data["acknowledgement"] = "Yes";
        } else {
            $data["acknowledgement"] = "No";
        }
        try {
            if (isset($input['document'])) {
                $file = $input['document'];
                $fileName =  $request->user_id . '_' . time() . '.' . $request->document->extension();

                $type = $request->document->getClientMimeType();
                $size = $request->document->getSize();
                $location = "uploads/users/$request->user_id/warnings";

                $storagePath = public_path($location);

                $ret = $request->document->move($location, $fileName);
                $documentPath = public_path("$location/$fileName");
                $data['document'] = $fileName;
            }
            // echo"<pre>";print_r($data);die;

            $userWarning = UserWarning::create($data);
            if (filter_var($userWarning->user->profile->personal_email, FILTER_VALIDATE_EMAIL)) {
                try {
                    if (isset($documentPath)) {
                        Mail::to($userWarning->user->profile->personal_email)->send(new WarningEmail($userWarning, $documentPath));
                    } else {
                        Mail::to($userWarning->user->profile->personal_email)->send(new WarningEmail($userWarning));
                    }
                } catch (Exception $e) {
                }
                $response = getSuccessResponse(createFlashMessage('Warning', 'raised'));
            } else {
                $response['error'] = 'Invalid recipient email address.';
            }
            if (env("FIREBASE_SERVER_KEY")) {
                $user_data = User::find($request->user_id);
                if (isset($request->user_id) && $request->user_id > 0) {
                    $get = $this->fcmService->sendFcmMessage($user_data->ftoken, 'Warning', 'New Warning raised', 3);
                }
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the specified warning.
     */
    public function show(UserWarning $userWarning)
    {
        $userWarning->load('user', 'user.department');
        return view('warning::warning.show', compact('userWarning'));
    }

    /**
     * Show the form for editing the specified warning.
     */
    public function edit(UserWarning $userWarning)
    {
        canPerform('Create Warning');
        $types = WarningType::cases();
        $userWarning->load('user');
        if (request()->getHost() !== 'burro.momdigital.io') {
            $types = array_filter($types, function ($type) {
                return !in_array($type->value, ['termination']);
            });
        }
        $html = view('warning::warning.edit', compact('types', 'userWarning'))->render();
        $response = ['success' => true, 'html' => $html];

        return  response()->json($response);
    }

    /**
     * Update the specified warning in storage.
     */
    public function update(Request $request, UserWarning $userWarning)
    {
        canPerform('Create Warning');
        $data =  $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'type' => ['required', new Enum(WarningType::class)],
            'detail' => 'required'
        ]);

        $response = getErrorResponse();
        if (isset($input['acknowledgement'])) {
            $data["acknowledgement"] = "Yes";
        } else {
            $data["acknowledgement"] = "No";
        }
        try {
            if (isset($input['document'])) {
                $fileName =  $request->user_id . '_' . time() . '.' . $request->document->extension();

                $type = $request->document->getClientMimeType();
                $size = $request->document->getSize();
                $location = "uploads/users/$request->user_id/warnings";

                $storagePath = public_path($location);

                $ret = $request->document->move($location, $fileName);
                $data['document'] = $fileName;
            }
            $userWarning->update($data);
            $response = getSuccessResponse(createFlashMessage('Waring', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified warning from storage.
     */
    public function destroy(UserWarning $userWarning)
    {
        canPerform('Delete Warning');
        try {
            //Log::info('Debugging message: UserWarning ID before deletion: ' . $userWarning->id);

            $deleted = $userWarning->delete();

            //Log::info('Deletion successful: ' . ($deleted ? 'true' : 'false'));

            $response = getSuccessResponse(createFlashMessage('Waring', 'deleted'));
            $response['redirect'] = route('backend.user-warnings.index');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        //my_dd($response);
        return redirect(route('backend.user-warnings.index'))->with('Waring', "deleted");
        //return response()->json(['redirect' => '/user-warnings']);
        //        return response()->json($response);
    }

    public function destroy2(UserWarning $userWarning)
    {
        canPerform('Delete Warning');

        try {
            //$deleted = $userWarning->delete();
            return redirect(route('backend.user-warnings.index'))->with('Waring', "deleted");
        } catch (Exception $e) {
        }
    }

    public function generate(UserWarning $userWarning)
    {
        canPerform('Manage Warning');
        $userWarning->load('user');
        $types = WarningType::cases();
        $logo = getLogo();
        if ($userWarning->type->value == 'performance') {
            $html = view('warning::warning.performance', compact('userWarning', 'types', 'logo'));
        } else if ($userWarning->type->value == 'notice_of_termination') {
            $html = view('warning::warning.termination', compact('userWarning', 'types', 'logo'));
        } else {
            $html = view('warning::warning.template', compact('userWarning', 'types'));
        }
        $mpdf = new Mpdf([
            'tempDir' => public_path('uploads/mpdf/temp'),
            'mode' => 'utf-8',
            'format' => 'A4-P',
            'setAutoTopMargin' => 'stretch',
            'autoMarginPadding' => 5
        ]);
        $mpdf->SetDisplayMode(90);
        $mpdf->WriteHTML($html);
        //call watermark content aand image
        $mpdf->SetWatermarkText(getSetting('site_title'));
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1;
        $filename = $userWarning->getFileName();
        $location = "uploads/users/$userWarning->user_id/warnings";
        $storagePath = public_path($location);
        if (!File::isDirectory($storagePath)) {
            File::makeDirectory($storagePath, 0777, true, true);
        }
        $mpdf->Output();
    }

    public function showAppreciation(Request $request){

        view()->share('activeLink', 'user-appreciation');

        // canPerform('Manage Warning');
        if ($request->ajax()) {
            $data = UserAppreciation::with('user');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    // if (hasPermission('Edit Warning')) {
                    $btn = createActionButton(route('backend.appreciation.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    // }
                    // if (hasPermission('Edit Warning')) {
                    $btn .= createActionButton(route('backend.appreciation.details', $row->id), 'View', 'btn-success', 'fa fa-eye');
                    // }

                    // if (hasPermission('Edit Warning')) {
                    if ($row->document) {
                        $path = "/uploads/users/$row->user_id/appreciation/$row->document";
                        $btn .= createActionButton($path, 'Document', 'btn-success', 'fa fa-download', 'target="_blank"');
                    }
                    // }
                    return $btn;
                })
                ->rawColumns(['action', 'type'])
                ->make(true);
        }
        return view('warning::appreciation.index');
    }

    public function createAppreciation()
    {
        //canPerform('Create Warning');
        $html = view('warning::appreciation.create')->render();
        $response = ['success' => true, 'html' => $html];

        return  response()->json($response);
    }

    public function storeAppreciation(Request $request)
    {
        //canPerform('Create Warning');
        $input = $request->all();
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'detail' => 'required',
        ]);

        $response = getErrorResponse();
        if (isset($input['acknowledgement'])) {
            $data["acknowledgement"] = "Yes";
        } else {
            $data["acknowledgement"] = "No";
        }

        try {
            if (isset($input['document'])) {
                $file = $input['document'];
                $fileName =  $request->user_id . '_' . time() . '.' . $request->document->extension();

                $type = $request->document->getClientMimeType();
                $size = $request->document->getSize();
                $location = "uploads/users/$request->user_id/appreciation";

                $storagePath = public_path($location);

                $ret = $request->document->move($location, $fileName);
                $data['document'] = $fileName;
            }
            $data['type'] = $request->type;

            $userWarning = UserAppreciation::create($data);
            $response = getSuccessResponse(createFlashMessage('Appreciation', 'raised'));
            if (env("FIREBASE_SERVER_KEY")) {
                $user_data = User::find($request->user_id);
                if (isset($request->user_id) && $request->user_id > 0) {
                    $get = $this->fcmService->sendFcmMessage($user_data->ftoken, 'info', 'New Appreciation raised', 3);
                }
            }
            if ($userWarning->user && isset($userWarning->user->profile) && filter_var($userWarning->user->profile->personal_email, FILTER_VALIDATE_EMAIL)) {

                $userAppreciation = UserAppreciation::find($userWarning->id);
                if (isset($input['document'])) {
                    $attachmentPath = public_path('uploads/users/'.$userAppreciation->user_id.'/appreciation/' . $userAppreciation->document);
                } else {
                    $attachmentPath = null;
                }
                try {
                    Mail::to($userWarning->user->profile->personal_email)->send(new AppreciationEmail($userAppreciation, $attachmentPath));
                    //return response()->json(['success' => true, 'message' => 'Appreciation email sent successfully']);
                } catch (\Exception $e) {
                    //return response()->json(['success' => false, 'message' => 'Failed to send email', 'error' => $e->getMessage()]);
                }
                $response = getSuccessResponse(createFlashMessage('Appreciation', 'raised'));
            } else {
                $response['error'] = 'Invalid recipient email address.';
            }
            
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function detailsAppreciation($id)
    {
        view()->share('activeLink', 'user-appreciation');

        $UserAppreciation = UserAppreciation::find($id);
        $UserAppreciation->load('user', 'user.department');
        return view('warning::appreciation.show', compact('UserAppreciation'));
    }

    public function editAppreciation($id)
    {
        //canPerform('Create Warning');
        $userAppreciation = UserAppreciation::find($id);
        $userAppreciation->load('user');
        $html = view('warning::appreciation.edit', compact('userAppreciation'))->render();
        $response = ['success' => true, 'html' => $html];

        return  response()->json($response);
    }

    public function updateAppreciation(Request $request,$id)
    {
        canPerform('Create Warning');
        $data =  $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            // 'type' => ['required', new Enum(WarningType::class)],
            'detail' => 'required'
        ]);
        $userAppreciation = UserAppreciation::find($id);

        $response = getErrorResponse();
        if (isset($request->acknowledgement)) {
            $data["acknowledgement"] = "Yes";
        } else {
            $data["acknowledgement"] = "No";
        }
        try {
            if (isset($request->document)) {
                $fileName =  $request->user_id . '_' . time() . '.' . $request->document->extension();

                $type = $request->document->getClientMimeType();
                $size = $request->document->getSize();
                $location = "uploads/users/$request->user_id/appreciation";

                $storagePath = public_path($location);

                $ret = $request->document->move($location, $fileName);
                $data['document'] = $fileName;
            }
            $data['type'] = $request->type;

            $userAppreciation->update($data);
            $response = getSuccessResponse(createFlashMessage('appreciation', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function appreciationDestroy($id)
    {
        canPerform('Delete Warning');
        try {
            $UserAppreciation = UserAppreciation::find($id);
            $deleted = $UserAppreciation->delete();

            $response = getSuccessResponse(createFlashMessage('User-Appreciation', 'deleted'));
            $response['redirect'] = route('backend.user-appreciation');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return redirect(route('backend.user-appreciation'))->with('Waring', "deleted");
    }
}
