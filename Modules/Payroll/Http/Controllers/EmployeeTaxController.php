<?php

namespace Modules\Payroll\Http\Controllers;


use Modules\Payroll\Entities\EmployeeTax;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;

use Exception;

class EmployeeTaxController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'taxation');
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employeeTaxes = EmployeeTax::select(['id', 'taxtype', 'taxunit', 'taxamount']);

            return DataTables::of($employeeTaxes)
                ->addColumn('action', function ($employeeTaxes) {
                    $btn = '';
                    $btn .= createActionButton(route('backend.payroll.taxes.edit', [$employeeTaxes->id]), '', 'btn-warning edit-button', 'fa fa-edit');
                    return $btn;
                })
                ->rawColumns(['action', 'id'])
                ->make(true);
        }
        $employeeTaxes = EmployeeTax::all();
        return view('payroll::taxes.index', compact('employeeTaxes'));
    }

    public function create()
    {
        //return view('payroll::taxes.create');
        $html = view('payroll::taxes.create')->render();
        $response = [
            'success' => true,
            'html' => $html
        ];
        return response()->json($response);
    }
    public function edit(User $user, $id)
    {
        $employeeTax = EmployeeTax::findOrFail($id);
        $html = view('payroll::taxes.edit', compact('user', 'employeeTax'))->render();
        $response = [
            'success' => true,
            'html' => $html
        ];
        return response()->json($response);
    }
    public function store(Request $request)
    {
        $request->validate([
            'taxtype' => 'required|string',
            'taxunit' => 'required|string',
            'taxamount' => 'required|numeric',
        ]);
        $response = getErrorResponse();
        try {
            EmployeeTax::create($request->all());
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            return response()->json($response);
        }
        $response = getSuccessResponse(createFlashMessage('Tax', 'Created'));
        return response()->json($response);
    }

    
}
