<?php

use Illuminate\Support\Facades\Route;
use Modules\Announcement\Http\Controllers\AnnouncementController;
use Modules\Announcement\Http\Controllers\AnnouncementTypeController;

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
    Route::resource('announcement-types', AnnouncementTypeController::class);
    Route::resource('announcements', AnnouncementController::class);
});
