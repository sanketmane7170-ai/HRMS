<?php
namespace Modules\Api\Http\Controllers\Warning;

use App\Mail\AcknowledgementEmail;
use App\Models\CompanyPolicy;
use App\Models\User;
use App\Models\UserAppreciation;
use App\Models\UserCompanyPolicy;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Warning\Entities\UserWarning;

class WarningController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {

        $this->fcmService = $fcmService;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {

        // $requests = ServiceRequestResource::collection(DocumentRequest::with('type')->my()->orderBy('id', 'DESC')->paginate(10));
        // return response()->success(__trans('service_request_listed_successfully'), $requests);
    }

    public function getWarningList(Request $request)
    {
        $query = UserWarning::with(['User'])->orderBy('id', 'DESC');
        if (isset($request->user_id) && ! empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }
        if (isset($request->id) && ! empty($request->id)) {
            $query->where('id', $request->id);
        }
        $data = $query->get();
        foreach ($data as $row => $val) {
            $data[$row]["path"]         = url("/uploads/users/$val->user_id/warnings/");
            $data[$row]["pathwithfile"] = url("/uploads/users/$val->user_id/warnings/$val->document/");
        }

        if ($data) {
            $response['success']  = true;
            $response['base_url'] = url('/');
            $response['data']     = $data;
        } else {
            $response['success'] = false;
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function getUserWarningList(Request $request)
    {
        $user_id = auth()->id();
        // dd($user_id);
        $query = UserWarning::with(['User'])->where("user_id", $user_id)->orderBy('id', 'DESC');

        // $query = UserWarning::with(['User']);
        if (isset($request->user_id) && ! empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }
        if (isset($request->id) && ! empty($request->id)) {
            $query->where('id', $request->id);
        }
        $data = $query->get();
        // $path = url("/uploads/users/$user_id/warnings/");
        // $data["path"]=$path;
        foreach ($data as $row => $val) {
            $data[$row]["path"]         = url("/uploads/users/$user_id/warnings/");
            $data[$row]["pathwithfile"] = url("/uploads/users/$user_id/warnings/$val->document/");
            $data[$row]["list_type"]    = [
                "title" => "Warnings",
                "enum"  => "warning",
            ];
        }

        // Fetch appreciations
        $appre = UserAppreciation::with(['User'])
            ->where("user_id", $user_id)
            ->orderBy('id', 'DESC');

        if (! empty($request->user_id)) {
            $appre->where('user_id', $request->user_id);
        }
        if (! empty($request->id)) {
            $appre->where('id', $request->id);
        }
        $appre = $appre->get();

        // Add path to appreciations
        foreach ($appre as $key => $val) {
            $appre[$key]["path"]         = url("/uploads/users/$user_id/appreciation/");
            $appre[$key]["pathwithfile"] = url("/uploads/users/$user_id/appreciation/{$val->document}");
            $appre[$key]["list_type"]    = [
                "title" => "Appraisals",
                "enum"  => "appraisals",
            ];
        }

        // Merge warnings and appreciations
        $mergedData = $data->merge($appre);

        if (! $mergedData->isEmpty()) {
            $response['success']  = true;
            $response['base_url'] = url('/');
            $response['data']     = $mergedData;
        } else {
            $response['success'] = false;
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function getUserCompanyPolicyList(Request $request)
    {
        $user_id        = auth()->id();
        $company_policy = CompanyPolicy::get();
        foreach ($company_policy as $key => $val) {
            $company_policy[$key]["path"]         = url("/uploads/companypolicydocument/");
            $company_policy[$key]["pathwithfile"] = url("/uploads/companypolicydocument/{$val->document}");

            $company_policy[$key]["list_type"] = [
                "title" => "Company Policy",
                "enum"  => "company_policy",
            ];
            $ack = UserCompanyPolicy::where('user_id', $user_id)
                ->where('company_policy_id', $val->id)
                ->first();

            // If exists, add acknowledgement data
            $company_policy[$key]['user_acknowledgement'] = $ack ? [
                "company_policy_id" => $ack->company_policy_id,
                "user_id"           => $ack->user_id,
                "ack_status"        => $ack->ack_status,
                "ack_datetime"      => $ack->ack_datetime,
                "ack_document"      => $ack->ack_document,
                "base_url"          => url('uploads/user_acknowledgements'),
            ] : null;
        }

        if (! $company_policy->isEmpty()) {
            $response['success']  = true;
            $response['base_url'] = url('/');
            $response['data']     = $company_policy;
        } else {
            $response['success'] = false;
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function getUserAppreciationList(Request $request)
    {
        $user_id = auth()->id();
        // dd($user_id);
        $query = UserAppreciation::with(['User'])->where("user_id", $user_id)->orderBy('id', 'DESC');
        // $query = UserWarning::with(['User']);
        if (isset($request->user_id) && ! empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }
        if (isset($request->id) && ! empty($request->id)) {
            $query->where('id', $request->id);
        }
        $data = $query->get();
        // $path = url("/uploads/users/$user_id/warnings/");
        // $data["path"]=$path;
        foreach ($data as $row => $val) {
            $data[$row]["path"]         = url("/uploads/users/$user_id/appreciation/");
            $data[$row]["pathwithfile"] = url("/uploads/users/$user_id/appreciation/$val->document/");
        }

        if ($data) {
            $response['success']  = true;
            $response['base_url'] = url('/');
            $response['data']     = $data;
        } else {
            $response['success'] = false;
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function updateAcknowledgement(Request $request)
    {
        $user_id = auth()->id();
        $user    = User::find(auth()->id());

        $data['ack_status']   = $request->ack_status;
        $data['ack_document'] = $request->ack_document;
        $data['ack_datetime'] = now();

        $response = getErrorResponse();

        $fileName = $user_id . '_' . time() . '.' . $request->ack_document->extension();

        $type     = $request->ack_document->getClientMimeType();
        $size     = $request->ack_document->getSize();
        $location = "uploads/users/$user_id/warnings";

        $storagePath = public_path($location);

        $ret = $request->ack_document->move($location, $fileName);

        $data['ack_document'] = $fileName;
        //    echo"<pre>";print_r($data);die;

        $result = UserWarning::where('user_id', $user_id)
            ->where('id', $request->warning_id)
            ->update($data); // this will also update the record
                         //   echo"<pre>";print_r($data);die;

        $admin = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
        $hrs   = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'hr'); // Make sure 'hr' matches your database role name
            })
            ->where('status', User::STATUS_ACTIVE) // Filter only active users
            ->get();

        $userData = [
            'id'      => $user_id,
            'name'    => $user->name,
            'email'   => $user->email,
            'message' => 'User update acknowledgement',
            'route'   => route('backend.user-warnings.index'),
        ];
        // $admin->notify(new GenerateNotification($userData, $admin->id));
         $admins = User::withoutGlobalScopes()
                        ->whereHas('roles', function ($q) {
                            $q->whereIn('name', [
                                User::ROLE_ADMIN,
                                User::ROLE_SUPER_ADMIN,
                            ]);
                        })
                        ->get();

        foreach ($admins as $admin) {
            $admin->notify(new GenerateNotification($userData, $admin->id));
        }

        foreach ($hrs as $hr) {
            $hr->notify(new GenerateNotification($userData, $hr->id));

            if ($hr && isset($hr->profile) && filter_var($hr->profile->personal_email, FILTER_VALIDATE_EMAIL)) {

                $userAcknowledgement = UserWarning::find($request->warning_id);
                $attachmentPath      = public_path('uploads/users/' . $user_id . '/warnings/' . $fileName);
                try {
                    Mail::to($hr->profile->personal_email)->send(new AcknowledgementEmail($userAcknowledgement, $attachmentPath));
                } catch (\Exception $e) {
                }
                $response = getSuccessResponse(createFlashMessage('Acknowledgement', 'raised'));
            } else {
                $response['error'] = 'Invalid recipient email address.';
            }
        }

        if ($result) {
            $response             = getSuccessResponse(createFlashMessage('acknowledgement', 'updated'));
            $response['success']  = true;
            $response['base_url'] = url('/');
            $response['data']     = $data;
        } else {
            $response['success'] = false;
            $response['data']    = [];
        }
        // echo"<pre>";print_r($response);die;
        return response()->json($response);
    }
    public function usercompanypolicyack(Request $request)
    {
        $validated = $request->validate([
            'company_policy_id' => 'required|exists:company_policies,id',
            'ack_status'        => 'required|boolean',
            'ack_document'      => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);
        $ackDocument = null;
        // handle file upload if present
        if ($request->hasFile('ack_document')) {
            $fileName = time() . '_' . $request->file('ack_document')->getClientOriginalName();
            $path     = public_path('uploads/user_acknowledgements');
            if (! file_exists($path)) {
                mkdir($path, 0775, true);
            }
            $request->file('ack_document')->move($path, $fileName);
            $ackDocument = $fileName;
        }

        // $record = UserCompanyPolicy::create([
        //     'company_policy_id' => $validated['company_policy_id'],
        //     'user_id'           => $request->user()->id,
        //     'ack_status'        => $validated['ack_status'],
        //     'ack_datetime'      => now(),
        //     'ack_document'      => $ackDocument,
        // ]);
        $record = UserCompanyPolicy::updateOrCreate(
            [
                'company_policy_id' => $validated['company_policy_id'],
                'user_id'           => $request->user()->id,
            ],
            [
                'ack_status'   => $validated['ack_status'],
                'ack_datetime' => now(),
                // update document only if uploaded
                'ack_document' => $ackDocument ?? DB::raw('ack_document'),
            ]
        );
        $record['base_url'] = url('uploads/user_acknowledgements');

        return response()->json([
            'status'  => true,
            'message' => 'Acknowledgement saved successfully.',
            'data'    => $record,
        ]);
    }
}
