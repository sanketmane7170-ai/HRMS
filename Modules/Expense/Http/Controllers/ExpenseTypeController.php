<?php

namespace Modules\Expense\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Expense\Entities\ExpenseType;
use Yajra\DataTables\Facades\DataTables;

class ExpenseTypeController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'expense-types');
    }

    /**
     * Display a listing of the announce type.
     * @return Renderable
     */
    public function index(Request $request)
    {
        canPerform('Manage Expense Type');
        if ($request->ajax()) {
            $data = ExpenseType::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('color', function ($row) {
                    return '<span class="badge" style="background:' . $row->color . '">' . $row->color . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Expense Type')) {
                        $btn = createActionButton(route('backend.expense-types.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Expense Type')) {
                        $btn .= createActionButton(route('backend.expense-types.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action','color'])
                ->make(true);
        }
        return view('expense::type.index');
    }

    /**
     * Show the form for creating a new announce type.
     * @return Renderable
     */
    public function create()
    {
        canPerform('Create Expense Type');
        $html = view('expense::type.create')->render();
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
        canPerform('Create Expense Type');
        $data = $request->validate([
            'name' => ['required', 'unique:expense_types,name'],
        ]);
        $response = getErrorResponse();
        try {
            $expenseType = ExpenseType::create($data);
            $response = getSuccessResponse(createFlashMessage('Expense Type', 'created'));
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
    public function edit(ExpenseType $expenseType)
    {
        canPerform('Edit Expense Type');
        $html = view('expense::type.edit', compact('expenseType'))->render();
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
    public function update(Request $request, ExpenseType $expenseType)
    {
        canPerform('Edit Expense Type');
        $data = $request->validate([
            'name' => ['required', 'unique:expense_types,name,' . $expenseType->id],
        ]);
        $response = getErrorResponse();
        try {
            $expenseType->update($data);
            $response = getSuccessResponse(createFlashMessage('Expense Type', 'updated'));
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
    public function destroy(ExpenseType $expenseType)
    {
        $response = getErrorResponse();

        try {
            $expenseType->delete();
            $response = getSuccessResponse(createFlashMessage('Expense Type', 'Deleted'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
