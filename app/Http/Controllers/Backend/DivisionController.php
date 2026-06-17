<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;
use Yajra\DataTables\Facades\DataTables;

class DivisionController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'department');
    }

    // public function index(Request $request)
    // {
    //     canPerform('Manage Division');
    //     if ($request->ajax()) {
    //         $data = Division::with('branch');
    //         return DataTables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('manager', function ($row) {
    //                 return $row->manager->name;
    //             })
    //              ->editColumn('branch', function ($row) {
    //                 $name = $row->branch ? $row->branch->name : 'All';
    //                 return $name;
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $btn = '';
    //                 $actions = [];
    //                 if (hasPermission('Edit Division')) {
    //                     $actions[] = [route('backend.divisions.edit', $row), 'Edit', 'fa fa-edit', 'edit-button'];
    //                 }
    //                 if (hasPermission('Delete Division')) {
    //                     $actions[] = [route('backend.divisions.destroy', $row), 'Delete', 'fa fa-trash', 'action-button',  'datatable'];
    //                 }
    //                 return createActionDropdownList($actions);
    //             })
    //             ->rawColumns(['action', 'role_id'])
    //             ->make(true);
    //     }

    //     return view('backend.division.index');
    // }
    public function index(Request $request)
    {
        canPerform('Manage Division');

        if ($request->ajax()) {
            $search = $request->input('search.value');

            $query = Division::with(['branch', 'manager']);

            return DataTables::of($query)
                ->filter(function ($query) use ($search) {
                    if (!empty($search)) {
                        if ($search == 'all' || $search == 'All' || $search == 'ALL') {
                            $query->where(function ($q) {
                                $q->where('divisions.branch_id', "0");
                            });
                        } else {
                            $query->where(function ($q) use ($search) {
                                $q->where('divisions.id', 'like', "%{$search}%")
                                    ->orWhere('divisions.name', 'like', "%{$search}%")
                                    ->orWhere('divisions.code', 'like', "%{$search}%")
                                    ->orWhereHas('branch', function ($d) use ($search) {
                                        $d->where('name', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('manager', function ($m) use ($search) {
                                        $m->where('name', 'like', "%{$search}%");
                                    });
                            });
                        }
                    }
                }, true) // 👈 true = disable default search, only use ours


                ->addIndexColumn()
                ->addColumn('manager', function ($row) {
                    return $row->manager->name;
                })
                ->editColumn('branch', function ($row) {
                    return $row->branch ? $row->branch->name : 'All';
                })
                ->addColumn('action', function ($row) {
                    $actions = [];
                    if (hasPermission('Edit Division')) {
                        $actions[] = [route('backend.divisions.edit', $row), 'Edit', 'fa fa-edit', 'edit-button'];
                    }
                    if (hasPermission('Delete Division')) {
                        $actions[] = [route('backend.divisions.destroy', $row), 'Delete', 'fa fa-trash', 'action-button', 'datatable'];
                    }
                    return createActionDropdownList($actions);
                })
                ->rawColumns(['action'])
                ->escapeColumns([]) // 👈 prevents DT from mapping branch/manager to SQL
                ->make(true);
        }

        return view('backend.division.index');
    }

    public function store(Request $request)
    {

        canPerform('Create Division');
        $data = $request->validate([
            'name' => 'required|unique:divisions,name',
            'code' => 'required|unique:divisions,code',
            // 'branch_id' => 'required|exists:departments,id',
            'branch_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !\DB::table('departments')->where('id', $value)->exists()) {
                        $fail('The selected branch is invalid.');
                    }
                },
            ],

        ]);

        $response = getErrorResponse();
        try {
            Division::create($data);
            $response = getSuccessResponse(createFlashMessage('Division', 'Created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the division resource.
     */
    public function edit(Division $division)
    {
        canPerform('Edit Division');

        return response()->json([
            'success' => true,
            'html' => view('backend.division.edit', compact('division'))->render()
        ]);
    }

    /**
     * Update the division resource in storage.
     */
    public function update(Request $request, Division $division)
    {
        canPerform('Edit Division');
        $data = $request->validate([
            'name' => 'required|unique:divisions,name,' . $division->id,
            'code' => 'required|unique:divisions,code,' . $division->id,
            'branch_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // allow "0" for All branches
                    if ($value != 0 && !\DB::table('departments')->where('id', $value)->exists()) {
                        $fail('The selected branch is invalid.');
                    }
                },
            ],
        ]);

        $response = getErrorResponse();
        try {
            $division->update($data);
            $response = getSuccessResponse(createFlashMessage('Division', 'Updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function destroy(Division $division)
    {
        canPerform('Delete Division');
        $response = getErrorResponse();
        try {
            $division->delete();
            $response = getSuccessResponse(createFlashMessage('Division', 'Deleted'));
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "Cannot delete this division because it is used by other records.";
                $response['error'] = "Cannot delete this division because it is referenced by other records.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }
}
