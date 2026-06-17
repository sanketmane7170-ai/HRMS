<?php
namespace Modules\Api\Http\Controllers\Auth;

use App\Models\PortalDetails;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Api\Transformers\UserResource;

/**
 * @group 1. Auth
 */
class LoginController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @bodyParam email email required The email of the user. Example: employee@example.com
     * @bodyParam password password required The password of the user. Example: Welcome2023
     *
     * @response status=422 scenario="Validation error" {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "email": [
     *            "The email field is required."
     *        ],
     *        "password": [
     *            "The password field is required."
     *        ]
     *    }
     * }
     *
     * @response status=200 scenario="Logged In successfully"{
     *    "success": true,
     *    "message": "Logged In Successfully",
     *    "data": {
     *              "token": "33|G9GlmxXd2LKbOmzNFNoB8fK1LXzIPixxGT52aafN",
     *              "user": {
     *                 "id": 3,
     *                 "name": "Ajay Sharma",
     *                  "first_name": "Ajay",
     *                  "last_name": "Sharma",
     *                  "email": "employee@example.com",
     *                  "phone": "641.207.5699",
     *                  "profile_image": "http://127.0.0.1:8000/assets/backend/img/profiles/avatar-01.jpg",
     *                  "department": {
     *                       "id": 1,
     *                       "code": "03122280",
     *                       "name": "IT"
     *                  },
     *                  "designation": {
     *                       "id": 1,
     *                       "code": "98071562",
     *                                  "name": "Manager"
     *                  }
     *              }
     *          }
     *      }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email', 'exists:users,email'],
            'password' => ['required'],
        ]);
        // Property Overriding of request->email for phone
        if ($validator->fails()) {
            $validator = Validator::make($request->all(), [
                'email'    => ['required', 'exists:users,phone'],
                'password' => ['required'],
            ]);

            if ($validator->fails()) {
                $validator = Validator::make($request->all(), [
                    'email'    => ['required', 'exists:users,employee_id'],
                    'password' => ['required'],
                ]);
                if ($validator->fails()) {
                    return response()->error(__trans('the_given_data_was_invalid'), $validator->errors());
                }
            }
        }

        $user = User::with(['department', 'designation'])->where('email', $request->email)->orWhere('phone', $request->email)->orWhere('employee_id', $request->email)->first();
        if ($user->status === 'in-active') { // Assuming 'inactive' is the status indicating deactivation
            return response()->error(__trans('Your account is deactivated. Please contact your company.'), [
                'account' => [__trans('Your account is deactivated. Please contact your company for more information.')],
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->error(__trans('the_credentials_do_not_match_our_records'), [
                'password' => [__trans('please_attempt_a_valid_password')],
            ]);
        }
        $user->tokens()->delete();

        $settings = Setting::select("key", "value")->whereIn('key', ['small_logo', 'site_address', 'attendance_module','timesheet_module'])->get();

        foreach ($settings as $key => $value) {
            if ($value->key == "attendance_module") {
                $attendance_module = $value->value;
            }
            if ($value->key == "timesheet_module") {
                $timesheet_module = $value->value;
            }
        }
        $data = [
            'token'             => $user->createToken($user->email)->plainTextToken,
            'user'              => UserResource::make($user),
            'attendance_module' => isset($attendance_module) ? $attendance_module : "",
            'timesheet_module' => isset($timesheet_module) ? $timesheet_module : "",
        ];
        app()->instance('user_id', $user->id);

        return response()->success(__trans('logged_in_successfully'), $data);
    }

    /**
     * Log the user out of the application.
     *
     * @authenticated
     * @response status=200 scenario="Success" {
     *     "success": true
     *     "message": "Logged out successfully."
     * }
     * @response status=400 scenario="Unauthenticated" {
     *     "message": "Unauthenticated."
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        // Assuming you have the authenticated user instance
        $user = $request->user();

        // Set the ftoken column to null
        //$user->update(['ftoken' => null]);

        return response()->success(__trans('logged_out_successfully'), []);
    }

    // public function getPortalInfo(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'unique_code' => ['required', 'string', 'exists:portal_details,unique_code'],
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->error(__trans('the_given_data_was_invalid'), $validator->errors());
    //     }
    //     app()->instance('unique_code', $request->unique_code);
    //     $data = PortalDetails::where('unique_code', $request->unique_code)->pluck('base_url')->first();

    //     $requiredKeys = [
    //         'site_title',
    //         'site_email',
    //         'site_phone',
    //         'site_address',
    //         'site_support_email',
    //         'site_short_description',
    //         'logo',
    //         'favicon',
    //         'small_logo',
    //         'radius',
    //         'latitude',
    //         'longitude',
    //         'is_check_location_radius',
    //         'shouldPerformLivenessCheck',
    //         'branch_wise_login',
    //         'user_wise_login',
    //         'break_in_out',
    //     ];

    //     $settings = Setting::select('key', 'value')
    //         ->whereIn('key', $requiredKeys)
    //         ->get()
    //         ->pluck('value', 'key')
    //         ->toArray();

    //     // Ensure all required keys exist
    //     foreach ($requiredKeys as $key) {
    //         $data[$key] = $settings[$key] ?? null;
    //     }

    //     return response()->success(__trans('portal_base_url_fetched_successfully'), ['base_url' => $data]);
    // }
    
    public function getPortalInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unique_code' => ['required', 'string', 'exists:portal_details,unique_code'],
        ]);

        if ($validator->fails()) {
            return response()->error(__trans('the_given_data_was_invalid'), $validator->errors());
        }

        app()->instance('unique_code', $request->unique_code);

        // Get base URL
        $baseUrl = PortalDetails::where('unique_code', $request->unique_code)
            ->value('base_url');

        $requiredKeys = [
            'site_title',
            'site_email',
            'site_phone',
            'site_address',
            'site_support_email',
            'site_short_description',
            'logo',
            'favicon',
            'small_logo',
            'radius',
            'latitude',
            'longitude',
            'is_check_location_radius',
            'shouldPerformLivenessCheck',
            'branch_wise_login',
            'user_wise_login',
            'break_in_out',
            'auto_face_scan',
            'auto_face_scan_with_list'
        ];

        $settings = Setting::whereIn('key', $requiredKeys)
            ->pluck('value', 'key')
            ->toArray();

        // Ensure all required keys exist
        $portalSettings = [];
        foreach ($requiredKeys as $key) {
            $portalSettings[$key] = $settings[$key] ?? null;
        }

        return response()->success(
            __trans('portal_base_url_fetched_successfully'),
            [
                'base_url' => $baseUrl,
                'settings' => $portalSettings,
            ]
        );
    }
}
