<?php

namespace Modules\Api\Http\Controllers\Leave;

use Illuminate\Routing\Controller;
use Modules\Api\Transformers\Leave\TypeListResource;
use Modules\Leave\Entities\LeaveType;

/**
 * @group 4. Leave
 */
class TypeController extends Controller
{

    /**
     * Return the list of all available leave types
     * @authenticated
     * @response status=200 scenario="Type Listed"{
     * "success": true,
     * "message": "Leave types fetched successfully",
     * "data":[
     *          {
     *              "id": 1,
     *              "name": "Maternity",
     *              "days": 45
     *          },
     *       ]
     *  }
     */
    public function index()
    {
        $types = TypeListResource::collection(LeaveType::get());

        return response()->success(__trans('leave_types_fetched_successfully'), $types);
    }
}
