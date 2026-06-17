<?php

namespace Modules\Announcement\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Announcement\Entities\AnnouncementType;
use Yajra\DataTables\Facades\DataTables;

class AnnouncementTypeController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'announcement-types');
    }

    /**
     * Display a listing of the announce type.
     * @return Renderable
     */
    public function index(Request $request)
    {
        canPerform('Manage Announcement Type');
        if ($request->ajax()) {
            $data = AnnouncementType::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('color', function ($row) {
                    return '<span class="badge" style="background:' . $row->color . '">' . $row->color . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Announcement Type')) {
                        $btn = createActionButton(route('backend.announcement-types.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Announcement Type')) {
                        $btn .= createActionButton(route('backend.announcement-types.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action','color'])
                ->make(true);
        }
        return view('announcement::type.index');
    }

    /**
     * Show the form for creating a new announce type.
     * @return Renderable
     */
    public function create()
    {
        canPerform('Create Announcement Type');
        $html = view('announcement::type.create')->render();
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created announce type in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // echo "<pre>";print_r($_REQUEST);exit();
        canPerform('Create Announcement Type');
        $data = $request->validate([
            'name' => ['required', 'unique:announcement_types,name'],
            'color' => ['required', 'unique:announcement_types,name'],
        ]);
        $response = getErrorResponse();
        try {
            $announcementType = AnnouncementType::create($data);
            $response = getSuccessResponse(createFlashMessage('Announcement Type', 'created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified announce type.
     * @param int $id
     * @return Renderable
     */
    public function edit(AnnouncementType $announcementType)
    {
        canPerform('Edit Announcement Type');
        $html = view('announcement::type.edit', compact('announcementType'))->render();
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified announce type in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, AnnouncementType $announcementType)
    {
        canPerform('Edit Announcement Type');
        $data = $request->validate([
            'name' => ['required', 'unique:announcement_types,name,' . $announcementType->id],
            'color' => ['required', 'unique:announcement_types,name'],
        ]);
        $response = getErrorResponse();
        try {
            $announcementType->update($data);
            $response = getSuccessResponse(createFlashMessage('Announcement Type', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified announce type from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(AnnouncementType $announcementType)
    {
        $response = getErrorResponse();

        try {
            $announcementType->delete();
            $response = getSuccessResponse(createFlashMessage('Announcement Type', 'Deleted'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
