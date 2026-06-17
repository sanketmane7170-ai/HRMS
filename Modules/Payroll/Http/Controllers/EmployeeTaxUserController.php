<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Payroll\Entities\EmployeeTaxUser;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Modules\Payroll\Entities\EmployeeTax;
use Exception;

class EmployeeTaxUserController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'taxmapping');
    }
    public function index()
    {
        if (request()->ajax()) {
            $employeeTaxUsers = EmployeeTaxUser::select(['id', 'user_id', 'employee_tax_id']);

            return DataTables::of($employeeTaxUsers)
                ->editColumn('user_id', function ($employeeTaxUsers) {
                    $user = User::where('id', $employeeTaxUsers->user_id)->first();
                    return $user->name;
                })
                ->editColumn('employee_tax_id', function ($employeeTaxUsers) {
                    $tax = EmployeeTax::where('id', $employeeTaxUsers->employee_tax_id)->first();
                    return $tax->taxtype;
                })
                ->addColumn('action', function ($employeeTaxUser) {
                    return createActionButton(route('backend.payroll.employeeTaxUsers.destroy', $employeeTaxUser->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('payroll::taxes.taxmapper');
    }

    // public function show($id)
    // {
    //     $employeeTaxUser = EmployeeTaxUser::findOrFail($id);
    //     return view('payroll::taxes.show', compact('employeeTaxUser'));
    // }

    public function create()
    {
        $user = User::pluck('name', 'id');
        $taxes = EmployeeTax::pluck('taxtype', 'id');
        $html = view('payroll::taxes.createtaxmapper', compact('user','taxes'))->render();
        $response = [
            'success' => true,
            'html' => $html
        ];
        return response()->json($response);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'employee_tax_id' => 'required',
            // Add other validation rules if necessary
        ]);
        try {
            EmployeeTaxUser::create($request->all());
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            return response()->json($response);
        }
        $response = getSuccessResponse(createFlashMessage('Tax', 'Created'));
        return response()->json($response);
    }

    public function edit($id)
    {
        $employeeTaxUser = EmployeeTaxUser::findOrFail($id);
        // Add code to prepare data for the edit view if needed
        return view('payroll::taxes.edit', compact('employeeTaxUser'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required',
            'employee_tax_id' => 'required',
            // Add other validation rules if necessary
        ]);

        $employeeTaxUser = EmployeeTaxUser::findOrFail($id);
        $employeeTaxUser->update($request->all());

        return redirect()->route('payroll::taxes.taxmapper')
            ->with('success', 'EmployeeTaxUser updated successfully.');
    }

    public function destroy($id)
    {
        $employeeTaxUser = EmployeeTaxUser::findOrFail($id);
        $employeeTaxUser->delete();

        $response = getSuccessResponse(createFlashMessage('Tax', 'Deleted'));
        return response()->json($response);
    }
}
