<?php

namespace Modules\FileManager\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FileManager\Entities\FileManager;
use Modules\FileManager\Entities\FileDownloadLink;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Exports\FileDetailsSampleExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FileDetailsImport;
use App\Exports\FailedFileRowsUpdateExport;
use Illuminate\Http\JsonResponse;

class FileManagerController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'filemanager');
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        // ->where("employee_id", Auth::user()->id)->get()
        // $data = FileManager::query()->where("employee_id", Auth::user()->id)->get();
        // return $data;
        canPerform('Manage FileManager');
        if ($request->ajax()) {
            // $data = FileManager::query()->with(['employee','department'])->where("employee_id", Auth::user()->id)->where('file_status','Active')->get();
            $data = DB::table('departments')->get();
            //dd($data);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    $btn .= createActionButton(route('backend.filemanager.file', $row->id), 'File Manager', 'btn-warning', 'fa fa-file');
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('filemanager::index');
    }

    // public function getfilesbyid($id){
    //     $result['department'] = DB::table('departments')->where('id',$id)->first();
    //     $result['file_managers'] = FileManager::where('department_id',$id)->get();
    //     return view('filemanager::files',$result);
    // }
    public function getfilesbyid(Request $request, $id)
    {
        $query = FileManager::where('department_id', $id);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhere('expiry_days', 'like', "%{$search}%")
                    ->orWhere('expiry_date', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('file_type', 'like', "%{$search}%")
                    ->orWhere('file_real_name', 'like', "%{$search}%")
                ;
            });
        }

        $result['department'] = DB::table('departments')->where('id', $id)->first();
        $result['file_managers'] = $query->get();

        return view('filemanager::files', $result);
    }

    public function branchfiledelete($id)
    {
        canPerform('Delete FileManager');

        $FileManager = FileManager::find($id);
        $FileManager->delete();
        return back();
    }

    public function branchfilestore(Request $request)
    {
        canPerform('Create FileManager');

        $input = $request->all();
        $titles = $input['title'] ?? [];

        if (count($titles) > 0) {
            foreach ($titles as $key => $title) {
                if ($input['title'][$key] != null) {
                    $data = [
                        "title" => $title ?? "",
                        "comment" => $input['comment'][$key] ?? "",
                        "issue_date" => $input['issue_date'][$key] ?? null,
                        "expiry_date" => $input['expiry_date'][$key] ?? null,
                        "expiry_days" => $input['expiry_days'][$key] ?? 0,
                        "department_id" => $input['branch_id'] ?? null,
                    ];
                    if (isset($input['file'][$key])) {
                        $file = $input['file'][$key];
                        if ($file) {
                            $path = Storage::disk('s3')->put('organization/' . Auth::user()->id, $file);
                            $data["file_name"] = $file->getClientOriginalName();
                            $data["file_real_name"] = $file->getClientOriginalName();
                            $data["file_path"] = $path;
                            $data["file_desc"] = json_encode($file);
                            $data["file_type"] = $file->getClientOriginalExtension();
                        }
                    }
                    try {
                        if (isset($input['file_id'][$key]) && $input['file_id'][$key] > 0) {
                            FileManager::where('id', $input['file_id'][$key])->update($data);
                            $message = "updated";
                        } else {
                            FileManager::create($data);
                            $message = "created";
                        }
                        $response = getSuccessResponse(createFlashMessage('File Manager', $message));
                        $response['redirect'] = route('backend.filemanager.index');
                    } catch (Exception $e) {
                        $response['error'] = $e->getMessage();
                    }
                } else {
                    $response['error'] = 'Please enter valid value';
                }
            }
        } else {
            $response['error'] = 'Please enter valid value';
        }
        return response()->json($response);
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {

        $branch = DB::table('departments')->get();

        return view('filemanager::create')->with(['departments' => $branch]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|file', // validation rule for the file field
            'branch_id' => 'required', // validation rule for the branch_id field
            'title' => 'required', // validation rule for the title field
            'comment' => 'required', // validation rule for the comment field
            'expiry_date' => 'required|date', // validation rule for the expiry_date field
            'expiry_days' => 'required|numeric|min:0|max:100', // validation rule for the expiry_days field
        ], [
            'branch_id.required' => 'The department field is required.', // Change the validation message for branch_id to department
        ]);

        // If validation fails, return with errors
        if ($validator->fails()) {

            $response['error'] = $validator->errors()->toArray();;
            return response()->json($response, 422);
        }

        $id = Auth::user()->id;

        $file = $request->file;

        $path = $file->store(
            'organization/' . $id,
            's3',
            'public'
        );


        $data = array(
            "file_name" => $path,
            "file_real_name" => $file->getClientOriginalName(),
            "file_path" => $path,
            "file_size" => $file->getSize(),
            "file_desc" => json_encode($request->file('files')),
            "file_type" => $file->extension(),
            "upload_date" => Date::now(),
            "employee_id" => Auth::user()->id,
            "company_id" => 0,
            "department_id" => $request->branch_id,
            "title" => $request->title,
            "comment" => $request->comment,
            "expiry_date" => $request->expiry_date,
            "expiry_days" => $request->expiry_days,
        );

        try {
            FileManager::create($data);
            $response = getSuccessResponse(createFlashMessage('File Manager', 'created'));
            $response['redirect'] = route('backend.filemanager.index');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('filemanager::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(FileManager $filemanager)
    {
        $departments = DB::table('departments')->get();

        $assetPath = $filemanager->file_path;
        $expiry = now()->addHour();

        $extension = pathinfo($filemanager->file_name, PATHINFO_EXTENSION);

        $previewImage = Storage::disk('s3')->temporaryUrl(
            $assetPath,
            $expiry,
            ['ResponseContentDisposition' => 'inline']
        );

        //dd($previewImage);

        canPerform('Edit Document Type');
        return view('filemanager::edit', compact('filemanager', 'departments', 'previewImage', 'extension'))->render();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(FileManager $filemanager, Request $request)
    {
        canPerform('Edit FileManager');
        $input = $request->all();

        $file = $request->file;

        $data = array(
            "title" => ($input['title']) ? $input['title'] : "",
            "comment" => ($input['comment']) ? $input['comment'] : "",
            "expiry_date" => ($input['expiry_date']) ? $input['expiry_date'] : NULL,
            "expiry_days" => ($input['expiry_days']) ? $input['expiry_days'] : 0,
            "department_id" => ($input['branch_id']) ? $input['branch_id'] : NULL,
        );

        if (isset($request->file) && !empty($request->file)) {
            $path = Storage::disk('s3')->put('organization/' . Auth::user()->id, $request->file);

            $data["file_name"] = $file->getClientOriginalName();
            $data["file_real_name"] = $file->getClientOriginalName();
            $data["file_path"] = $path;
            $data["file_desc"] = json_encode($request->file);
        }


        $response = getErrorResponse();
        try {
            $filemanager->update($data);
            $response = getSuccessResponse(createFlashMessage('FileManager', 'updated'));
            $response['redirect'] = route('backend.filemanager.index');
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
    public function destroy(FileManager $filemanager)
    {
        canPerform('Delete FileManager');
        $response = getErrorResponse();
        try {
            // storage::disk('s3')->delete($filemanager->file_path);
            $filemanager->update(['file_status' => 'Deleted']);
            $response = getSuccessResponse(createFlashMessage('File Manager', 'Delete'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function download($id)
    {

        canPerform('Download FileManager');
        $response = getErrorResponse();
        try {
            $filemanager  = FileManager::where("id", $id)->first();
            $assetPath = $filemanager->file_path;

            $expiry = now()->addHour();

            $download_link = Storage::disk('s3')->temporaryUrl(
                $assetPath,
                $expiry,
                [
                    'ResponseContentDisposition' => 'attachment; filename=' . $filemanager->file_real_name
                ]
            );

            $data = array(
                "file_id" => $id,
                "download_link" => $download_link,
                "expiry_date" => $expiry,
            );

            FileDownloadLink::create($data);

            return redirect($download_link);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function ExportSampleToFileDetails()
    {
        return Excel::download(new FileDetailsSampleExport, 'sample_' . time() . '.xlsx');
    }

    public function updateFileDetailsToExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ], [
            'file.required' => __trans('Please upload file to import users')
        ]);
        $response = getErrorResponse();
        try {
            $import = new FileDetailsImport();
            $import->import($request->file('file'));

            $failedRows = $import->getFailedRows();

            if (!empty($failedRows)) {
                $filePath = 'uploads/failedexport/employee_update_import_failed.xlsx';

                if (file_exists(public_path($filePath))) {
                    unlink(public_path($filePath));
                }

                try {
                    Excel::store(new FailedFileRowsUpdateExport($failedRows), $filePath, 'real_public');
                } catch (\Exception $e) {
                    \Log::error('Error storing Excel file: ' . $e->getMessage());
                    return response()->json(['error' => $e->getMessage()], 500);
                }

                $response = getSuccessResponse(createFlashMessage('File', 'imported partially'));
                $response['download_url'] = asset($filePath);
            } else {
                $response = getSuccessResponse(createFlashMessage('File', 'imported successfully'));
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
