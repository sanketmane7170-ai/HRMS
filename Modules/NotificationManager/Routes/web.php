<?php

use Illuminate\Support\Facades\Route;
use Modules\NotificationManager\Http\Controllers\NotificationManagerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware('auth',"subscription")->as('backend.')->group(function () {
    Route::resource('settings/notifications', NotificationManagerController::class)->names('notification.manager');
    Route::post('notification/{alertrecipient}/change-status/{status}', [NotificationManagerController::class, 'updateNotificationStatus'])->name('notification.manager.update-status')
            ->where('alert_status', '0|1');
});