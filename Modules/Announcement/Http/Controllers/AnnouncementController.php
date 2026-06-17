<?php

namespace Modules\Announcement\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Announcement\Entities\Announcement;
use Modules\Announcement\Entities\AnnouncementType;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\Department;
use App\Services\FirebaseService;
use App\Mail\AnnouncementEmail;
use Illuminate\Support\Facades\Mail;

class AnnouncementController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'announcements');
        $this->fcmService = $fcmService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        canPerform('Manage Announcement');
        if ($request->ajax()) {

            $data = Announcement::select('announcements.*', 'users.name as user_name', 'departments.name as department_name')
                ->leftJoin('users', function ($leftJoin) {
                    $leftJoin->on('users.id', '=', 'announcements.user_id');
                })
                ->leftJoin('departments', function ($leftJoin) {
                    $leftJoin->on('departments.id', '=', 'announcements.department_id');
                });

            //     $data = Announcement::query()
            //     ->leftJoin('users', function($leftJoin)
            // {
            //     $leftJoin->on('users.id', '=', 'announcements.user_id');



            // })
            // ->select([
            //     'users.name as user_name',
            //     'users.id as user_id',
            // ]);

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('body', function ($row) {

                    return shorterText($row->body);
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Announcement')) {
                        $btn = createActionButton(route('backend.announcements.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Edit Announcement')) {
                        $btn .= createActionButton(route('backend.announcements.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'color'])
                ->make(true);
        }
        return view('announcement::announcement.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $departmentId = 0;
        canPerform('Create Announcement');
        $announcementTypes = AnnouncementType::get(['id', 'name']);
        $departments = Department::get(['id', 'name']);
        $users = User::get(['id', 'name']);
        // dd($users);
        $html = view('announcement::announcement.create', compact('announcementTypes', 'departments', 'users', 'departmentId'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        canPerform('Create Announcement');

        $data =  $request->validate([
            'body' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleanString = strip_tags($value);
                    $cleanString = str_replace('&nbsp;', '', $cleanString);
                    $cleanString = str_replace(' ', '', $cleanString);
                    if ($cleanString === '') {
                        $fail('The ' . $attribute . ' field cannot be empty or contain only whitespace.');
                    }
                },
            ],
            'start_at' => 'required|date_format:Y-m-d H:i|after_or_equal:' . date('Y-m-d'),
            'end_at' => 'required|date_format:Y-m-d H:i|after:start_at',
            'announcement_type_id' => ['required', 'exists:announcement_types,id']
        ]);

        if (isset($request->user_id)) {
            $data['user_id'] =  $request->user_id;
        }
        if (isset($request->department_id)) {
            $data['department_id'] =  $request->department_id;
        }
       
        if ($request->hasFile('file')) {
            $file = $request->file;
            $fileName =  time() . '.' . $request->file->extension();
            $path = public_path('uploads/users/'.$request->user_id.'/announcement/');
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
            }
            $location = "uploads/users/".$request->user_id."/announcement/";
            $storagePath = public_path($location);
            $ret = $request->file->move($storagePath, $fileName);
            $data['file'] = $fileName;
        }

        $response = getErrorResponse();
        try {
            $user = User::find(auth()->id());
            $announcement = Announcement::create($data);
            $announcementType = AnnouncementType::Where('id',$announcement->announcement_type_id)->first();
            if(isset($request->user_id) && $request->user_id > 0 && $announcementType->name=="Increment Announcement"){
                $user_data = User::find($request->user_id);
                // echo"<pre>";print_r($user_data);die;
                if (filter_var($user_data->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        Mail::to($user_data->email)->send(new AnnouncementEmail($announcement));
                    } catch (Exception $e) {
                        \Log::error('Failed to send email. Recipient: ' . $user_data->email);
                    }
                    $response = getSuccessResponse(createFlashMessage('Warning', 'raised'));
                } else {
                    $response['error'] = 'Invalid recipient email address.';
                }
            }
            if (env("FIREBASE_SERVER_KEY")) {
                $user_data = User::find($request->user_id);
                if (isset($request->department_id) && $request->department_id > 0) {
                    $get = $this->fcmService->sendFcmMessage('FOR_DEPARTMENT_USERS', 'Announcement', 'New Announcement Added', 2,$request->department_id);
                } else  if (isset($request->user_id) && $request->user_id > 0) {
                    $get = $this->fcmService->sendFcmMessage($user_data->ftoken, 'Announcement', 'New Announcement Added', 2);
                    
                } else {
                    $get = $this->fcmService->sendFcmMessage('FOR_ALL_USERS', 'Announcement', 'New Announcement Added', 2);
                }
            }

            $response = getSuccessResponse(createFlashMessage('Announcement Type', 'created'));
        } catch (Exception $e) {
            //dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Announcement $announcement)
    {
        canPerform('Edit Announcement');
        $announcementTypes = AnnouncementType::get(['id', 'name']);
        $html = view('announcement::announcement.edit', compact('announcementTypes', 'announcement'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Announcement $announcement)
    {
        canPerform('Edit Announcement');

        $data =  $request->validate([
            'body' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleanString = strip_tags($value);
                    $cleanString = str_replace('&nbsp;', '', $cleanString);
                    $cleanString = str_replace(' ', '', $cleanString);
                    if ($cleanString === '') {
                        $fail('The ' . $attribute . ' field cannot be empty or contain only whitespace.');
                    }
                },
            ],
            'start_at' => 'required|date_format:Y-m-d H:i|after_or_equal:' . date('Y-m-d'),
            'end_at' => 'required|date_format:Y-m-d H:i|after:start_at',
            'announcement_type_id' => ['required', 'exists:announcement_types,id']
        ]);
        if (isset($request->user_id)) {
            $data['user_id'] =  $request->user_id;
        }
        if (isset($request->department_id)) {
            $data['department_id'] =  $request->department_id;
        }
        $response = getErrorResponse();
        try {
            if ($request->hasFile('file')) {
                $path = public_path('uploads/users/'.$request->user_id.'/announcement/');
                $oldPhotoPath = $path . $announcement->file;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
                $file = $request->file;
                $fileName =  time() . '.' . $request->file->extension();
                $path = public_path('uploads/users/'.$request->user_id.'/announcement/');
                if (!file_exists($path)) {
                    mkdir($path, 0775, true);
                }
                $location = "uploads/users/".$request->user_id."/announcement/";
                $storagePath = public_path($location);
                
                $ret = $request->file->move($storagePath, $fileName);
                $data['file'] = $fileName;
            }
            $user = User::find(auth()->id());
            $announcement->update($data);
            //$get = $this->fcmService->sendFcmMessage($user->ftoken, 'Announcement', 'New Announcement Added');
            $response = getSuccessResponse(createFlashMessage('Announcement', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Announcement $announcement)
    {
        $response = getErrorResponse();
        try {
            $announcement->delete();
            $response = getSuccessResponse(createFlashMessage('Announcement', 'deleted'));
        } catch (Exception $e) {
        }

        return response()->json($response);
    }
}
