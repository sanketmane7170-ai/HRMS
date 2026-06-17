<?php

namespace App\Http\Controllers\Backend;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Enum;
use Exception;
use GuzzleHttp\Client;
use App\Models\Department;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PortalDetails;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function clearCache()
    {
        canPerform('Clear Cache Settings');
        $data = [
            'msg' => 'success',
            'message' => __trans('Cache Cleared and Optimized!')
        ];
        Setting::clear();

        // Run the Artisan command to clear the cache | Added by GAGAN 12-06-2024
        Artisan::call('cache:clear');

        if (request()->ajax()) {
            return response()->json($data);
        }
        successMessage(__trans('Cache Cleared and Optimized!'));
        return back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function general()
    {
        
        canPerform('General Settings');
        $page = 'general';
        $timezones = timezone_identifiers_list();

        $strRoleId = getSetting('shift_hierarchy_roles');
        $shiftHierarchyRoles = explode(",",$strRoleId);
        
        $roleList = [];
        $roles = Role::query()->select('id', 'name')->whereNotIn('name', [
        User::ROLE_ADMIN,
        User::ROLE_SUPER_ADMIN,
    ])->get();
        foreach ($roles as  $data) { 
            if(in_array($data->id, $shiftHierarchyRoles)) {
                $roleList[] = array(
                    "id" => $data->id,
                    "text" => ucwords($data->name)
                );
            }
        }

        $currencyList = [];
        $currencies = Currency::query()->select('id', 'country_name','currency_name','currency_code','symbol','exchange_rate')
        ->get();
        foreach ($currencies as  $currency) {
            $currencyList[] = array(
                "id" => ucwords($currency->currency_code).'-'.ucwords($currency->symbol),
                "text" => ucwords($currency->currency_code).'-'.ucwords($currency->symbol)
            );
        }
        
        view()->share('activeLink', "setting-$page");
        return view('backend.settings.index', compact('page', 'timezones','shiftHierarchyRoles','roleList','currencyList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function smtp()
    {
        canPerform('Smtp Settings');
        $page = 'smtp';
        view()->share('activeLink', "setting-$page");
        return view('backend.settings.index', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment()
    {
        canPerform('Payment Settings');
        $page = 'payment';
        view()->share('activeLink', "setting-$page");
        return view('backend.settings.index', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function socialLogin()
    {
        canPerform('Social Settings');
        $page = 'social-login';
        view()->share('activeLink', "setting-$page");
        return view('backend.settings.index', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function advance()
    {
        canPerform('Advance Settings');
        $page = 'advance';
        view()->share('activeLink', "setting-$page");
        return view('backend.settings.index', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function systemInfo()
    {
        $page = 'info';
        view()->share('activeLink', "setting-$page");
        return view('backend.settings.index', compact('page'));
    }

    /**
     * Test Added SMTP Settings
     *
     * @return \Illuminate\Http\Response
     */
    public function testSmtp()
    {
        try {
            $email = getSetting('smtp_test_email') ? getSetting('smtp_test_email') : getSetting('site_email');
            $message = "This email is being sent at :" . now();
            $mail = Mail::raw($message, function ($msg) use ($email) {
                $msg->to($email)->subject('Testing Smtp Settings');
            });
            successMessage('Email has been successfully send to your email address ' . $email, false);
        } catch (\Exception $e) {
            Log::error($e);
            errorMessage($e->getMessage(), false);
        }


        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveGeneralSettings(Request $request)
    {
        

        canPerform('General Settings');
        $request->validate([
            'site_title' => 'required|string',
            'logo' => 'nullable|mimes:png,jpg,svg',
            'small_logo' => 'nullable|mimes:png,jpg,svg',
            'site_timezone' => 'timezone'
        ]);

        $response = getErrorResponse();
        try {
            $address = $request->site_address;
    
            $apiKey = env('GOOGLE_MAPS_API_KEY');
            $client = new Client();
            $response = $client->get("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey");
            $data = json_decode($response->getBody());
            if ($data->status === 'OK') {
                $lat = $data->results[0]->geometry->location->lat;
                $lng = $data->results[0]->geometry->location->lng;
                $request['latitude'] = $lat;
                $request['longitude'] = $lng;

                //Dynamic Hierarchy Configurations
                $roleId= $request->get('shift_hierarchy_roles');
                $roldIdString = rtrim(implode(',', $roleId), ',');
                $request['shift_hierarchy_roles'] = $roldIdString;
                
            } 
            // else {
            //     $response = getErrorResponse($message = "Please write google verified address", $error = null);
            //     return response()->json($response); 
            // }
            $this->setAttribute($request);
            $response = getSuccessResponse(createFlashMessage('General Setting', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveSmtpSettings(Request $request)
    {
        canPerform('Smtp Settings');
        $data = $request->validate([
            'smtp_driver' => 'required|string',
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'smtp_sender_name' => 'required|string',
            'smtp_sender_email' => 'required|email',
        ]);
        $response = getErrorResponse();
        try {
            $this->setAttribute($request);
            $response = getSuccessResponse(createFlashMessage('Smtp Setting', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function savePaymentSettings(Request $request)
    {
        canPerform('Payment Settings');
        $request->validate([
            'stripe_key' => 'required|string',
            'stripe_secret' => 'required|string',
            'stripe_currency' => 'required|string',
        ]);
        try {
            $this->setAttribute($request);
            $response = getSuccessResponse(createFlashMessage('Payment Setting', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveSocialLoginSettings(Request $request)
    {
        canPerform('Social Settings');
        $request->validate([
            'social_google_enable' => ['required', new Enum(Status::class)],
            'social_facebook_enable' => ['required', new Enum(Status::class)],
            'google_recaptcha_enable' => ['required', new Enum(Status::class)],
            'google_recaptcha_site_key' => ($request->google_recaptcha_enable == STATUS::Enabled->value) ? 'required' : 'nullable',
            'google_recaptcha_site_secret' => ($request->google_recaptcha_enable == STATUS::Enabled->value) ? 'required' : 'nullable',
            'social_google_id' => ($request->social_google_enable == STATUS::Enabled->value) ? 'required' : 'nullable',
            'social_google_secret' => ($request->social_google_enable == STATUS::Enabled->value) ? 'required' : 'nullable',
            'social_facebook_id' => ($request->social_facebook_enable == STATUS::Enabled->value) ? 'required' : 'nullable',
            'social_facebook_secret' => ($request->social_facebook_enable == STATUS::Enabled->value) ? 'required' : 'nullable',
        ]);

        try {
            $this->setAttribute($request);
            $response = getSuccessResponse(createFlashMessage('Social Setting', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveAdvanceSettings(Request $request)
    {
        canPerform('Advance Settings');
        DB::beginTransaction();
        try {
            $this->setAttribute($request);
            $response = getSuccessResponse(createFlashMessage('Advance Setting', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setAttribute(Request $request)
    {

        foreach ($request->except('_token') as $key => $value) {
            $isFile = false;
            if ($request->hasFile($key)) {
                $value = $this->upload($request->{$key}, 'uploads/assets/');
                $isFile = true;
            }
            Setting::addOrUpdate($key, $value, $isFile);
        }
    }

    public function portalsInfo(Request $request)
    {
        $page = 'portal-management';
        view()->share('activeLink', $page);
        if ($request->ajax()) {
            $data = PortalDetails::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= createActionButton(route('backend.settings.portals.info.edit', $row->id), '', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= '<a href="' .$row->base_url. '" class="btn btn-success" target="_blank"><i class="fas fa-external-link-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('backend.settings.index', compact('page'));
    }

    public function portalInfoStore(Request $request)
    {   
        $data = $request->validate([
            'name' => 'required|string',
            'base_url' => 'required|string',
            'unique_code' => 'required|unique:portal_details,unique_code',
        ]);
        $data['unique_code'] = str_ireplace (' ', '', $request->unique_code);
        try {
            $create = PortalDetails::create($data);
            $response = getSuccessResponse(createFlashMessage('Portal Information', 'created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function portalInfoEdit(PortalDetails $portaldetail)
    {
        canPerform('Edit Portal Details');

        return response()->json([
            'success' => true,
            'html' => view('backend.settings.portal.edit', compact('portaldetail'))->render()
        ]);
    }

    public function portalInfoUpdate(Request $request, PortalDetails $portaldetail)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'base_url' => 'required|string',
            'unique_code' => 'required|unique:portal_details,unique_code,' . $portaldetail->id,
        ]);
        $response = getErrorResponse();
        try {
            $portaldetail->update($data);
            $response = getSuccessResponse(createFlashMessage('Portal Information', 'update'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
