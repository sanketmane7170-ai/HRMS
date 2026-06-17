<?php
namespace Modules\Api\Http\Controllers\Expense;

use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Expense\Entities\Expense;
use Modules\Expense\Entities\ExpenseDocument;

class ExpenseController extends Controller
{
    // public function __construct() {}
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    // List all expense types
    public function index()
    {
        $perPage = request()->get('per_page', 10);

        $query = Expense::with('type', 'user', 'documents', 'creator')
            ->orderBy('created_at', 'desc');

        $expenses = $query->where("user_id", auth()->user()->id)->paginate($perPage);
        if ($expenses->isEmpty()) {
            return response()->json([
                'message' => 'Expense not found',
                'status'  => false,
            ], 400);
        }

        return response()->json([
            'message'  => 'Expense fetched successfully.',
            'status'   => 'success',
            'data'     => $expenses,
            'base_url' => url('/'),
        ], 200);
    }

    // Create a new expense type
    public function store(Request $request)
    {
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
            $data['user_id']    = auth()->user()->id;
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
                $user = User::withoutGlobalScopes()->find(auth()->id());

                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Generated a Expense Request for ' . $expense->type->name,
                    'route'   => route('backend.expense.index'),
                    // Add any other user data you want to pass...
                ];
                // $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();
                $admin = User::withoutGlobalScopes()
                    ->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
                    ->first();
                if ($admin) {
                    $admin->notify(new GenerateNotification($userData, $admin->id));
                }
                if (auth()->user()->ftoken != null) {
                    $get = $this->fcmService->sendFcmMessage(auth()->user()->ftoken, 'Information', "Expense Created Sucessfully..!", 22);
                }

                // $hr = User::role('hr')->first();
                // $hr->notify(new GenerateNotification($userData, $hr->id));
                // $lm = User::where('status', 'active')
                //     ->whereHas('lineManager', function ($query) {
                //         $query->where('user_id', auth()->user()->id);
                //     })
                //     ->first();

                // if ($lm)
                //     $lm->notify(new GenerateNotification($userData, $lm->id));
            } else {
                $user = User::withoutGlobalScopes()->find($data['user_id']);
                // $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();
                $admin = User::withoutGlobalScopes()
                    ->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
                    ->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Expense is created by ' . $admin->name,
                    'route'   => route('backend.expense.index'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));
                if ($user->ftoken != null) {
                    $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "Expense Created Sucessfully..!", 22);
                }
            }
            return response()->json([
                'message'  => 'Expense created successfully.',
                'status'   => 'success',
                'data'     => $expense,
                'base_url' => url('/'),

            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Expense created failed',
                'status'  => false,
                'error'   => $e,
            ], 400);
        }
    }

    // Show a single expense type
    public function show(Request $request, $id)
    {
        $id = $request->route('id');

        $expenseType = Expense::find($id);
        // if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
        //     $expenses = Expense::with('type', 'user', 'documents', 'creator')->get();
        // } else  if (auth()->user()->hasRole("LM")) {
        //     $expenses = Expense::with('type', 'user', 'documents', 'creator')
        //         ->where(function ($query) {
        //             $query->whereHas('user.workDetail', function ($query) {
        //                 $query->where('report_to_id', auth()->user()->id);
        //             })
        //                 ->orWhere('user_id', auth()->user()->id);
        //         })
        //         ->find($id);
        // } else {
        //     $expenses = Expense::with('type', 'user', 'documents', 'creator')->find($id);
        // }
        $expenses = Expense::with('type', 'user', 'documents', 'creator')->find($id);
        if (! $expenses) {
            return response()->json([
                'message' => 'Expense not found',
                'status'  => false,
            ], 400);
        }
        return response()->json([
            'message'  => 'Expense fetched successfully.',
            'status'   => 'success',
            'data'     => $expenses,
            'base_url' => url('/'),

        ], 200);
    }

    // Update an existing expense type
    public function update(Request $request, $id)
    {
        $id      = $request->route('id');
        $expense = Expense::find($id);
        if (! $expense) {
            return response()->json([
                'message' => 'Expense not found',
                'status'  => false,
            ], 400);
        }
        $data = $request->validate([
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
            return response()->json([
                'message'  => 'Expense updated successfully.',
                'status'   => 'success',
                'data'     => $expense,
                'base_url' => url('/'),

            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Expense not found',
                'status'  => false,
                'error'   => $e->getMessage(),
            ], 400);
        }
    }

    // Delete an expense type
    public function destroy(Request $request, $id)
    {
        $id      = $request->route('id');
        $expense = Expense::find($id);

        if (! $expense) {
            return response()->json([
                'message' => 'Expense not found',
                'status'  => false,
            ], 400);
        }

        $expense->delete();
        return response()->json([
            'message' => 'Expense deleted successfully',
            'status'  => 'success',
            'data'    => $expense,
        ], 200);
    }

    public function document_delete($id)
    {
        $user_id  = auth()->user()->id;
        $document = ExpenseDocument::findOrFail($id);
        if (! $document) {
            return response()->json([
                'message' => 'Document not found',
                'status'  => false,
            ], 400);
        }
        $filePath = public_path('uploads/expense/' . $user_id . '/' . $document->document);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
            'status'  => 'success',
            'data'    => $document,
        ], 200);
    }
}
