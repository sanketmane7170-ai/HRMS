<?php

namespace Modules\Warning\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Warning\Entities\UserWarning;
use Yajra\DataTables\Facades\DataTables;

class EmployeeWarningController extends Controller
{

    public function __construct()
    {
        view()->share('activeLink', 'my-warnings');
    }

    /**
     * Display a listing of the warning.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $data = UserWarning::my()->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('type', function ($data) {
                    return $data->type->getHtml();
                })
                ->editColumn('detail', function ($data) {
                    return shorterText($data->detail, 100);
                })
                ->addColumn('action', function ($row) {
                    $btn = '';

                    $btn .= createActionButton(route('backend.employee.user-warnings.show', $row), 'View', 'btn-success', 'fa fa-eye');
                    return $btn;
                })
                ->rawColumns(['action', 'type', 'detail'])
                ->make(true);
        }
        return view('warning::employee.warning-index');
    }

    /**
     * Show the specified warning.
     */
    public function show($id)
    {
        $userWarning = UserWarning::with(['user' => ['department']])->my()->findOrFail($id);
        return view('warning::employee.warning-show', compact('userWarning'));
    }
}
