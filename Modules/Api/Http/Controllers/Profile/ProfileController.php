<?php
namespace Modules\Api\Http\Controllers\Profile;

use App\Models\Country;
use App\Models\Department;
use App\Models\NotificationData;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\FirebaseService;
use App\Traits\File;
use Exception;
use Google\Client as GoogleClient;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Attendance\Entities\Visitin;
use Modules\Attendance\Enums\BreakinType;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Enums\VisitinType;

class ProfileController extends Controller
{
    // Use Traits Functions
    use File;
    /**
     * Display a listing of the resource.
     * @return Response
     */

    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index()
    {
        $user                                         = User::with(['profile', 'workDetail', 'designation', 'department', 'division'])->where('id', auth()->id())->first();
        $defaultImage                                 = asset('assets/backend/img/profiles/avatar-01.jpg');
        ($user->profile_image) ? $user->profile_image = asset($user->profile_image) : $user->profile_image = $defaultImage;
        $report_user                                  = 'Not Assigned';
        if (isset($user->workDetail) && ! empty($user->workDetail->report_to_ids)) {
            $reportToIds = $user->workDetail->report_to_ids;
            $reportUsers = User::whereIn('id', $reportToIds)->pluck('name');
            $report_user = $reportUsers->implode(', ');
        }
        // if(isset($user->workDetail) && $user->workDetail->report_to_id != 0){
        //     $report_user = User::find($user->workDetail->report_to_id)->name;
        // }
        // $settings = Setting::select("key","value")->whereIn('key', ['logo', 'small_logo','favicon','site_title','site_email'])->get();
        $settings     = Setting::select("key", "value")->whereIn('key', ['small_logo', 'site_address', 'attendance_module', 'timesheet_module','site_title'])->get();
        $site_address = "";
        $logo         = "";
        foreach ($settings as $key => $value) {
            if ($value->key == "site_address") {
                $site_address = $value->value;
            } else if ($value->key == "small_logo") {
                $logo = $value->value;
            } else if ($value->key == "attendance_module") {
                $attendance_module = $value->value;
            } else if ($value->key == "site_title") {
                $site_title = $value->value;
            } else if ($value->key == "timesheet_module") {
                $timesheet_module = $value->value;
            }
            
        }
        $role                    = $user->getCurrentRole();
        $notificationListCount   = auth()->user()->unreadNotifications->count();
        $dbnotificationListCount = NotificationData::where('status', 1)
            ->where('receiver_id', auth()->id())
            ->count();

        // $country_name = '';
        // if(isset($user->profile->country_id)){
        //     $country_name = Country::select('name')->where('id',$user->profile->country_id)->pluck('name')->first();
        // }

        $collect = [
            'id'                        => $user->id,
            'name'                      => $user->name,
            'email'                     => $user->email,
            'phone'                     => $user->phone,
            'profile_image'             => $user->profile_image,
            'employee_id'               => $user->employee_id,
            'device_info'               => $user->device_info,
            'version_info'              => $user->version_info,
            'attendance_module'         => isset($attendance_module) ? $attendance_module : "",
            'timesheet_module'         => isset($timesheet_module) ? $timesheet_module : "",
            'role'                      => $role ? $role->name : 'No Role',
            'site_title'         => isset($site_title) ? $site_title : "",
            'unReadNotificationCount'   => $notificationListCount,
            'dbunReadNotificationCount' => $dbnotificationListCount,
            'personal_details'          => [
                'gender'         => isset($user->profile) ? $user->profile->gender : "",
                'personal_email' => isset($user->profile) ? $user->profile->personal_email : "",
                'personal_phone' => isset($user->profile) ? $user->profile->personal_phone : "",
                'visa_category' => isset($user->profile) ? $user->profile->visa_category : "",
                'date_of_birth'  => isset($user->profile) ? $user->profile->date_of_birth->toDateString() : "",
                // 'nationality' => $country_name,
                'country_id'     => isset($user->profile) ? $user->profile->country_id : "",
                'martial_status' => isset($user->profile) ? $user->profile->martial_status : "",
                'address'        => isset($user->profile) ? $user->profile->address : "",
            ],
            'social_details'            => [
                'linkedin_url' => isset($user->profile) ? $user->profile->linkedin_url : "",
                'skills'       => isset($user->profile) ? $user->profile->skills : "",
                'hobbies'      => isset($user->profile) ? $user->profile->hobbies : "",
            ],
            'work_details'              => [
                'company_name'       => isset($user->workDetail) ? $user->workDetail->company_name : "",
                'department'         => $user->department?->name ?? 'NA',
                'division'           => isset($user->division) ? $user->division?->name : "",
                'designation'        => $user->designation->name ?? "-",
                'date_of_joining'    => isset($user->workDetail) ? $user->workDetail?->joining_date->toDateString() : "",
                'probation_month'    => isset($user->workDetail) ? $user->workDetail->probation_month : "",
                'probation_end_date' => isset($user->workDetail) ? $user->workDetail->probation_end_date->toDateString() : "",
                'work_week'          => isset($user->workDetail) ? $user->workDetail->work_week : "",
                'location'           => isset($user->workDetail) ? $user->workDetail->location : "",
                'shift_start'        => isset($user->workDetail) ? $user->workDetail->shift_start : "",
                'shift_end'          => isset($user->workDetail) ? $user->workDetail->shift_end : "",
                'report_to'          => $report_user,
                'logo'               => isset($logo) ? $logo : "",
                'site_address'       => isset($site_address) ? $site_address : "",
                'base_url'           => url('/'),
                'is_rider'           => isset($user->workDetail) ? $user->workDetail->is_rider : "0",

            ],
            'app_details'               => [
                'ANDROID_LIVE_APK_VERSION' => "1.2.5(51)",
                'ANDROID_LIVE_APP_LINK'    => "market://details?id=com.employee.mom",
                'IOS_LIVE_APP_VERSION'     => "1.2.5",
                'IOS_LIVE_APP_LINK'        => "https://apps.apple.com/us/app/mom-digital/id6463124736",
            ],
        ];
        $collected = collect($collect);

        return response()->success(__trans('profile_data_fetched_successfully'), $collected);
    }

