<?php
namespace Modules\Task\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseService;
use App\Traits\File;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Task\Entities\Task;
use Modules\Task\Entities\TaskComment;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    use File;
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'Task');
        $this->fcmService = $fcmService;
    }

    /**
     * Display a listing of the Task.
     * @return Renderable
     */
    public function index(Request $request)
    {

        canPerform('Manage Task');
        $statusCounts = Task::select('status', \DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $priorityCounts = Task::select('priority', \DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority');

        if ($request->ajax()) {

            $company_id    = 0;
            $task_id       = 0;
            $department_id = 0;
            $user_id       = 0;
            if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
            } else if (auth()->user()->companymanager()->exists()) {
                $company_id = auth()->user()->current_company_id;
            } else if (auth()->user()->taskmanager()->exists()) {
                $task_id = auth()->user()->task_id;
            } else if (auth()->user()->departmentmanager()->exists()) {
                $department_id = auth()->user()->department_id;
            } else {
                $user_id = auth()->user()->id;
            }

            $data = Task::with('assigned_to_user', 'assigned_by_user')->latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('assigned_to_user', function ($row) {
                    return isset($row->assigned_to_user) ? $row->assigned_to_user->name : "NA";
                })
                ->addColumn('assigned_by_user', function ($row) {
                    return isset($row->assigned_by_user) ? $row->assigned_by_user->name : "NA";
                })

                ->addColumn('action', function ($row) {
                    $actions = [];
                    if (hasPermission('Create Task')) {
                        $actions[] = [route('backend.task.user.add.form', $row), 'Assign User', 'fa fa-plus', 'edit-button'];
                    }
                    if (hasPermission('Edit Task')) {
                        $actions[] = [route('backend.task.edit', $row), 'Edit', 'fa fa-edit', 'edit-button'];
                    }
                    if (hasPermission('Create Task')) {
                        $actions[] = [route('backend.task.show', $row), 'Show', 'fa fa-eye'];
                    }
                    if (hasPermission('Delete Task')) {
                        $actions[] = [route('backend.task.destroy', $row), 'Delete', 'fa fa-trash', 'action-button', 'datatable'];
                    }
                    return createActionDropdownList($actions);
                })
                ->rawColumns(['action', 'role_id'])
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('task::task.index', compact('statusCounts', 'priorityCounts'));
    }

    public function show(Task $task)
    {

        return view('task::task.show', compact('task'));
    }

    public function create()
    {
        canPerform('Create Task');

        $html = view('task::task.create')->render();
        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function store(Request $request)
    {
        canPerform('Create Task');

        $data = $request->validate([
            // 'assigned_to' => ['required', 'exists:users,id'],
            'title'       => ['required'],
            'description' => ['required'],
            'priority'    => ['required'],
            'end_date'    => ['required'],
        ]);

        $response = getErrorResponse();
        try {
            // $data['assigned_by'] = auth()->user()->id;
            $Task = Task::create($data);

            $response = getSuccessResponse(createFlashMessage('Task', 'created'));
        } catch (Exception $e) {
            dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {

        $html = view('task::task.edit', compact('task'))->render();
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
        canPerform('Create Task');
        $Task = Task::findOrFail($id);
        $data = $request->validate([
            // 'assigned_to' => ['required', 'exists:users,id'],
            'title'       => ['required'],
            'description' => ['required'],
            'priority'    => ['required'],
            'end_date'    => ['required'],
        ]);

        $response = getErrorResponse();
        try {

            $Task->update($data);

            $response = getSuccessResponse(createFlashMessage('Task', 'updated'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    /**
     * Remove the specified Task from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Task $Task)
    {
        $Task->delete();
        $response = getSuccessResponse(createFlashMessage('Task', 'deleted'));
        return response()->json($response);
    }
    public function task_comment_store(Request $request, $taskId)
    {

        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $response = getErrorResponse();
        try {
            TaskComment::create([
                'task_id' => $taskId,
                'user_id' => auth()->id(),
                'comment' => $request->comment,
            ]);
            $response = getSuccessResponse(createFlashMessage('Task', 'created'));
        } catch (Exception $e) {
            dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }

        return redirect()->back()->with('success', 'Comment added successfully!');
    }

    public function task_comment_destroy($commentId)
    {
        $comment = TaskComment::findOrFail($commentId);
        if ($comment->user_id == auth()->id() || auth()->user()->isAdmin()) {
            $comment->delete();
        }
        $response = getSuccessResponse(createFlashMessage('Comment', 'deleted'));
        return redirect()->back()->with('success', 'Comment deleted!');
    }

    public function my_task()
    {
        $tasks = Task::where('assigned_to', Auth::id())->get()->groupBy('status');

        $statusCounts = Task::where('assigned_to', Auth::id())
            ->select('status', \DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $priorityCounts = Task::where('assigned_to', Auth::id())
            ->select('priority', \DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority');

        return view('task::task.my_task', compact('tasks', 'statusCounts', 'priorityCounts'));
    }

    public function create_my_task(Request $request)
    {

        $data = $request->validate([
            // 'assigned_to' => ['required', 'exists:users,id'],
            'title'       => ['required'],
            'description' => ['required'],
            'priority'    => ['required'],
            'end_date'    => ['required'],
        ]);

        $response = getErrorResponse();
        try {
            $data['assigned_by'] = auth()->user()->id;
            $data['assigned_to'] = auth()->user()->id;
            $Task                = Task::create($data);

            $response = getSuccessResponse(createFlashMessage('Task', 'created'));
        } catch (Exception $e) {
            dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
    public function addUserForm(Task $task)
    {

        $users = User::select(
            'id', 'name', 'email'
        )
            ->whereNotIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]) // Ensure ROLE_ADMIN is an array or single ID
            ->where('status', User::STATUS_ACTIVE)
            ->get();

        return response()->json([
            'success' => true,
            'html'    => view('task::task.add-user-form', compact('task', 'users'))->render(),
        ]);
    }

    /**
     * Add Users to particular Task
     */
    public function addUser(Task $task, Request $request)
    {
        $response = getErrorResponse();
        try {
            if ($request->users == "Select User") {
                $task->assigned_to = null;
                $task->assigned_by = null;
            } else {
                $task->assigned_to = $request->users;
                $task->assigned_by = auth()->user()->id;
            }
            
            $task->save();
            $response = getSuccessResponse(__trans('user_successfully_added_to_task'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $task->status = $request->input('status');

        if ($task->status === "in_progress" && empty($task->start_date)) {
            $task->start_date = now()->toDateString(); // Laravel's helper for current date
        }

        $task->save();

        return response()->json(['message' => 'Task status updated successfully.']);
    }

}
