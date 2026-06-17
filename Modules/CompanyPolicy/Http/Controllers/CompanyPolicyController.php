<?php
namespace Modules\CompanyPolicy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CompanyPolicy;
use App\Models\User;
use App\Models\UserCompanyPolicy;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Yajra\DataTables\Facades\DataTables;

class CompanyPolicyController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth'); // ensures user is logged in
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
                return redirect()->route('backend.dashboard')->with('error', 'You do not have permission to access this page.');
            }
            return $next($request);
        });

        view()->share('activeLink', 'Company-Policy');
    }

    public function getCompanyPolicy(Request $request)
    {
        if ($request->ajax()) {
            $data = CompanyPolicy::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('acknowledgement', function ($row) {
                    return $row->acknowledgement; // raw value (0/1), DataTables render handles badge
                })
                ->addColumn('action', function ($row) {
                    $btn  = '';
                    // if (hasPermission('Edit Document Type')) {
                    $btn  = createActionButton(route('backend.editCompanyPolicy', $row), 'Edit', 'btn-warning', 'fa fa-edit');
                    // }
                    // if (hasPermission('Delete Document Type')) {
                    $btn .= createActionButton(route('backend.deleteCompanyPolicy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    // }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('companypolicy::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function addCompanyPolicy(Request $request)
    {
        return view('companypolicy::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeCompanyPolicy(Request $request)
    {
        $data = $request->validate([
            'title'           => 'required',
            'policy'          => 'required',
            'acknowledgement' => 'nullable|boolean',
        ]);

        $response = getErrorResponse();
        try {
            if (isset($request->document)) {
                $file     = $request->document;
                $fileName = time() . '.' . $request->document->extension();
                $path     = public_path('uploads/companypolicydocument');
                if (! file_exists($path)) {
                    mkdir($path, 0775, true);
                }
                $location = "uploads/companypolicydocument/";

                $storagePath = public_path($location);

                $ret              = $request->document->move($location, $fileName);
                $data['document'] = $fileName;
            }
            $policy = CompanyPolicy::create($data);
            $users  = User::withoutGlobalScopes()
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['Admin', 'Super Admin']);
                })
                ->where('status', 'active')
                ->get();
            foreach ($users as $user) {

                if ($user->ftoken != null) {
                    $fcmService = new FirebaseService();
                    $get        = $fcmService->sendFcmMessage($user->ftoken, 'Information', "A new company policy has been published.", 23);
                }
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'A new company policy has been published.',
                    'route'   => route('backend.getCompanyPolicy'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));

            }

            $response             = getSuccessResponse(createFlashMessage('Company Policy', 'created'));
            $response['redirect'] = route('backend.getCompanyPolicy');
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
        return view('companypolicy::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editCompanyPolicy(Request $request, $id)
    {
        $policy = CompanyPolicy::findOrFail($id);
        return view('companypolicy::edit', compact('policy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCompanyPolicy(Request $request, $id)
    {
        $data = $request->validate([
            'title'           => 'required',
            'policy'          => 'required',
            'acknowledgement' => 'nullable|boolean',
        ]);

        $response = getErrorResponse();
        try {
            $policy = CompanyPolicy::findOrFail($id);
            if (isset($request->document)) {
                // Delete old document if exists
                if ($policy->document && file_exists(public_path('uploads/companypolicydocument/' . $policy->document))) {
                    unlink(public_path('uploads/companypolicydocument/' . $policy->document));
                }
                // Upload new document
                $file     = $request->document;
                $fileName = time() . '.' . $request->document->extension();
                $path     = public_path('uploads/companypolicydocument');
                if (! file_exists($path)) {
                    mkdir($path, 0775, true);
                }
                $location = "uploads/companypolicydocument/";

                $storagePath = public_path($location);

                $ret              = $request->document->move($location, $fileName);
                $data['document'] = $fileName;
            }
            $policy->update($data);
            $response             = getSuccessResponse(createFlashMessage('Company Policy', 'updated'));
            $response['redirect'] = route('backend.getCompanyPolicy');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteCompanyPolicy($id)
    {
        $response = getErrorResponse();
        try {
            $policy = CompanyPolicy::findOrFail($id);
            $policy->delete();
            $response             = getSuccessResponse(createFlashMessage('Company Policy', 'deleted'));
            $response['redirect'] = route('backend.getCompanyPolicy');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
    public function getUserCompanyPolicy(Request $request)
    {
        view()->share('activeLink', 'User-Company-Policy');
        if ($request->ajax()) {
            $query = UserCompanyPolicy::with('policy', 'user');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('user_name', fn($row) => $row->user?->name ?? '-')
                ->addColumn('policy_title', fn($row) => $row->policy?->title ?? '-')
                ->addColumn(
                    'ack_status',
                    fn($row) =>
                    $row->ack_status
                        ? '<span class="badge bg-success">Acknowledged</span>'
                        : '<span class="badge bg-warning">Pending</span>'
                )
                ->addColumn(
                    'ack_datetime',
                    fn($row) =>
                    $row->ack_datetime ? $row->ack_datetime : '-'
                )
                ->addColumn(
                    'ack_document',
                    fn($row) =>
                    $row->ack_document
                        ? '<a href="' . asset('uploads/user_acknowledgements/' . $row->ack_document) . '" target="_blank">View</a>'
                        : '-'
                )

                ->rawColumns(['ack_status', 'ack_document'])
                ->make(true);
        }
        return view('companypolicy::usercompanypolicy');
    }
}
