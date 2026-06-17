<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\LeaveApprovalSetting;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class LeaveApprovalSettingController extends Controller
{

    public function __construct()
    {
        view()->share('activeLink', 'leave_approval');
    }
    public function index()
    {
        $leaveApprovalSetting = LeaveApprovalSetting::all();
        $permission_roles = Role::whereNotIn('name', ['Admin'])->pluck('name', 'id');
    
        // Map settings to role_id => level
        $levels = $leaveApprovalSetting->pluck('level', 'role_id');
    
        return view('backend.leave-approval', compact('leaveApprovalSetting', 'permission_roles', 'levels'));
    }
    
    public function store(Request $request)
    {
        try {
            LeaveApprovalSetting::truncate();

            if (! $request->has('levels')) {
                return redirect()->back()->with('success', 'Leave approval settings saved successfully!');
            }

           
            foreach ($request->levels as $roleId => $level) {
                if ($level) {

                  
                    LeaveApprovalSetting::create([
                        'role_id' => $roleId,
                        'level'   => $level,
                        'step_number'   => $level,
                        'approvers'   => "report_to_id",
                    ]);

                }
            }

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->back()->with('success', 'Leave approval settings saved successfully!');

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response          = getFailureResponse(createFlashMessage('Leave approval Setting', 'Failed'));
            dd($response);
            if ($request->ajax()) {
                return response()->json($response, 500);
            }

            return redirect()->back()->with('error', 'Failed to save leave approval settings!');
        }
    }

}
