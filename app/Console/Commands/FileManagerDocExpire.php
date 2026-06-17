<?php

namespace App\Console\Commands;

use App\Mail\FileManagerDocExpireEmail;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Modules\FileManager\Entities\FileManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class FileManagerDocExpire extends Command implements ShouldQueue
{

    private $daysBeforeNotification = 40;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filemanager:doc-exprire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to creator user for document expire in dynamic days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now() . '] filemanager:doc-exprire started.');

        $filemanager = FileManager::select('id', 'title', 'expiry_days', 'expiry_date', 'employee_id')
            ->with(['employee', 'department'])
            ->whereNotNull('expiry_date')
            ->whereNotNull('expiry_days')
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->get();

        Log::info('doc-exprire', ["filemanager" => $filemanager]);

        $temArr = $filemanager->filter(function ($data) {
            $expiryDate = Carbon::parse($data->expiry_date);
            $newExpiryDate = $expiryDate->subDays($data->expiry_days)->toDateString();
            return $newExpiryDate == now()->toDateString();
        });
        Log::info('doc-exprire', ["temArr" => $temArr]);

        foreach ($temArr as $data) {
            $this->info('[' . now() . '] data: ' . json_encode($data));

            if (isset($data->employee->ftoken) && $data->employee->ftoken != '') {
                $userData = [
                    'id' => $data->employee->id,
                    'name' => $data->employee->name,
                    'email' => $data->employee->email,
                    'message' => 'Your file manager document expire soon. Please update your filemanager document.',
                    'route' => route('backend.filemanager.index'),
                ];
                try {
                    Mail::to($data->employee->email)->send(new FileManagerDocExpireEmail($data,$userData));
                } catch (Exception $e) {
                    \Log::error('Failed to send email. Recipient: ' . $data->employee->email.$e);
                }
                $fcmService = new FirebaseService();
                $notification = $fcmService->sendFcmMessage($data->employee->ftoken, 'Document Reminder', $userData['message'], 5);
                // dd($data);
               
            }
        }

        $this->info('[' . now() . '] filemanager:doc-exprire ended.');
        // send email to user to expire document today

        //foreach ($temArr as $user) {
        //Notification::send($hrs, new ProbationEndTodayNotification($user));
        //}


    }
}
