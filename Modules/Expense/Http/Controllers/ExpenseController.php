<?php
namespace Modules\Expense\Http\Controllers;

use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use App\Traits\File;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Expense\Entities\Expense;
use Modules\Expense\Entities\ExpenseDocument;
use Modules\Expense\Entities\ExpenseType;
use Modules\Expense\Enums\ExpenseStatus;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    use File;
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'expense');
        $this->fcmService = $fcmService;
    }

    /**
     * Display a listing of the expense.
     * @return Renderable
     */
    public function index(Request $request)
    {

        // dd(auth()->user()->hasRole(User::ROLE_LM));
        $types = ExpenseType::get();
        canPerform('Manage Expense');
        if ($request->ajax()) {
            if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
                $data = Expense::with('type', 'user', 'documents', 'creator')->get();
            } else if (auth()->user()->hasRole("LM")) {
                // $data = Expense::with('type', 'user', 'documents', 'creator')
                // ->whereHas('user.workDetail', function ($query) {
                //     $query->where('report_to_id', auth()->user()->id);
                // });
                $data = Expense::with('type', 'user', 'documents', 'creator')
                    ->where(function ($query) {
                        $query->whereHas('user.workDetail', function ($query) {
                            $query->whereJsonContains('report_to_ids', (string) auth()->user()->id);
                            // $query->where('report_to_id', auth()->user()->id);
                        })
                            ->orWhere('user_id', auth()->user()->id);
                    })
                    ->get();
            } else {
                $data = Expense::with('type', 'user', 'documents', 'creator')->where("user_id", auth()->user()->id)->get();
            }
            // dd( $data);
            // dd($data);

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status->getHtml();
                    // return $row->status;
                })
                ->addColumn('action', function ($row) {
                    // dd($row);
                    $btn = '';
                    // if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
                    $btn = createActionButton(route('backend.expense.show', $row), 'View', 'btn-success', 'fa fa-eye');
                    // }
                    // dd(ExpenseStatus::Pending->value);
                    // dd($row->status);
                    if ($row->status->value == ExpenseStatus::Pending->value) {

                        if (hasPermission('Edit Expense')) {
                            $btn .= createActionButton(route('backend.expense.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                        }
                    }
                    if ($row->status->value == ExpenseStatus::Pending->value || $row->status->value == ExpenseStatus::Approved->value) {
                        if (hasPermission('Delete Expense')) {
                            $btn .= createActionButton(route('backend.expense.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                        }
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('expense::expense.index', compact('types'));
    }

    public function expensesReport(Request $request)
    {
        $types = ExpenseType::get();
        canPerform('Manage Expense');
        $users        = $dates        = [];
        $departmentId = '';
        $searchEmp    = '';
        $search       = false;
        if ($request->post()) {
            $search       = true;
            $departmentId = $request->department_id;
            $searchEmp    = $request->search_emp;
            $query        = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                });

            if ($departmentId !== 'all') {
                $query->where('department_id', $departmentId);
            }

            if (! empty($searchEmp)) {
                $query->where('name', 'like', '%' . $searchEmp . '%');
            }

            $users = $query->get();
        }
        view()->share('activeLink', 'expenses-report');
        return view('expense::expense.report', compact('types', 'filterEmployees', 'users', 'departmentId', 'searchEmp', 'search'));
    }

    /**
     * show  the lisitng expense from storage.
     */
    public function show(Expense $expense)
    {
        // dd($expense->creator);
        return view('expense::expense.show', compact('expense'));
    }

    public function create()
    {
        canPerform('Create Expense');
        $expenseTypes = ExpenseType::get();

        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
            $users = User::whereNotIn('name', [
                User::ROLE_ADMIN,
                User::ROLE_SUPER_ADMIN,
            ])->where('status', 'active')->get();
            $is_employee = false;
        } else if (auth()->user()->hasRole("LM")) {
            // $users = User::whereNotIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
            //     ->where('status', 'active')->get();
            $users = User::where('status', 'active')
                ->where(function ($query) {
                    $query->where('id', auth()->user()->id)
                        ->orWhereHas('workDetail', function ($query) {
                            $query->whereJsonContains('report_to_ids', (string) auth()->user()->id);
                            // $query->where('report_to_id', auth()->user()->id);
                        });
                })->get();
            $is_employee = true;
        } else {
            $is_employee = true;
            $users       = User::where('id', auth()->user()->id)->where('status', 'active')->get();
        }

        $html = view('expense::expense.create', compact('expenseTypes', 'users', 'is_employee'))->render();
        // dd($html);
        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function getUsers(Request $request): JsonResponse
    {
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
            $users = User::whereNotIn('name', [
                User::ROLE_ADMIN,
                User::ROLE_SUPER_ADMIN,
            ])->where('status', 'active')->get();
            $query = User::query()
                ->select('id', 'name', 'email', 'employee_id', 'department_id')
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [
                        User::ROLE_ADMIN,
                        User::ROLE_SUPER_ADMIN,
                    ]);
                })

                ->when($request->search, function ($query) use ($request) {
                    return $query->where('name', 'Like', "%$request->search%")
                        ->orWhere('email', 'Like', "%$request->search%")
                        ->orWhereHas('department', function ($query) use ($request) {
                            return $query->where('name', 'Like', "%$request->search%");
                        });
                });
            $users = $query->get();

            $is_employee = false;
        } else if (auth()->user()->hasRole("LM")) {
            // $users = User::whereNotIn('name', [
            //     User::ROLE_ADMIN,
            //     User::ROLE_SUPER_ADMIN,
            // ])->where('status', 'active')->get();
            $users = User::where('status', 'active')
                ->where(function ($query) {
                    $query->where('id', auth()->user()->id)
                        ->orWhereHas('workDetail', function ($query) {
                            $query->whereJsonContains('report_to_ids', (string) auth()->user()->id);
                            // $query->where('report_to_id', auth()->user()->id);
                        });
                })
                ->when($request->search, function ($query) use ($request) {
                    return $query->where('name', 'Like', "%$request->search%")
                        ->orWhere('email', 'Like', "%$request->search%")
                        ->orWhereHas('department', function ($query) use ($request) {
                            return $query->where('name', 'Like', "%$request->search%");
                        });
                })->get();
            $is_employee = true;
        } else {
            $is_employee = true;
            $users       = User::where('id', auth()->user()->id)->where('status', 'active')->get();
        }
        foreach ($users as $key => $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => "{$data->employee_id} - $data->name ",
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    public function store(Request $request)
    {
        canPerform('Create Expense');

        $data = $request->validate([
            'date'            => 'required',
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'name'            => ['required'],
            'payment_mode'    => ['required'],
            'amount'          => ['required'],
            'user_id'         => ['required'],
            // 'document.*' => 'required|file|mimes:jpg,png,jpeg,gif|max:2048', // Adjust the mime types and size limit as needed
        ]);

        $response = getErrorResponse();
        try {
            $data['created_by'] = auth()->user()->id;
            $expense            = Expense::create($data);
            if ($request->hasfile('document')) {
                foreach ($request->file('document') as $index => $document) {
                    $documentName = $request->input('document_name')[$index] ?? 'Untitled';
                    // $user_id = auth()->user()->id;
                    $user_id  = $data['user_id'];
                    $location = "uploads/expense/$user_id";
                    if (! file_exists(public_path($location))) {
                        mkdir(public_path($location), 0755, true);
                    }
                    $fileName = date('mdYHis') . uniqid() . time() . '_' . $document->getClientOriginalName();
                    $ret      = $document->move($location, $fileName);
                    ExpenseDocument::create([
                        'document_name' => $documentName,
                        'document'      => $fileName,
                        'expense_id'    => $expense->id,
                    ]);
                }
            }
            if ($data['user_id'] == auth()->user()->id) {
                $user = User::find(auth()->id());

                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Generated a Expense Request for ' . $expense->type->name,
                    'route'   => route('backend.expense.index'),
                    // Add any other user data you want to pass...
                ];
                $admin = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
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
                // $hr = User::role('hr')->first();
                $hr = User::where('status', 'active')->whereHas('roles', function ($query) {
                    $query->where('name', 'hr');
                })->first();
                if ($hr) {
                    $hr->notify(new GenerateNotification($userData, $hr->id));
                }
                $user   = User::where('id', $data['user_id'])->first();
                $lmList = $user->lineManager();
                foreach ($lmList as $lm) {
                    $lmuser = User::where('id', $lm->user_id)->first();
                    $lmuser->notify(new GenerateNotification($userData, $lmuser->id));
                }
            } else {
                $user     = User::find($data['user_id']);
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Expense is created by ' . $admin->name,
                    'route'   => route('backend.expense.index'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));
            }
            $response = getSuccessResponse(createFlashMessage('Expense Type', 'created'));
        } catch (Exception $e) {
            dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        canPerform('Edit Expense');
        $expenseTypes = ExpenseType::get(['id', 'name']);

        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
            $users = User::whereNotIn('name', [
                User::ROLE_ADMIN,
                User::ROLE_SUPER_ADMIN,
            ])->where('status', 'active')->get();
            $is_employee = false;
        } else if (auth()->user()->hasRole("LM")) {
            // $users = User::whereNotIn('name', [
            //     User::ROLE_ADMIN,
            //     User::ROLE_SUPER_ADMIN,
            // ])->where('status', 'active')->get();
            $users = User::where('status', 'active')
                ->where(function ($query) {
                    $query->where('id', auth()->user()->id)
                        ->orWhereHas('workDetail', function ($query) {
                            $query->whereJsonContains('report_to_ids', (string) auth()->user()->id);
                            // $query->where('report_to_id', auth()->user()->id);
                        });
                })->get();
            $is_employee = true;
        } else {
            $is_employee = true;
            $users       = User::where('id', auth()->user()->id)->where('status', 'active')->get();
        }

        $user = User::where('id', '=', $expense->user_id)->first();
        $html = view('expense::expense.edit', compact('expenseTypes', 'expense', 'user', 'users', 'is_employee'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        canPerform('Create Expense');
        $expense = Expense::findOrFail($id);
        $data    = $request->validate([
            'date'            => 'required',
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'name'            => ['required'],
            'payment_mode'    => ['required'],
            'amount'          => ['required'],

        ]);

        $response = getErrorResponse();
        try {

            $expense->update($data);
            if ($request->hasfile('document')) {
                foreach ($request->file('document') as $index => $document) {
                    $documentName = $request->input('document_name')[$index] ?? 'Untitled';
                    $user_id      = auth()->user()->id;
                    $location     = "uploads/expense/$user_id";
                    if (! file_exists(public_path($location))) {
                        mkdir(public_path($location), 0755, true);
                    }
                    $fileName = date('mdYHis') . uniqid() . time() . '_' . $document->getClientOriginalName();
                    $ret      = $document->move($location, $fileName);
                    ExpenseDocument::create([
                        'document_name' => $documentName,
                        'document'      => $fileName,
                        'expense_id'    => $expense->id,
                    ]);
                }
            }
            $response = getSuccessResponse(createFlashMessage('Expense Request', 'updated'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    /**
     * Remove the specified expense from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        $response = getSuccessResponse(createFlashMessage('Expense Request', 'deleted'));
        // } else {
        // $response['message'] = __trans('permission_denied');
        // }

        return response()->json($response);
    }

    /**
     * Return Response for pending expense
     */
    public function takeAction(Expense $expense, $action)
    {
        $response = getErrorResponse();

        $message  = ($action == 'approve') ? 'Approved' : 'Rejected';
        $response = $this->$action($expense);

        return response()->json($response);
    }

    /**
     * Save rejection remark for the expense
     */
    public function rejectExpense(Expense $expense, Request $request)
    {
        // dd($expense);

        $data = $request->validate([
            'remark' => 'required',
        ]);

        $response = getErrorResponse();
        try {

            if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
                $expense->hr_id       = auth()->user()->id;
                $expense->lm_id       = auth()->user()->id;
                $expense->hr_status   = ExpenseStatus::Rejected->value;
                $expense->lm_status   = ExpenseStatus::Rejected->value;
                $expense->hr_comments = "Rejected By Admin";
                $expense->lm_comments = "Rejected By Admin";

                $user     = User::find($expense->user_id);
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Your Expense Request Rejected By Admin ' . $admin->name,
                    'route'   => route('backend.expense.index'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));
            } else if (auth()->user()->hasRole(User::ROLE_HR)) {
                $expense->hr_id       = auth()->user()->id;
                $expense->hr_status   = ExpenseStatus::Rejected->value;
                $expense->hr_comments = "Rejected By HR";

                $user     = User::find($expense->user_id);
                $hr       = User::where('id', auth()->user()->id)->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Your Expense Request Rejected By HR ' . $hr->name,
                    'route'   => route('backend.expense.index'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));
            } else if (auth()->user()->hasRole("LM")) {
                $expense->lm_id       = auth()->user()->id;
                $expense->lm_status   = ExpenseStatus::Rejected->value;
                $expense->lm_comments = "Rejected By LM";
                $expense->hr_id       = auth()->user()->id;
                $expense->hr_status   = ExpenseStatus::Rejected->value;
                $expense->hr_comments = "Rejected By LM";

                $user     = User::find($expense->user_id);
                $lm       = User::where('id', auth()->user()->id)->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Your Expense Request Rejected By LM ' . $lm->name,
                    'route'   => route('backend.expense.index'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));
            }
            // $expense = Expense::with('type', 'user', 'documents','creator')->where("user_id", auth()->user()->id)->get();
            if ($expense->hr_status == ExpenseStatus::Rejected->value || $expense->lm_status == ExpenseStatus::Rejected->value) {
                $expense->status = ExpenseStatus::Rejected->value;
                $expense->remark = $data['remark'];
            }

            // $expense->status = ExpenseStatus::Rejected->value;
            // $expense->remark = $data['remark'];
            if ($expense->save()) {
                $response = getSuccessResponse(createFlashMessage('Expense', 'rejected'));
            } else {
                return $response = getErrorResponse(__trans('you_dont_have_enough_expenses'));
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    protected function approve(Expense $expense)
    {
        // dd(auth()->user()->hasRole(User::ROLE_LM));
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            $expense->hr_id       = auth()->user()->id;
            $expense->lm_id       = auth()->user()->id;
            $expense->hr_status   = ExpenseStatus::Approved->value;
            $expense->lm_status   = ExpenseStatus::Approved->value;
            $expense->hr_comments = "Approved By Admin";
            $expense->lm_comments = "Approved By Admin";

            $user     = User::find($expense->user_id);
            $admin    = User::where('id', auth()->user()->id)->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Your Expense Request Approved By ' . $admin->name,
                'route'   => route('backend.expense.index'),
            ];
            $user->notify(new GenerateNotification($userData, $user->id));
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
            if ($user->ftoken != null) {

                $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "Your Expense Request Approved By " . $admin->name, 22);
            }

        } else if (auth()->user()->hasRole(User::ROLE_HR)) {
            $expense->hr_id       = auth()->user()->id;
            $expense->hr_status   = ExpenseStatus::Approved->value;
            $expense->hr_comments = "Approved By HR";

            $user     = User::find($expense->user_id);
            $hr       = User::where('id', auth()->user()->id)->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Your Expense Request Approved By HR ' . $hr->name,
                'route'   => route('backend.expense.index'),
            ];
            $user->notify(new GenerateNotification($userData, $user->id));
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
            if ($user->ftoken != null) {

                $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "Your Expense Request Approved By HR " . $hr->name, 22);
            }
        } else if (auth()->user()->hasRole("LM")) {
            $expense->lm_id       = auth()->user()->id;
            $expense->lm_status   = ExpenseStatus::Approved->value;
            $expense->lm_comments = "Approved By LM";

            $user     = User::find($expense->user_id);
            $lm       = User::where('id', auth()->user()->id)->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Your Expense Request Approved By LM ' . $lm->name,
                'route'   => route('backend.expense.index'),
            ];
            $user->notify(new GenerateNotification($userData, $user->id));
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
            if ($user->ftoken != null) {

                $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "Your Expense Request Approved By LM " . $lm->name, 22);
            }
        }
        else{
             $user     = User::find($expense->user_id);
            $approver       = User::where('id', auth()->user()->id)->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Your Expense Request Approved By ' . $approver->name,
                'route'   => route('backend.expense.index'),
            ];
            $user->notify(new GenerateNotification($userData, $user->id));
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
            if ($user->ftoken != null) {

                $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "Your Expense Request Approved By " . $admin->name, 22);
            }
        }
        // $expense = Expense::with('type', 'user', 'documents','creator')->where("user_id", auth()->user()->id)->get();
        if ($expense->hr_status == ExpenseStatus::Approved->value && $expense->lm_status == ExpenseStatus::Approved->value) {
            $expense->status = ExpenseStatus::Approved->value;
        }

        if ($expense->save()) {
            // $employee = User::find($expense->user_id);
            // $get = $this->fcmService->sendFcmMessage($employee->ftoken, 'Expense Approved', 'Expense Approved by admin', 2);
            return [
                'success'  => true,
                'message'  => createFlashMessage('Expense', 'Approved'),
                'redirect' => route('backend.expense.show', $expense),
            ];
        } else {
            return $response = getErrorResponse(__trans('you_dont_have_enough_expenses'));
        }
    }

    protected function reject(Expense $expense)
    {
        $html = view('expense::expense.cancel', compact('expense'))->render();

        return [
            'success' => true,
            'html'    => $html,
        ];
    }

    public function document_delete($id, $user_id)
    {
        $document = ExpenseDocument::findOrFail($id);
        $filePath = public_path('uploads/expense/' . $user_id . '/' . $document->document);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $document->delete();

        return response()->json(['success' => true]);
    }
}
