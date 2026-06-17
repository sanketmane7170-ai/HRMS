<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Recruitment\Http\Controllers\JobController;
use Modules\Recruitment\Http\Controllers\ApplicationController;
use Modules\Recruitment\Http\Controllers\InterviewController;
use Modules\Recruitment\Http\Controllers\OfferController;
use Modules\Recruitment\Http\Controllers\CareerController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::middleware(['auth:sanctum',"subscription"])->prefix('v1')->name('api.')->group(function () {
    
    // Job Management API Routes
    Route::prefix('recruitment/jobs')->name('recruitment.jobs.')->group(function () {
        Route::get('/', [JobController::class, 'apiIndex'])->name('index');
        Route::post('/', [JobController::class, 'apiStore'])->name('store');
        Route::get('/{job}', [JobController::class, 'apiShow'])->name('show');
        Route::put('/{job}', [JobController::class, 'apiUpdate'])->name('update');
        Route::delete('/{job}', [JobController::class, 'apiDestroy'])->name('destroy');
        Route::post('/{job}/toggle-status', [JobController::class, 'apiToggleStatus'])->name('toggle-status');
        
        // Job Applications API Routes
        Route::get('/{job}/applications', [ApplicationController::class, 'apiJobApplications'])->name('applications.index');
    });

    // Application Management API Routes
    Route::prefix('recruitment/applications')->name('recruitment.applications.')->group(function () {
        Route::get('/', [ApplicationController::class, 'apiIndex'])->name('index');
        Route::post('/', [ApplicationController::class, 'apiStore'])->name('store');
        Route::get('/{application}', [ApplicationController::class, 'apiShow'])->name('show');
        Route::put('/{application}', [ApplicationController::class, 'apiUpdate'])->name('update');
        Route::delete('/{application}', [ApplicationController::class, 'apiDestroy'])->name('destroy');
        Route::post('/{application}/stage', [ApplicationController::class, 'apiUpdateStage'])->name('update-stage');
        Route::post('/{application}/notes', [ApplicationController::class, 'apiAddNote'])->name('add-note');
        Route::get('/{application}/timeline', [ApplicationController::class, 'apiTimeline'])->name('timeline');
        Route::post('/{application}/schedule-interview', [ApplicationController::class, 'apiScheduleInterview'])->name('schedule-interview');
    });

    // Interview Management API Routes
    Route::prefix('recruitment/interviews')->name('recruitment.interviews.')->group(function () {
        Route::get('/', [InterviewController::class, 'apiIndex'])->name('index');
        Route::post('/', [InterviewController::class, 'apiStore'])->name('store');
        Route::get('/{interview}', [InterviewController::class, 'apiShow'])->name('show');
        Route::put('/{interview}', [InterviewController::class, 'apiUpdate'])->name('update');
        Route::delete('/{interview}', [InterviewController::class, 'apiDestroy'])->name('destroy');
        Route::post('/{interview}/complete', [InterviewController::class, 'apiComplete'])->name('complete');
        Route::post('/{interview}/reschedule', [InterviewController::class, 'apiReschedule'])->name('reschedule');
        Route::get('/{interview}/feedback', [InterviewController::class, 'apiFeedback'])->name('feedback');
    });

    // Offer Management API Routes
    Route::prefix('recruitment/offers')->name('recruitment.offers.')->group(function () {
        Route::get('/', [OfferController::class, 'apiIndex'])->name('index');
        Route::post('/', [OfferController::class, 'apiStore'])->name('store');
        Route::get('/{offer}', [OfferController::class, 'apiShow'])->name('show');
        Route::put('/{offer}', [OfferController::class, 'apiUpdate'])->name('update');
        Route::delete('/{offer}', [OfferController::class, 'apiDestroy'])->name('destroy');
        Route::post('/{offer}/send', [OfferController::class, 'apiSend'])->name('send');
        Route::post('/{offer}/accept', [OfferController::class, 'apiAccept'])->name('accept');
        Route::post('/{offer}/decline', [OfferController::class, 'apiDecline'])->name('decline');
        Route::post('/{offer}/withdraw', [OfferController::class, 'apiWithdraw'])->name('withdraw');
    });

    // Career Portal API Routes (Public accessible through different middleware)
    Route::prefix('career')->name('career.')->group(function () {
        Route::get('/jobs', [CareerController::class, 'apiJobs'])->name('jobs');
        Route::get('/jobs/{job}', [CareerController::class, 'apiJobDetail'])->name('job-detail');
    });

    // Dashboard & Analytics API Routes
    Route::prefix('recruitment/dashboard')->name('recruitment.dashboard.')->group(function () {
        Route::get('/stats', [JobController::class, 'apiDashboardStats'])->name('stats');
        Route::get('/recent-applications', [ApplicationController::class, 'apiRecentApplications'])->name('recent-applications');
        Route::get('/upcoming-interviews', [InterviewController::class, 'apiUpcomingInterviews'])->name('upcoming-interviews');
        Route::get('/pending-offers', [OfferController::class, 'apiPendingOffers'])->name('pending-offers');
    });

});
