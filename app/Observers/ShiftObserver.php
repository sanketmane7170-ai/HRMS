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
     * Handle the ShiftSchedule "updated" event.
     */
    public function updated(User $user): void
    {
        Log::info("ShiftObserver@updated triggered for user: {$user->id} - {$user->name}");

        $currentDate = Carbon::now()->toDateString();
        Log::info("Current date: {$currentDate}");

        $userShifts = $user->assigned_shifts()
            ->whereDate('assigned_for_date', $currentDate)
            ->with('shift_schedule_information')
            ->get();

        Log::info("Found " . $userShifts->count() . " shift(s) for user {$user->name}");

        foreach ($userShifts as $shift) {
            $shiftData = $shift->shift_schedule_information;
            if (!$shiftData) {
                Log::warning("No shift_schedule_information found for shift ID {$shift->id}");
                continue;
            }

            Log::info("Checking shift ID {$shift->id} | Start: {$shiftData->shift_start} | End: {$shiftData->shift_end}");

            if ($this->isShiftApproaching($shiftData->shift_start)) {
                Log::info("Shift approaching for START time of {$shiftData->shift_start}");
                $this->sendNotification($user, $shiftData, 'checkin');
            } elseif ($this->isShiftApproaching($shiftData->shift_end)) {
                Log::info("Shift approaching for END time of {$shiftData->shift_end}");
                $this->sendNotification($user, $shiftData, 'checkout');
            } else {
                Log::info("No shift approaching for user {$user->name} at this time.");
            }
        }
    }

    private function isShiftApproaching($shiftTime)
    {
        $currentTimeDubai = Carbon::now('Asia/Dubai');
        $shiftTimeMinus15Minutes = Carbon::parse($shiftTime)->subMinutes(15);

        $currentDubaiTime = $currentTimeDubai->format('H:i');
        $checkTime = $shiftTimeMinus15Minutes->format('H:i');

        Log::info("Current Dubai time: {$currentDubaiTime} | Shift minus 15 min: {$checkTime}");

        return $currentDubaiTime === $checkTime;
    }

    private function sendNotification(User $user, ShiftSchedule $shiftSchedule, $flag)
    {
        Log::info("Preparing to send '{$flag}' notification for user {$user->name}, shift {$shiftSchedule->id}");

        try {
            $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();
            if (!$admin) {
                Log::warning("Admin user not found. Notification may not have a valid sender.");
            }

            $message = "Don't forget to {$flag} on the app to ensure accurate time tracking!";
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'message' => $message,
                'route' => route('backend.employee.document-requests.index'),
            ];

            Log::info("UserData for FCM: " . json_encode($userData));

            $serviceAccountFile = base_path('mom-digital-eb91a-a5f9fd0b40b5.json');
            Log::info("Using service account file: {$serviceAccountFile}");

            $client = new GoogleClient();
            $client->setAuthConfig($serviceAccountFile);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            $accessTokenArray = $client->fetchAccessTokenWithAssertion();
            $accessToken = $accessTokenArray['access_token'] ?? null;

            if (!$accessToken) {
                Log::error("Failed to retrieve Firebase access token");
                return false;
            }

            Log::info("Firebase access token retrieved successfully");

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
                        'body' => $message,
                    ],
                    'data' => ["enum" => 'shift'],
                ],
            ];

            Log::info("Sending FCM request: " . json_encode($data));

            $httpClient = new Client();
            $response = $httpClient->post($url, [
                'headers' => $headers,
                'json' => $data,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            Log::info("FCM Notification sent successfully. Response: {$responseBody}");

            return $responseBody;
        } catch (\Exception $e) {
            Log::error("Error sending FCM notification: {$e->getMessage()}");
            Log::error($e->getTraceAsString());
            return false;
        }
    }

    public function deleted(ShiftSchedule $shiftSchedule): void
    {
        Log::info("ShiftObserver@deleted triggered for shift ID {$shiftSchedule->id}");
    }

    public function restored(ShiftSchedule $shiftSchedule): void
    {
        Log::info("ShiftObserver@restored triggered for shift ID {$shiftSchedule->id}");
    }

    public function forceDeleted(ShiftSchedule $shiftSchedule): void
    {
        Log::info("ShiftObserver@forceDeleted triggered for shift ID {$shiftSchedule->id}");
    }
}