    public function userdepartment()
    {
        $data = User::with(['department'])->where('id', auth()->id())->first();
        //    dd($data->department);
        return response()->success(__trans('user_department_fetched_successfully'), $data->department);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    public function updateprofileimage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => ['nullable', 'mimes:jpg,jpeg,png'],
        ]);

        if ($validator->fails()) {
            return response()->error(__trans('validation_failed'), $validator->errors());
        }
        try {
            $data            = $validator->validated();
            $data['user_id'] = auth()->id();
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $this->upload($request->profile_image, '/uploads/profile', auth()->user()->profile_image);
            }
            auth()->user()->update($data);
            return response()->success(createFlashMessage('Photo', 'updated'));
        } catch (Exception $e) {
            return response()->error($e->getMessage());
        }
    }

    public function deleteprofileimage()
    {
        $profile_image = auth()->user()->profile_image;
        try {
            if (file_exists(public_path($profile_image))) {
                @unlink(public_path($profile_image));
                $data['profile_image'] = '';
                auth()->user()->update($data);
                return response()->success(createFlashMessage('Photo', 'removed'));
            } else {
                $data['profile_image'] = '';
                auth()->user()->update($data);
                return response()->success(createFlashMessage('Photo', 'removed'));
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage());
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $update_type)
    {
        /**
         * Update specific type Using PATCH Method.
         */
        if ($update_type == 'personal-details') {
            $validator = Validator::make($request->all(), [
                // 'name' => ['nullable', 'string', 'min:5'],
                'gender'         => ['required', 'string'],
                'personal_email' => ['required', 'email'],
                'personal_phone' => ['nullable', 'string'],
                'date_of_birth'  => ['required', 'date'],
                'country_id'     => ['required'],
                'martial_status' => ['required', 'string'],
                'address'        => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->error(__trans('validation_failed'), $validator->errors());
            }

            try {
                $data = $validator->validated();
                UserProfile::where('user_id', auth()->id())->update($request->all());
                return response()->success(createFlashMessage('Personal Details', 'updated'));
            } catch (Exception $e) {
                return response()->error($e->getMessage());
            }

            return response()->error(config('project.error_message'));
        } else if ($update_type == 'social-details') {
            $validator = Validator::make($request->all(), [
                // 'name' => ['nullable', 'string', 'min:5'],
                'linkedin_url' => ['nullable', 'string'],
                'skills'       => ['nullable', 'string'],
                'hobbies'      => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->error(__trans('validation_failed'), $validator->errors());
            }

            try {
                $data = $validator->validated();
                UserProfile::where('user_id', auth()->id())->update($request->all());
                return response()->success(createFlashMessage('Social Details', 'updated'));
            } catch (Exception $e) {
                return response()->error($e->getMessage());
            }
        } else {
            return response()->error(config('project.error_message'));
        }
    }

    public function profileUpdate(Request $request)
    {
        if ($request->input(['current_password'])) {
            $password_validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password'     => ['required', 'min:8'],
                'confirm_password' => ['required', 'same:new_password'],
            ], [
                'current_password' => 'Your current password  didn\'t matched with password in our recordsd',
            ]);
            if ($password_validator->fails()) {
                return response()->error(__trans('validation_failed'), $password_validator->errors());
            }

            $profile_data_validator = Validator::make($request->all(), [
                'name'  => ['nullable', 'string', 'min:5'],
                'email' => ['required', 'email', Rule::unique('users')->ignore(auth()->id())],
                'phone' => ['nullable', 'string', 'min:10'],
            ]);

            if ($profile_data_validator->fails()) {
                return response()->error(__trans('validation_failed'), $profile_data_validator->errors());
            }

            $user = auth()->user();

            try {
                if (! Hash::check($request->current_password, $user->password)) {
                    return response()->error(__trans('validation_failed'), $password_validator->customMessages);
                } else {
                    $user->password = Hash::make($request->new_password);
                }
                $data = $profile_data_validator->validated();
                auth()->user()->update($data);
                $user->save();
                return response()->success(createFlashMessage('Profile', 'updated'));
            } catch (Exception $e) {
                return response()->error($e->getMessage());
            }
            return response()->error(config('project.error_message'));
        } else {
            $profile_data_validator = Validator::make($request->all(), [
                'name'  => ['nullable', 'string', 'min:5'],
                'email' => ['required', 'email', Rule::unique('users')->ignore(auth()->id())],
                'phone' => ['nullable', 'string', 'min:10'],
            ]);

            if ($profile_data_validator->fails()) {
                return response()->error(__trans('validation_failed'), $profile_data_validator->errors());
            }

            try {
                $data = $profile_data_validator->validated();
                auth()->user()->update($data);
                return response()->success(createFlashMessage('Profile', 'updated'));
            } catch (Exception $e) {
                return response()->error($e->getMessage());
            }

            return response()->error(config('project.error_message'));
        }
    }

    /**
     * Get Country List for Nationality in Personal Details.
     * @return Response
     */
    public function countrylist()
    {
        // ->keyBy('id')
        $data = Country::get(['id', 'name']);
        $list = collect($data);
        return response()->success(__trans('country_list_fetched_successfully'), $list);
    }

    /**
     * Get Country List for Nationality in Personal Details.
     * @return Response
     */
    public function updateuserlocation(Request $request)
    {
        $breakinExist = Breakin::my()->where([
            'date' => now()->toDateString(),
            'type' => BreakinType::IN,
        ])->exists();

        if ($breakinExist) {
            Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["break" => "started"]);

            return response()->success(createFlashMessage('break', 'started'));
        }
        if (getSetting('auto_clockout') == 'true') {

            Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["start_time" => now()]);
            Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["request" => $request]);
            $validator = Validator::make($request->all(), [
                'longitude' => ['required', 'string'],
                'latitude'  => ['required', 'string'],
            ]);
            Log::info('updateuserlocation-user_id-' . auth()->id(), ["request" => $request->all()]);
            if ($validator->fails()) {
                return response()->error(__trans('validation_failed'), $validator->errors());
            }

            try {
                $data = $validator->validated();
                Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["data" => $data]);

                // $data['user_id'] = auth()->id();
                $updated = auth()->user()->update($data);
                Log::info('updateuserlocation-user_id-' . auth()->id(), ["updated" => $updated]);

                $user = User::select(
                    'id',
                    'longitude as user_longitude',
                    'latitude as user_latitude',
                    'updated_at',
                    'status',
                    'department_id'
                )->where("id", auth()->user()->id)->first();

                $location_parameter = User::select('department_id', 'longitude as user_longitude', 'latitude as user_latitude')->where('id', auth()->user()->id)->first();
                Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["location_parameter" => $location_parameter]);

                Log::info('updateuserlocation-user_id-' . $user->id, ["user" => $user]);

                $user_lat = $user->user_latitude;
                $user_lng = $user->user_longitude;
                $unit     = "M"; //M = miles
                                 // $department_id = User::where('id', $user->id)->pluck('department_id')->first();
                                 // $branch = Department::where('id', $department_id)->first();
                                 // $branch_id = User::where('id', $user->id)->pluck('branch_id')->first();

                // $branch = Branch::where('id', $user->clock_in_branch_id)->first();
                // Log::info('updateuserlocation-user_id-' . $user->id, array("branch_by_clock_in_branch_id" => $branch));

                // if (empty($branch)) {
                //     $branch = Branch::where('id', $user->branch_id)->first();
                //     Log::info('updateuserlocation-user_id-' . $user->id, array("branch_by_branch_id" => $branch));
                // }
                $department = Department::where('id', $user->department_id)->first();
                Log::info('updateuserlocation-user_id-' . $user->id, ["department" => $department]);
                if ($department) {

                    // $getdistance = $this->distanceInMeters(floatval($department->latitude), floatval($department->longitude), floatval($user_lat), floatval($user_lng));
                    $getdistance       = $this->distanceInMeters2(floatval($department->latitude), floatval($department->longitude), floatval($user_lat), floatval($user_lng));
                    $getdistance       = str_replace(',', '', $getdistance);
                    $getdistance_float = (float) $getdistance;
                    Log::info('updateuserlocation-user_id-' . $user->id, ["getdistance_float" => $getdistance_float]);
                    $radius = $department->login_radius;
                    Log::info('updateuserlocation-user_id-' . $user->id, ["department_radius" => $department->login_radius]);
                    $record = Checkin::where([
                        // 'date' => now()->toDateString(),
                        'user_id' => $user->id,
                    ])->orderByDesc('id')->limit(1)->first();

                    Log::info('updateuserlocation-user_id-' . $user->id, ["check_in_record" => $record]);

                    $lastUpdatedTime = $user->updated_at;
                    /* Auto-Logout if User Account Deactivated From Database */
                    if ($user->status === 'in-active') {
                        $user->tokens()->delete();
                    }
                    $is_rider = isset($user->workDetail) ? $user->workDetail->is_rider : 0;
                    Log::info('updateuserlocation-user_id-' . $user->id, ["is_rider" => $is_rider]);

                    $userId = $user->id;
                    $key    = "user:location:window:{$userId}";
                    Log::info('updateuserlocation-user_id-' . $user->id, ["key" => $key]);

                    // build reading payload
                    $reading = [
                        'distance'  => (float) $getdistance_float,
                        'latitude'  => $user_lat,
                        'longitude' => $user_lng,
                        'at'        => now()->toDateTimeString(),
                    ];
                    Log::info('updateuserlocation-user_id-' . $user->id, ["reading" => $reading]);

                    // get window (array) from cache
                    $window = Cache::get($key, []);

                    // append and keep only last 3
                    $window[] = $reading;

                    if (count($window) > 3) {
                        array_shift($window);
                    }
                    Log::info('updateuserlocation-user_id-' . $user->id, ["window" => $window]);

                    // save back to cache with TTL (adjust TTL as required)
                    $ttlMinutes = 10;
                    Cache::put($key, $window, now()->addMinutes($ttlMinutes));

                    Log::info("updateuserlocation-user_id-{$userId}", ['cache_window' => $window]);

                    // check 3 consecutive -> trigger only if exactly 3 entries and all > radius
                    $allExceeded = false;
                    Log::info('updateuserlocation-user_id-' . $user->id, ["allExceeded" => $allExceeded]);

                    if (count($window) === 5 && $is_rider == 0) {
                        $allExceeded = true;
                        Log::info('updateuserlocation-user_id-' . $user->id, ["allExceeded" => $allExceeded]);

                        foreach ($window as $r) {
                            Log::info('updateuserlocation-user_id-' . $user->id, ["r" => $r]);

                            if ((float) $r['distance'] <= (float) $radius) {
                                $allExceeded = false;
                                Log::info('updateuserlocation-user_id-' . $user->id, ["allExceeded" => $allExceeded]);

                                break;
                            }
                        }
                    }

                    if ($allExceeded && ($getdistance_float > $radius && $is_rider == 0)) {
                        Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["start_clock_out_time" => now()]);

                        $type = CheckinType::IN;
                        if ($record) {
                            Log::info('updateuserlocation-user_id-' . $user->id, ["record" => $record]);
                            if ($record->type == CheckinType::IN->value && $record->face_attendance == 0) {
                                $type = CheckinType::OUT;
                                Log::info('updateuserlocation-user_id-' . $user->id, ["type" => $type]);
                                $userId  = auth()->id(); // or from $request->user_id
                                $lockKey = 'user-clock-out-lock-' . $userId;
                                $lock    = Cache::lock($lockKey, 5); // Lock for 5 seconds max
                                if ($lock->get()) {
                                    try {
                                        $checkin = Checkin::firstOrCreate([
                                            'user_id'         => $user->id,
                                            'date'            => now()->toDateString(),
                                            'time'            => date('H:i:s'),
                                            'type'            => $type,
                                            'latecomment'     => 'AUTO_RADIUSOUT-3',
                                            'checkout_reason' => 'OUT OF RADIUS',
                                            'is_auto_update'  => 1,
                                        ]);
                                        Log::info('updateuserlocation-user_id-' . $user->id, ["checkin" => $checkin]);
                                        Log::info('updateuserlocation-user_id-' . $user->id, ["FIREBASE_SERVER_KEY" => env("FIREBASE_SERVER_KEY")]);
                                        Log::info('updateuserlocation-user_id-' . $user->id, ["FIREBASE" => "FIREBASE_Notification_start"]);

                                        // if (auth()->user()->ftoken != null) {
                                        //     $serviceAccountFile = base_path('mom-digital-eb91a-a5f9fd0b40b5.json');
                                        //     // Initialize the Google client
                                        //     $client = new GoogleClient();
                                        //     // Set the service account credentials
                                        //     $client->setAuthConfig($serviceAccountFile);
                                        //     // Set the scope to Firebase Cloud Messaging
                                        //     $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                                        //     // Get the access token
                                        //     $accessTokenArray = $client->fetchAccessTokenWithAssertion();
                                        //     // Return the access token
                                        //     $accessToken = $accessTokenArray['access_token'] ?? null;

                                        //     $url = 'https://fcm.googleapis.com/v1/projects/mom-digital-eb91a/messages:send';

                                        //     $headers = [
                                        //         'Authorization' => 'Bearer ' . $accessToken,
                                        //         'Content-Type'  => 'application/json',
                                        //     ];

                                        //     $data = [
                                        //         'message' => [
                                        //             'token'        => auth()->user()->ftoken,
                                        //             'notification' => [
                                        //                 'title' => 'Information',
                                        //                 'body'  => "You are now outside of Login radius from . $department->name",
                                        //             ],
                                        //             'data'         => ["enum" => "Information"],
                                        //         ],

                                        //     ];
                                        //     try {
                                        //         $httpClient = new Client();
                                        //         $response   = $httpClient->post($url, [
                                        //             'headers' => $headers,
                                        //             'json'    => $data,
                                        //             'curl'    => [
                                        //                 CURLOPT_SSL_VERIFYPEER => false,
                                        //                 CURLOPT_SSL_VERIFYHOST => false,
                                        //             ],
                                        //         ]);
                                        //         //\Log::info("firebase response  " . json_encode($response));
                                        //     } catch (\Exception $e) {
                                        //         \Log::error("An error occurred for user ID {auth()->user()->id}: " . $e->getMessage());
                                        //     }
                                        // }
                                        if(auth()->user()->ftoken != null){
                                          $get = $this->fcmService->sendFcmMessage(auth()->user()->ftoken, 'Information', "You are now outside of Login radius from . $department->name", 1);
                                        }

                                        Log::info('updateuserlocation-user_id-' . $user->id, ["FIREBASE" => "FIREBASE_Notification_end"]);

                                        // $user->tokens()->delete();
                                        Log::info('updateuserlocation-user_id-' . $user->id, ["clock-in-code-stop" => "logged out successfully and access tokens revoked."]);
                                        //$this->info("User {$user->id} logged out successfully and access tokens revoked.");
                                        return response()->success(createFlashMessage('Location', 'updated'));
                                    } finally {
                                        $lock->release(); // 🔥 Always release lock!
                                    }
                                } else {
                                    Log::info('updateuserlocation-user_id-' . $user->id, ["clock-message" => "Multiple requests detected. Please try again."]);
                                    return response()->json(['status' => false, 'message' => 'Multiple requests detected. Please try again.'], 429);
                                }
                            }
                        }
                        Log::info('updateuserlocation-user_id-' . $user->id, ["clock-in-code-stop" => "logged out successfully and access tokens revoked."]);
                        Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["stop_clock_out_time" => now()]);
                        //    $this->info("{$user->id} Out Side Record Condition.");
                    }
                }
                Log::info('updateuserlocation-user_id-' . $user->id, ["visit-stop-code-start" => "started"]);
                $visit_type = VisitinType::IN;
                Log::info('updateuserlocation-user_id-' . $user->id, ["visit-visit_type" => $visit_type]);
                $visit_record = Visitin::where([
                    'user_id' => auth()->id(),
                ])->orderByDesc('id')->limit(1)->first();
                Log::info('updateuserlocation', ["Visit-record" => $visit_record]);
                Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["start_visit_out_time" => now()]);
                if ($visit_record) {
                    if ($visit_record->type == VisitinType::IN->value) {
                        $visit_radius = 0;
                        // $visit_radius_settings = Setting::select('key', 'value')->whereIn('key', [
                        //     'radius',
                        // ])->first();
                        // if (!empty($visit_radius_settings)) {
                        //     $visit_radius = $visit_radius_settings->value;
                        //     Log::info('updateuserlocation-user_id-' . auth()->id(), array("visit_radius" => $visit_radius));
                        // }
                        $visit_radius_settings = Setting::where('key', 'radius')->first();

                        if ($visit_radius_settings !== null) {
                            $visit_radius = $visit_radius_settings->value;
                            Log::info('updateuserlocation-user_id-' . (auth()->check() ? auth()->id() : 'guest'), [
                                "visit_radius" => $visit_radius,
                            ]);
                        }

                        $visit_location_id = $visit_record->location_id;
                        Log::info('updateuserlocation-user_id-' . auth()->id(), ["visit_location_id" => $visit_location_id]);
                        if ($visit_location_id != 0) {
                            $locationvisitin = LocationVisits::where('id', $visit_location_id)->first();
                            Log::info('updateuserlocation-user_id-' . auth()->id(), ["locationvisitin" => $locationvisitin]);

                            if ($locationvisitin) {
                                $visit_latitude  = $locationvisitin->latitude;
                                $visit_longitude = $locationvisitin->longitude;
                                Log::info('updateuserlocation-user_id-' . auth()->id(), ["visit_latitude" => $visit_latitude]);
                                Log::info('updateuserlocation-user_id-' . auth()->id(), ["visit_longitude" => $visit_longitude]);
                                $user_lat = floatval($location_parameter->user_latitude);
                                $user_lng = floatval($location_parameter->user_longitude);
                                Log::info('updateuserlocation-user_id-' . auth()->id(), ["user_lat" => $user_lat]);
                                Log::info('updateuserlocation-user_id-' . auth()->id(), ["user_lng" => $user_lng]);
                                $getvisitdistance = $this->distanceInMeters2($visit_latitude, $visit_longitude, $user_lat, $user_lng);
                                Log::info('updateuserlocation-user_id-' . auth()->id(), ["getvisitdistance" => $getvisitdistance]);
                                $getvisitdistance       = str_replace(',', '', $getvisitdistance);
                                $getvisitdistance_float = (float) $getvisitdistance;
                                Log::info('updateuserlocation-user_id-' . auth()->id(), ["getvisitdistance_float" => $getvisitdistance_float]);
                                $visit_type = VisitinType::OUT;
                                if ($getvisitdistance_float > $visit_radius && $visit_radius > 0 && $getvisitdistance_float > 0) {
                                    $locationvisit = LocationVisits::where('id', $visit_location_id)->update(['visit_out' => date('H:i:s'), 'status' => 1]);
                                    Log::info('updateuserlocation', ["Visit-locationvisit" => $locationvisit]);

                                    $userId  = auth()->id(); // or from $request->user_id
                                    $lockKey = 'user-visit-out-lock-' . $userId;
                                    $lock    = Cache::lock($lockKey, 5); // Lock for 5 seconds max
                                    if ($lock->get()) {
                                        try {
                                            $visitin = Visitin::firstOrCreate([
                                                'user_id'     => auth()->id(),
                                                'date'        => now()->toDateString(),
                                                'time'        => date('H:i:s'),
                                                'type'        => $visit_type,
                                                'location_id' => $visit_location_id,
                                            ]);
                                            Log::info('updateuserlocation', ["Visit-visitin" => $visitin]);
                                            if ($visit_type == VisitinType::OUT) {

                                                if (auth()->user()->ftoken != null) {
                                                    $serviceAccountFile = base_path('mom-digital-eb91a-a5f9fd0b40b5.json');
                                                    // Initialize the Google client
                                                    $client = new GoogleClient();
                                                    // Set the service account credentials
                                                    $client->setAuthConfig($serviceAccountFile);
                                                    // Set the scope to Firebase Cloud Messaging
                                                    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                                                    // Get the access token
                                                    $accessTokenArray = $client->fetchAccessTokenWithAssertion();
                                                    // Return the access token
                                                    $accessToken = $accessTokenArray['access_token'] ?? null;

                                                    $url = 'https://fcm.googleapis.com/v1/projects/mom-digital-eb91a/messages:send';

                                                    $headers = [
                                                        'Authorization' => 'Bearer ' . $accessToken,
                                                        'Content-Type'  => 'application/json',
                                                    ];

                                                    $data = [
                                                        'message' => [
                                                            'token'        => auth()->user()->ftoken,
                                                            'notification' => [
                                                                'title' => 'Information',
                                                                'body'  => "You are now outside of Visit radius, Your visit is now stopped",
                                                            ],
                                                            'data'         => ["enum" => "Information"],
                                                        ],

                                                    ];
                                                    try {
                                                        $httpClient = new Client();
                                                        $response   = $httpClient->post($url, [
                                                            'headers' => $headers,
                                                            'json'    => $data,
                                                            'curl'    => [
                                                                CURLOPT_SSL_VERIFYPEER => false,
                                                                CURLOPT_SSL_VERIFYHOST => false,
                                                            ],
                                                        ]);
                                                        //\Log::info("firebase response  " . json_encode($response));
                                                    } catch (\Exception $e) {
                                                        \Log::error("An error occurred for user ID {auth()->user()->id}: " . $e->getMessage());
                                                    }
                                                }
                                            }
                                            return response()->success(createFlashMessage('Location', 'updated'));
                                        } finally {
                                            $lock->release(); // 🔥 Always release lock!
                                        }
                                    } else {
                                        Log::info('updateuserlocation-user_id-' . $user->id, ["visit-message" => "Multiple requests detected. Please try again."]);
                                        return response()->json(['status' => false, 'message' => 'Multiple requests detected. Please try again.'], 429);
                                    }
                                }
                            }
                        }
                    }
                }
                Log::info('updateuserlocation-user_id-' . $user->id, ["visit-stop-code-stop" => "ended"]);
                Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["stop_visit_out_time" => now()]);
                return response()->success(createFlashMessage('Location', 'updated'));
            } catch (Exception $e) {
                return response()->error($e->getMessage());
            }
            Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["end_time" => now()]);
            return false;
        } else {
            Log::info('updateuserlocation-user_id-' . auth()->user()->id, ["auto_clockout" => "disabled"]);

            return response()->success(createFlashMessage('auto_clockout', 'disabled'));
        }
    }

    /**
     * Send Firebase Push Notification
     */

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function storeFToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ftoken' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->error(__trans('validation_failed'), $validator->errors());
        }

        try {
            $data            = $validator->validated();
            $data['user_id'] = auth()->id();
            auth()->user()->update($data);
            return response()->success(createFlashMessage('F-Token', 'updated'));
        } catch (Exception $e) {
            return response()->error($e->getMessage());
        }
    }

    public function UserAllottedShiftList()
    {
        $user_id    = auth()->id();
        $user       = User::find($user_id);
        $userShifts = $user->assigned_shifts()
            ->whereDate('assigned_for_date', '>=', now())
            ->whereDate('assigned_for_date', '<=', now()->addDay(30))
            ->with('shift_schedule_information')
            ->orderBy('assigned_for_date', 'asc')
            ->get();

        $formattedShifts = [];

        foreach ($userShifts as $shift) {
            $assignedForDate = $shift['assigned_for_date'];
            $shiftStart      = $shift['shift_schedule_information']['shift_start'];
            $shiftEnd        = $shift['shift_schedule_information']['shift_end'];
            $shiftTitle      = $shift['shift_schedule_information']['title'];

            if (isset($formattedShifts[$assignedForDate])) {
                $formattedShifts[$assignedForDate][] = [
                    'title'       => $shiftTitle,
                    'shift_start' => $shiftStart,
                    'shift_end'   => $shiftEnd,
                ];
            } else {
                // If not, create a new entry for the date
                $formattedShifts[$assignedForDate] = [
                    [
                        'title'       => $shiftTitle,
                        'shift_start' => $shiftStart,
                        'shift_end'   => $shiftEnd,
                    ],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => __trans('shift_data_loaded_successfully'),
            'data'    => $formattedShifts,
        ]);
    }

    public function apk_info(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_info'  => ['required'],
            'version_info' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->error(__trans('validation_failed'), $validator->errors());
        }

        try {
            $data            = $validator->validated();
            $data['user_id'] = auth()->id();
            auth()->user()->update($data);
            return response()->success(createFlashMessage('apk_info', 'updated'));
        } catch (Exception $e) {
            return response()->error($e->getMessage());
        }
    }
    public function haversine($lat1, $lon1, $lat2, $lon2)
    {
        // Convert latitude and longitude from degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Haversine formula
        $dlat     = $lat2 - $lat1;
        $dlon     = $lon2 - $lon1;
        $a        = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c        = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = 6371 * $c; // Radius of Earth in kilometers

        return $distance;
    }
    public function distanceInMeters2($company_lat, $company_lon, $user_lat, $user_lon)
    {
        return number_format($this->haversine($company_lat, $company_lon, $user_lat, $user_lon) * 1000, 2);
    }
}