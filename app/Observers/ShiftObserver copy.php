<?php

namespace App\Observers;

use App\Models\ShiftSchedule;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Services\FirebaseService;
use App\Notifications\ServiceRequest\GenerateNotification;
use Google\Client as GoogleClient;
use GuzzleHttp\Client;

class ShiftObserver
{
    /**
     * Handle the ShiftSchedule "created" event.
     */
    // public function created(ShiftSchedule $shiftSchedule): void
    // {
    //     //
    // }

    /**
     * Handle the ShiftSchedule "updated" event.
     */
    public function updated(User $user): void
    {
        $currentDate = Carbon::now()->toDateString();
        $userShifts = $user->assigned_shifts()->whereDate('assigned_for_date', $currentDate)->with('shift_schedule_information')->get();

        foreach ($userShifts as $shift) {
            $shiftData = $shift->shift_schedule_information;

            if ($this->isShiftApproaching($shiftData->shift_start)) {
                $this->sendNotification($user, $shiftData, 'checkin');
            } else if($this->isShiftApproaching($shiftData->shift_end)) {
                $this->sendNotification($user, $shiftData, 'checkout');
            } else {
                //
            }
        }
    }

    private function isShiftApproaching($shiftStartTime)
    {
        $currentTimeDubai = Carbon::now('Asia/Dubai');
    
        $shiftTime = Carbon::parse($shiftStartTime)->format('H:i');
        
        $shiftTimeMinus10Minutes = Carbon::parse($shiftStartTime)->subMinutes(15)->format('H:i');
    
        $currentDubaiTime = $currentTimeDubai->format('H:i');
    
        return $currentDubaiTime == $shiftTimeMinus10Minutes;
    }

    private function sendNotification(User $user, ShiftSchedule $shiftSchedule,$flag)
    {
        $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();
        //Log::info("Notification sending for {$user->name} for shift {$shiftSchedule->title}");
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'message' => "Don't forget to {$flag} on the app to ensure accurate time tracking!",
            'route' => route('backend.employee.document-requests.index'),
        ];
        //$user->notify(new GenerateNotification($userData, $admin->id));
        //$fcmService = new FirebaseService();
        //$notification = $fcmService->sendFcmMessage($user->ftoken, 'Reminder', $userData['message'],1);

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
        $accessToken =  $accessTokenArray['access_token'] ?? null;

        $url = 'https://fcm.googleapis.com/v1/projects/mom-digital-eb91a/messages:send';

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];

        $data = [
            'message' => [
                'token' => $user->ftoken,
                'notification' => [
                    'title' => 'Reminder',
                    'body' => $userData['message'],
                ],
                'data' => ["enum" => 'shift'],
            ],
            
        ];
        try {
            $httpClient =  new Client();
            $response = $httpClient->post($url, [
                'headers' => $headers,
                'json' => $data,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ],
            ]);
            //\Log::info("firebase response  " . json_encode($response));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            //\Log::info("Error  " . $error);
            return false;
        }
        return $response->getBody()->getContents();
    }

    /**
     * Handle the ShiftSchedule "deleted" event.
     */
    public function deleted(ShiftSchedule $shiftSchedule): void
    {
        //
    }

    /**
     * Handle the ShiftSchedule "restored" event.
     */
    public function restored(ShiftSchedule $shiftSchedule): void
    {
        //
    }

    /**
     * Handle the ShiftSchedule "force deleted" event.
     */
    public function forceDeleted(ShiftSchedule $shiftSchedule): void
    {
        //
    }
}
