<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmployeeAssetController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //
        view()->share('activeLink', 'assets');

        if ($request->ajax()) {
            $data = auth()->user()->assignments()
                ->with([
                    'asset' => [
                        'type', 'manufacturer'
                    ]
                ])->orderBy('return_date');
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
        return view('asset::employee.index');
    }
}
