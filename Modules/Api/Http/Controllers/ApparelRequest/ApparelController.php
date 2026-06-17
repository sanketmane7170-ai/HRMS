<?php
namespace Modules\Api\Http\Controllers\ApparelRequest;

use Illuminate\Routing\Controller;
use Modules\Apparel\Entities\Apparel;
use Modules\Apparel\Entities\ApparelRequest;

class ApparelController extends Controller
{
    /**
     * Uniform List (with remaining limit)
     */
    // public function index()
    // {
    //     $apparels = Apparel::get()->map(function ($apparel) {

    //         $approvedQty = ApparelRequest::where('apparel_id', $apparel->id)
    //             ->where('status', 1) // approved
    //             ->sum('number_of_apparel');

    //         $remaining = max(
    //             ($apparel->number_of_given ?? 0) - $approvedQty,
    //             0
    //         );

    //         return [
    //             'id'              => $apparel->id,
    //             'name'            => $apparel->name,
    //             'total_limit'     => $apparel->number_of_given,
    //             'already_given'   => $approvedQty,
    //             'remaining_limit' => $remaining,
    //             'is_available'    => $remaining > 0,
    //         ];
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Uniform list fetched successfully',
    //         'data'    => $apparels,
    //     ], 200);
    // }
    public function index()
    {
        $apparels = Apparel::get()->map(function ($apparel) {

            $approvedQty = (int) ApparelRequest::where('apparel_id', $apparel->id)
                ->where('status', 1)
                ->sum('number_of_apparel');

            $totalGiven = (int) ($apparel->number_of_given ?? 0);

            $remaining = max($totalGiven - $approvedQty, 0);

            return [
                'id'              => $apparel->id,
                'name'            => $apparel->name,
                'total_limit'     => $totalGiven,
                'already_given'   => "$approvedQty",
                'remaining_limit' => $remaining,
                'is_available'    => $remaining > 0,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Uniform list fetched successfully',
            'data'    => $apparels,
        ], 200);
    }

}
