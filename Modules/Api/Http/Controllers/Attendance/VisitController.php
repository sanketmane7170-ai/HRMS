<?php
namespace Modules\Api\Http\Controllers\Attendance;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Attendance\Services\VisitService;

class VisitController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    // public function index()
    // {
    //     $user_id = auth()->id();
    //     $data = LocationVisits::where('user_id', $user_id)->whereYear('date', now()->year)->orderBy('created_at', 'desc')->paginate(15)->toArray();

    //     if(empty($data)){
    //         return response()->json([
    //             'success' => true,
    //             'message' => __trans('visits_data_loaded_successfully'),
    //             'data' => [],
    //         ]);
    //     }

    //     return response()->success(__trans('visits_data_loaded_successfully'), $data);
    // }

    public function index(Request $request)
    {
        $user_id = auth()->id();

        // Read from query string
        $month = $request->query('month', now()->month);
        $year  = $request->query('year', now()->year);

        $data = LocationVisits::where('user_id', $user_id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('created_at', 'desc')
            ->paginate(15); // page is auto-read from ?page=1

        if ($data->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => __trans('visits_data_loaded_successfully'),
                'data'    => [],
            ]);
        }

        return response()->success(
            __trans('visits_data_loaded_successfully'),
            $data
        );
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

    public function handleMultiVisits(Request $request, VisitService $visitService)
    {
        Log::info('handleMultiVisits', ["request" => $request]);
        $user_id = auth()->id();
        //$visitin = $visitService->performVisitInVisitOut();
        try {
            if ($request->filled(['location', 'visit_purpose'])) {
                // When Visit Started
                // User::where('id',$user_id)->update([
                //     'longitude' => $request->longitude,
                //     'latitude' => $request->latitude
                // ]);
                $visitin = $visitService->performVisitInVisitOut($request->all());
            } else {
                // When Visit End
                $visitin = $visitService->performVisitInVisitOut();
            }
            $data = [
                'is_currently_visit_in' => isUserVisitedIn(auth()->id()),
            ];

            return response()->success(createFlashMessage('you', 'Visit ' . $visitin->type->name), $data);
        } catch (\Exception $e) {
            return response()->error($e->getMessage());
        }
    }
}
