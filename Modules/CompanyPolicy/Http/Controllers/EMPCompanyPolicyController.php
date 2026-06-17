<?php

namespace Modules\CompanyPolicy\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\CompanyPolicy;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class EMPCompanyPolicyController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'Company-Policy');
    }

    public function showCompanyPolicy(Request $request)
    {
        if ($request->ajax()) {
            $data = CompanyPolicy::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn = createActionButton(route('backend.employee.viewCompanyPolicy', $row), 'View', 'btn-warning', 'fa fa-eye');
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('companypolicy::showCompanyPolicy');
    }

    public function viewCompanyPolicy(Request $request,$id)
    {
        $policy = CompanyPolicy::findOrFail($id);
        return view('companypolicy::viewPolicy', compact('policy'));
    }
}
