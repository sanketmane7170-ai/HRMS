<?php

namespace Modules\Attendance\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Services\CheckinService;

class EmployeeCheckInController extends Controller
{

    public function userCheckInCheckOut(CheckinService $checkinService)
    {
        $response = getErrorResponse();
        try {
            $checkin =  $checkinService->performCheckInCheckOut();
            if ($checkin) {
                $message = "clock_{$checkin->type->name}_successfully";
                $response = getSuccessResponse(__trans($message));
                $response['html'] = view('attendance::components.checkin')->render();
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    // /**
    //  * Clock In user as present for today
    //  */
    // public function clockIn(): JsonResponse
    // {

    //     $checkin = Checkin::my()->where('date', now()->toDateString())
    //         ->exists();
    //     if (!$checkin) {
    //         $checkin = Checkin::create([
    //             'user_id' => auth()->id(),
    //             'date' => now()->toDateString(),
    //             'time' => date('H:i:s'),
    //             'type' => CheckinType::IN
    //         ]);
    //         if ($checkin) {
    //             $response = getSuccessResponse(createFlashMessage('You', 'clock In'));
    //             $response['html'] = view('attendance::components.checkin')->render();
    //         }
    //     }

    //     return response()->json($response);
    // }

    // /**
    //  * Clock out user as present for today
    //  */
    // public function clockOut(): JsonResponse
    // {
    //     $response = getErrorResponse(__trans('please_clock_in_first ') . now()->toDateString());
    //     $checkin = Checkin::my()->where([
    //         'date' => now()->toDateString(),
    //         'type' => CheckinType::IN
    //     ])->count();
    //     if ($checkin == 1) {
    //         $checkout = Checkin::create([
    //             'user_id' => auth()->id(),
    //             'date' => now()->toDateString(),
    //             'time' => date('H:i:s'),
    //             'type' => CheckinType::OUT
    //         ]);
    //         if ($checkout) {
    //             $response = getSuccessResponse(createFlashMessage('You', 'clock out'));
    //             $response['html'] = view('attendance::components.checkin')->render();
    //         }
    //     }
    //     if ($checkin == 2) {
    //         $response['message'] = __trans('you_already_have_clocked_out');
    //     }

    //     return response()->json($response);
    // }
}
