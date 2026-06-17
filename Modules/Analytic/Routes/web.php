<?php

use Illuminate\Support\Facades\Route;
use Modules\Analytic\Http\Controllers\ListController;

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
    Route::prefix('analytic')->as('analytic.')->group(function () {
        Route::get('anniversary-list', [ListController::class, 'upcominAnniversaryList'])->name('anniversary.list');
        Route::get('birthday-list', [ListController::class, 'upcomingBirthdayList'])->name('birthday.list');
        Route::get('documets-expiring.list', [ListController::class, 'documentexpiringList'])->name('documetsexpiring.list');
        Route::get('feature-list', [ListController::class, 'latestFeatureList'])->name('feature.list');
        // Route::get('probation-ending-list',  [ListController::class, 'upcomingProbationList'])->name('probation.upcoming.list');
        Route::get('expire-document-list', [ListController::class, 'expiredDocumentList'])->name('document.expired.list');
        Route::get('expire-filemanager-list', [ListController::class, 'expiredFilemanagerList'])->name('filemanager.expired.list');
        Route::get('country-user-list', [ListController::class, 'countryUserList'])->name('country.user.list');
        Route::get('leave-list', [ListController::class, 'upcominLeaveList'])->name('leave.list');
        Route::get('airticket-list', [ListController::class, 'upcominAirTicketList'])->name('airticket.list');
        Route::get('PIC-certification-expiry-list',  [ListController::class, 'picCertificationExpiryList'])->name('PICCertificationExpiry.list');

    });
});
