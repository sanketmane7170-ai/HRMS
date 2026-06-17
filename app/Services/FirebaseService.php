<?php
namespace App\Services;

use App\Models\NotificationData;
use App\Models\User;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $httpClient;
    protected $apiKey;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->apiKey     = env('FIREBASE_SERVER_KEY'); // Replace with your Firebase Server Key
    }

    public function sendFcmMessage($deviceToken, $title, $message, $status_type, $department_id = null)
    {
        Log::info('FirebaseService', ["sendFcmMessage->deviceToken" => $deviceToken]);
        Log::info('FirebaseService', ["sendFcmMessage->title" => $title]);
        Log::info('FirebaseService', ["sendFcmMessage->message" => $message]);
        Log::info('FirebaseService', ["sendFcmMessage->status_type" => $status_type]);
        Log::info('FirebaseService', ["sendFcmMessage->department_id" => $department_id]);

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

        $type = [
            1  => 'information',          //For any info related notification
            2  => 'announcement',         //for Any Type of announcement
            3  => 'warning',              //for Any Type of warning
            4  => 'leave',                //any type of leave related notification
            5  => 'document',             // any type of Document related notification
            6  => 'shift',                //any type of shift related notification'
            7  => 'attendance',           // for Any attendance Related notification
            8  => 'visit',                //for Any visit related notification
                                          // manager acc type
            11 => 'manager_information',  //For any info related notification
            12 => 'manager_announcement', //for Any Type of announcement
            13 => 'manager_warning',      //for Any Type of warning
            14 => 'manager_leave',        //any type of leave related notification
            15 => 'manager_document',     // any type of Document related notification
            16 => 'manager_shift',        //any type of shift related notification'
            17 => 'manager_attendance',   // for Any attendance Related notification
            18 => 'manager_visit',
            19 => 'user_general_request',
            20 => 'user_loan_advance_salary_request',
            21 => 'user_uniform_request',
            22 => 'user_expense_request',
            23 => 'e_sign_document',
            24 => 'general_request_manager',
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];
        Log::info("firebase headers  " . json_encode($headers));

        $returnData = false;
        if ($deviceToken == 'FOR_ALL_USERS') {
            $deviceTokensArray = User::whereNotNull('ftoken')->whereNotIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->pluck('ftoken');
            Log::info('FirebaseService', ["sendFcmMessage->deviceTokensArray" => $deviceTokensArray]);
            if (count($deviceTokensArray) > 0) {
                $returnData = true;
            }
            if (! $returnData) {
                return true;
            }

            Log::info("all department user firebase data  " . json_encode($deviceTokensArray));
            foreach ($deviceTokensArray as $token) {
                $data = [
                    'message' => [
                        'token'        => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $message,
                        ],
                    ],
                ];
                try {
                    $response = $this->httpClient->post($url, [
                        'headers' => $headers,
                        'json'    => $data,
                        'curl'    => [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                        ],
                    ]);
                    $user = User::where('ftoken', $token)->first();
                    if ($user) {
                        // $admin           = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                        $admins = User::withoutGlobalScopes()
                        ->whereHas('roles', function ($q) {
                            $q->whereIn('name', [
                                User::ROLE_ADMIN,
                                User::ROLE_SUPER_ADMIN,
                            ]);
                        })
                        ->get();

                        foreach ($admins as $admin) {
                            if ($admin->ftoken) {
                                $senderId             = $admin->id;
                                $addadminNotification = NotificationData::create([
                                    'sender_id'   => auth()->id() ?: $user->id,
                                    'receiver_id' => $admin->id,
                                    'title'       => $title,
                                    'message'     => $message,
                                    'status'      => 1,
                                    'enum'        => $type[$status_type],
                                    'date'        => Carbon::now()->toDateString(),
                                    'time'        => Carbon::now()->toTimeString(),
                                ]);
                            }
                        }
                        if ($user->ftoken) {
                            $addNotification = NotificationData::create([
                                'sender_id'   => auth()->id() ?: $user->id,
                                'receiver_id' => $user->id,
                                'title'       => $title,
                                'message'     => $message,
                                'status'      => 1,
                                'enum'        => $type[$status_type],
                                'date'        => Carbon::now()->toDateString(),
                                'time'        => Carbon::now()->toTimeString(),
                            ]);
                        }

                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    User::where('ftoken', $token)->update(['ftoken' => null]);
                    Log::info("Error sending notification to token " . $token . ": " . $error);
                }
            }
        } else if ($deviceToken == 'FOR_DEPARTMENT_USERS') {
            $deviceTokensArray = User::whereNotNull('ftoken')->where('department_id', $department_id)->pluck('ftoken');
            Log::info('FirebaseService', ["sendFcmMessage->deviceTokensArray" => $deviceTokensArray]);
            if (count($deviceTokensArray) > 0) {
                $returnData = true;
            }
            if (! $returnData) {
                return true;
            }

            Log::info("all department user firebase data  " . json_encode($deviceTokensArray));
            foreach ($deviceTokensArray as $token) {
                $data = [
                    'message' => [
                        'token'        => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $message,
                        ],
                        'data'         => ["enum" => $type[$status_type]],
                    ],
                ];

                try {
                    $response = $this->httpClient->post($url, [
                        'headers' => $headers,
                        'json'    => $data,
                        'curl'    => [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                        ],
                    ]);
                    $user = User::where('ftoken', $token)->first();
                    if ($user) {
                        // $admin           = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                        $admins = User::withoutGlobalScopes()
                        ->whereHas('roles', function ($q) {
                            $q->whereIn('name', [
                                User::ROLE_ADMIN,
                                User::ROLE_SUPER_ADMIN,
                            ]);
                        })
                        ->get();

                        foreach ($admins as $admin) {
                            if ($admin->ftoken) {
                                $senderId             = $admin->id;
                                $addadminNotification = NotificationData::create([
                                    'sender_id'   => auth()->id() ?: $user->id,
                                    'receiver_id' => $admin->id,
                                    'title'       => $title,
                                    'message'     => $message,
                                    'status'      => 1,
                                    'enum'        => $type[$status_type],
                                    'date'        => Carbon::now()->toDateString(),
                                    'time'        => Carbon::now()->toTimeString(),
                                ]);
                            }
                        }
                        if ($user->ftoken) {
                            $addNotification = NotificationData::create([
                                'sender_id'   => auth()->id() ?: $user->id,
                                'receiver_id' => $user->id,
                                'title'       => $title,
                                'message'     => $message,
                                'status'      => 1,
                                'enum'        => $type[$status_type],
                                'date'        => Carbon::now()->toDateString(),
                                'time'        => Carbon::now()->toTimeString(),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    User::where('ftoken', $token)->update(['ftoken' => null]);
                    Log::info("Error sending notification to token " . $token . ": " . $error);
                }
            }
        } else {
            if ($deviceToken) {
                $returnData = true;
                Log::info('FirebaseService', ["sendFcmMessage->returnData" => $returnData]);
                $data = [
                    'message' => [
                        'token'        => $deviceToken,
                        'notification' => [
                            'title' => $title,
                            'body'  => $message,
                        ],
                        'data'         => ["enum" => $type[$status_type]],
                    ],

                ];
                Log::info('FirebaseService', ["sendFcmMessage->data" => $data]);
            }

            if (! $returnData) {
                Log::info('FirebaseService', ["sendFcmMessage->not-returnData" => $returnData]);

                return true;
            }
            try {
                $response = $this->httpClient->post($url, [
                    'headers' => $headers,
                    'json'    => $data,
                    'curl'    => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ],
                ]);
                $user = User::where('ftoken', $deviceToken)->first();
                if ($user) {
                    // $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();
                    $admins = User::withoutGlobalScopes()
                        ->whereHas('roles', function ($q) {
                            $q->whereIn('name', [
                                User::ROLE_ADMIN,
                                User::ROLE_SUPER_ADMIN,
                            ]);
                        })
                        ->get();

                    foreach ($admins as $admin) {
                        if ($admin->ftoken) {
                            $senderId             = $admin->id;
                            $addadminNotification = NotificationData::create([
                                'sender_id'   => auth()->id() ?: $user->id,
                                'receiver_id' => $admin->id,
                                'title'       => $title,
                                'message'     => $message,
                                'status'      => 1,
                                'enum'        => $type[$status_type],
                                'date'        => Carbon::now()->toDateString(),
                                'time'        => Carbon::now()->toTimeString(),
                            ]);
                        }
                    }
                    if ($user->ftoken) {
                        $addNotification = NotificationData::create([
                            'sender_id'   => auth()->id() ?: $user->id,
                            'receiver_id' => $user->id,
                            'title'       => $title,
                            'message'     => $message,
                            'status'      => 1,
                            'enum'        => $type[$status_type],
                            'date'        => Carbon::now()->toDateString(),
                            'time'        => Carbon::now()->toTimeString(),
                        ]);
                    }
                }
                Log::info("firebase response  " . json_encode($response));
            } catch (\Exception $e) {
                $error = $e->getMessage();
                Log::info("Error  " . $error);
                return false;
            }
        }

        Log::info("FCM LOG  " . json_encode($data));

        return $response->getBody()->getContents();
    }
}
