<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markAsRead($notificationId)
    {
        $notification = DatabaseNotification::findOrFail($notificationId);
        $notification->markAsRead();
        return response()->json(['message' => 'Notification marked as read successfully']);
    }

    public function readAllNotifications(Request $request){
        // Delete notifications for the given notifiable_id
        DatabaseNotification::where('notifiable_id', $request->userId)->update(['read_at' => now()]);
        $response = getSuccessResponse(createFlashMessage('All Notifications', 'read'));

        return response()->json($response);
    }
}
