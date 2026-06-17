<?php

namespace Modules\Api\Http\Controllers\Attendance;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Enums\BreakinType;
use Modules\Attendance\Services\BreakinService;
use Carbon\Carbon;
use Modules\Api\Transformers\AttendanceResource;
use App\Models\Setting;
use App\Models\User;
use App\Models\Department;

class BreakinController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('api::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('api::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('api::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('api::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function handleMultiBreakIns(BreakinService $breakinService)
    {
        $user_id = auth()->id();
            $breakin = $breakinService->performBreakInBreakOut();
            $data = [
                'is_currently_break_in' => isUserBreakedIn(auth()->id()),
            ];

            return response()->success(createFlashMessage('you', 'Break ' . $breakin->type->name), $data);
    }
}
