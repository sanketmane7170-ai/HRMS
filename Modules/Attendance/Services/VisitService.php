<?php

namespace Modules\Attendance\Services;

use Exception;
use Modules\Attendance\Entities\Visitin;
use Modules\Attendance\Enums\VisitinType;
use Modules\Attendance\Entities\LocationVisits;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseService;
use GuzzleHttp\Client;
use Google\Client as GoogleClient;


class VisitService
{

    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    public function performVisitInVisitOut(array $requestData = []): Visitin
    {
        try {
            if (config('attendance.multi_visitins_allowed')) {
                if (!empty($requestData) && isset($requestData['visit_purpose'])) {
                    $visitin = $this->multiVisitInVisitOut($requestData);
                    Log::info('performVisitInVisitOut', array("multi_visitins_allowed" => $visitin));
                } else {
                    $visitin = $this->multiVisitInVisitOut();
                    Log::info('performVisitInVisitOut', array("not_multi_visitins_allowed" => $visitin));
                }
            } else {
                if (!empty($requestData) && isset($requestData['visit_purpose'])) {
                    $visitin = $this->singleVisitInVisitOut($requestData);
                    Log::info('performVisitInVisitOut', array("singleVisitInVisitOut" => $visitin));
                } else {
                    $visitin = $this->singleVisitInVisitOut();
                    Log::info('performVisitInVisitOut', array("without_visit_purpose_singleVisitInVisitOut" => $visitin));
                }
            }

            return $visitin;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function multiVisitInVisitOut(array $requestData = []): Visitin
    {
        Log::info('multiVisitInVisitOut', array("multiVisitInVisitOut" => "multiVisitInVisitOut function in"));

        $location_visit = null;
        $location_id = 0;
        if (!empty($requestData) && isset($requestData['visit_purpose'])) {
            $location_visit = LocationVisits::create([
                'user_id' => auth()->id(),
                'location' => $requestData['location'],
                'visit_purpose' => $requestData['visit_purpose'],
                'visit_in' => date('H:i:s'),
                'longitude' => $requestData['longitude'],
                'latitude' => $requestData['latitude'],
                'date' => now()->toDateString(),
            ]);
            // if(auth()->user()->ftoken != null){
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
            //     $accessToken =  $accessTokenArray['access_token'] ?? null;
        
            //     $url = 'https://fcm.googleapis.com/v1/projects/mom-digital-eb91a/messages:send';
        
            //     $headers = [
            //         'Authorization' => 'Bearer ' . $accessToken,
            //         'Content-Type' => 'application/json',
            //     ];
        
            //     $data = [
            //         'message' => [
            //             'token' => auth()->user()->ftoken,
            //             'notification' => [
            //                 'title' => 'Information',
            //                 'body' => "Visit In Successfully..!",
            //             ],
                       
            //         ],
                    
            //     ];
            //     try {
            //         $httpClient =  new Client();
            //         $response = $httpClient->post($url, [
            //             'headers' => $headers,
            //             'json' => $data,
            //             'curl' => [
            //                 CURLOPT_SSL_VERIFYPEER => false,
            //                 CURLOPT_SSL_VERIFYHOST => false,
            //             ],
            //         ]);
            //         \Log::info("firebase response  " . json_encode($response));
            //     } catch (\Exception $e) {
            //         \Log::error("An error occurred for user ID {auth()->user()->id}: " . $e->getMessage());
            //     }
            // }

            
            Log::info('multiVisitInVisitOut', array("location_visit" => $location_visit));
            $location_id = $location_visit->id;
        }
        $type = VisitinType::IN;
        $record = Visitin::my()->where([
            //'date' => now()->toDateString(),
            'user_id' => auth()->id()
        ])->orderByDesc('id')->limit(1)->first();
        Log::info('multiVisitInVisitOut', array("record" => $record));
        if ($record) {
            if ($location_visit) {
                //$location_id = $location_visit->id;
            } else {
                $location_id = $record->location_id;
            }
            if ($record->type == VisitinType::IN->value) {
                $type = VisitinType::OUT;
                LocationVisits::where('id', $record->location_id)->update(['visit_out' => date('H:i:s'), 'status' => 1]);
            }
        }
        if ($location_id != 0) {
            $visitin = Visitin::create([
                'user_id' => auth()->id(),
                'date' => now()->toDateString(),
                'time' => date('H:i:s'),
                'type' => $type,
                'location_id' => $location_id
            ]);
            Log::info('multiVisitInVisitOut', array("visitin" => $visitin));
        } else {
            $visitin = [];
        }
        // if ($type == VisitinType::OUT) {
        //     if(auth()->user()->ftoken != null){
        //         $serviceAccountFile = base_path('mom-digital-eb91a-a5f9fd0b40b5.json');
        //         // Initialize the Google client
        //         $client = new GoogleClient();
        //         // Set the service account credentials
        //         $client->setAuthConfig($serviceAccountFile);
        //         // Set the scope to Firebase Cloud Messaging
        //         $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        //         // Get the access token
        //         $accessTokenArray = $client->fetchAccessTokenWithAssertion();
        //         // Return the access token
        //         $accessToken =  $accessTokenArray['access_token'] ?? null;
        
        //         $url = 'https://fcm.googleapis.com/v1/projects/mom-digital-eb91a/messages:send';
        
        //         $headers = [
        //             'Authorization' => 'Bearer ' . $accessToken,
        //             'Content-Type' => 'application/json',
        //         ];
        
        //         $data = [
        //             'message' => [
        //                 'token' => auth()->user()->ftoken,
        //                 'notification' => [
        //                     'title' => 'Information',
        //                     'body' => "Visit Out Successfully..!",
        //                 ],
                       
        //             ],
                    
        //         ];
        //         try {
        //             $httpClient =  new Client();
        //             $response = $httpClient->post($url, [
        //                 'headers' => $headers,
        //                 'json' => $data,
        //                 'curl' => [
        //                     CURLOPT_SSL_VERIFYPEER => false,
        //                     CURLOPT_SSL_VERIFYHOST => false,
        //                 ],
        //             ]);
        //             \Log::info("firebase response  " . json_encode($response));
        //         } catch (\Exception $e) {
        //             \Log::error("An error occurred for user ID {auth()->user()->id}: " . $e->getMessage());
        //         }
        //     }
        // }
        Log::info('multiVisitInVisitOut', array("multiVisitInVisitOut" => "multiVisitInVisitOut function out"));
        return $visitin;
    }

    private function singleVisitInVisitOut(array $requestData = [])
    {
        Log::info('singleVisitInVisitOut', array("singleVisitInVisitOut" => "singleVisitInVisitOut function in"));
        $visitinExist = Visitin::my()->where([
            'date' => now()->toDateString(),
            'type' => VisitinType::IN
        ])->exists();

        Log::info('singleVisitInVisitOut', array("visitinExist" => $visitinExist));


        if (!$visitinExist) {
            $event = Visitin::create([
                'user_id' => auth()->id(),
                'date' => now()->toDateString(),
                'time' => date('H:i:s'),
                'type' => VisitinType::IN
            ]);
            Log::info('singleVisitInVisitOut', array("notvisitinExist event" => $event));
        } else {
            $visitOutExist = Visitin::my()->where([
                'date' => now()->toDateString(),
                'type' => VisitinType::OUT
            ])->exists();
            Log::info('singleVisitInVisitOut', array("visitOutExist" => $visitOutExist));

            if (!$visitOutExist) {
                $event = Visitin::create([
                    'user_id' => auth()->id(),
                    'date' => now()->toDateString(),
                    'time' => date('H:i:s'),
                    'type' => VisitinType::OUT
                ]);
                Log::info('singleVisitInVisitOut', array("notvisitOutExist" => $visitOutExist));
            } else {
                throw new Exception(__trans('you_already_have_visit_out'));
            }
        }
        Log::info('singleVisitInVisitOut', array("singleVisitInVisitOut" => "singleVisitInVisitOut function out"));
        return $event;
    }
}
