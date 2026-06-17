<?php

namespace Modules\Api\Http\Controllers\GeneralRequest;

use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\GeneralRequest\Entities\GeneralRequestType;

class GeneralRequestTypeController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'General request types list',
            'data'    => GeneralRequestType::select('id', 'name')->orderBy('name')->get(),
        ], 200);
    }
}
