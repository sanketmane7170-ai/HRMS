<?php

use Illuminate\Support\Facades\Route;
use Modules\Recruitment\Http\Controllers\JobController;
use Modules\Recruitment\Http\Controllers\ApplicationController;
use Modules\Recruitment\Http\Controllers\InterviewController;
use Modules\Recruitment\Http\Controllers\InterviewListController;
use Modules\Recruitment\Http\Controllers\SimpleInterviewController;
use Modules\Recruitment\Http\Controllers\InterviewDebugController;
use Modules\Recruitment\Http\Controllers\BasicInterviewController;
use Modules\Recruitment\Http\Controllers\OfferController;
use Modules\Recruitment\Http\Controllers\OfferLetterController;
use Modules\Recruitment\Http\Controllers\CareerController;
use Modules\Recruitment\Http\Controllers\DashboardController;
use Modules\Recruitment\Http\Controllers\AnalyticsController;
use Modules\Recruitment\Http\Controllers\BulkOperationsController;
use App\Http\Controllers\Employee\RecruitmentController;

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

// Public Career Portal Routes (No Auth Required)
Route::prefix('career')->name('career.')->group(function() {
    Route::get('/', [CareerController::class, 'index'])->name('index');
    Route::get('/jobs/{id}', [CareerController::class, 'jobDetail'])->name('job-detail');
    Route::get('/jobs/{id}/apply', [CareerController::class, 'apply'])->name('apply');
    Route::post('/jobs/{id}/apply', [CareerController::class, 'submitApplication'])->name('submit-application');
    Route::get('/application-success/{id}', [CareerController::class, 'applicationSuccess'])->name('application-success');
    Route::match(['GET', 'POST'], '/track-application', [CareerController::class, 'trackApplication'])->name('track-application');
    Route::get('/about', [CareerController::class, 'about'])->name('about');
    Route::get('/benefits', [CareerController::class, 'benefits'])->name('benefits');
    Route::get('/faq', [CareerController::class, 'faq'])->name('faq');
    Route::get('/contact', [CareerController::class, 'contact'])->name('contact');
});

// Admin/HR Routes
Route::middleware(['auth',"subscription"])->prefix('recruitment')->name('recruitment.')->group(function() {

    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');
    Route::get('/recent-applications', [DashboardController::class, 'recentApplications'])->name('recent-applications');
    Route::get('/upcoming-interviews', [DashboardController::class, 'upcomingInterviews'])->name('upcoming-interviews');

    // Analytics Routes
    Route::prefix('analytics')->name('analytics.')->group(function() {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/funnel', [AnalyticsController::class, 'funnel'])->name('funnel');
        Route::get('/pipeline', [AnalyticsController::class, 'pipeline'])->name('pipeline');
        Route::get('/source-effectiveness', [AnalyticsController::class, 'sourceEffectiveness'])->name('source-effectiveness');
        Route::get('/time-to-hire', [AnalyticsController::class, 'timeToHire'])->name('time-to-hire');
    });

    // Bulk Operations Routes
    Route::prefix('bulk')->name('bulk.')->group(function() {
        Route::get('/', [BulkOperationsController::class, 'index'])->name('index');
        Route::post('/process', [BulkOperationsController::class, 'process'])->name('process');
        Route::post('/move-stage', [BulkOperationsController::class, 'moveStage'])->name('move-stage');
        Route::post('/reject', [BulkOperationsController::class, 'reject'])->name('reject');
        Route::post('/schedule-interview', [BulkOperationsController::class, 'scheduleInterview'])->name('schedule-interview');
    });
        
    // Job Management Routes
    Route::prefix('jobs')->name('jobs.')->group(function() {
        Route::get('/', [JobController::class, 'index'])->name('index');
        Route::get('/create', [JobController::class, 'create'])->name('create');
        Route::post('/', [JobController::class, 'store'])->name('store');
        Route::get('/{id}', [JobController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [JobController::class, 'edit'])->name('edit');
        Route::put('/{id}', [JobController::class, 'update'])->name('update');
        Route::delete('/{id}', [JobController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [JobController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/generate-ai', [JobController::class, 'generateAi'])->name('generate-ai');
    });
        
    // Application Management Routes  
    Route::prefix('applications')->name('applications.')->group(function() {
        Route::get('/', [ApplicationController::class, 'index'])->name('index');
        Route::get('/create', [ApplicationController::class, 'create'])->name('create');
        Route::post('/', [ApplicationController::class, 'store'])->name('store');
        Route::get('/{id}', [ApplicationController::class, 'show'])->name('show');
        Route::get('/{id}/download-resume', [ApplicationController::class, 'downloadResume'])->name('download-resume');
        Route::get('/{id}/edit', [ApplicationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ApplicationController::class, 'update'])->name('update');
        Route::delete('/{id}', [ApplicationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/move-stage', [ApplicationController::class, 'moveStage'])->name('move-stage');
        Route::post('/{id}/add-notes', [ApplicationController::class, 'addNotes'])->name('add-notes');
        Route::post('/{id}/schedule-interview', [ApplicationController::class, 'scheduleInterview'])->name('schedule-interview');
        Route::post('/bulk-move', [ApplicationController::class, 'bulkMove'])->name('bulk-move');
        Route::get('/job/{jobId}', [ApplicationController::class, 'byJob'])->name('by-job');
        Route::get('/export', [ApplicationController::class, 'export'])->name('export');
        Route::get('/pipeline-stats', [ApplicationController::class, 'pipelineStats'])->name('pipeline-stats');
    });
    
    // Interview Management Routes
    Route::prefix('interviews')->name('interviews.')->group(function() {
        Route::get('/', [InterviewController::class, 'index'])->name('index');
        Route::get('/datatable', [InterviewController::class, 'datatable'])->name('datatable');
        Route::post('/check-existing', [InterviewController::class, 'checkExistingInterview'])->name('check-existing');
        Route::get('/create', [InterviewController::class, 'create'])->name('create');
        Route::post('/', [InterviewController::class, 'store'])->name('store');
        Route::get('/{id}', [InterviewController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [InterviewController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InterviewController::class, 'update'])->name('update');
        Route::delete('/{id}', [InterviewController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/feedback', [InterviewController::class, 'submitFeedback'])->name('submit-feedback');
        Route::post('/{id}/complete', [InterviewController::class, 'complete'])->name('complete');
        Route::post('/{id}/reschedule', [InterviewController::class, 'reschedule'])->name('reschedule');
        Route::post('/{id}/cancel', [InterviewController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/schedule-next-round', [InterviewController::class, 'scheduleNextRound'])->name('schedule-next-round');
        Route::get('/debug', [InterviewController::class, 'debug'])->name('debug');
        Route::get('/test', [InterviewController::class, 'test'])->name('test');
    });
    
    // New Interview List Routes (Simplified)
    Route::prefix('interview-list')->name('interview-list.')->group(function() {
        Route::get('/', [InterviewListController::class, 'index'])->name('index');
        Route::get('/test-data', [InterviewListController::class, 'testData'])->name('test-data');
    });
    
    // Simple Interview Routes (No Complex Permissions)
    Route::prefix('simple-interviews')->name('simple-interviews.')->group(function() {
        Route::get('/', [SimpleInterviewController::class, 'index'])->name('index');
        Route::get('/data', [SimpleInterviewController::class, 'data'])->name('data');
    });
    
    // Debug Routes (No Authentication)
    Route::prefix('debug')->name('debug.')->group(function() {
        Route::get('/interviews', [InterviewDebugController::class, 'show'])->name('interviews');
        Route::get('/json', [InterviewDebugController::class, 'debug'])->name('json');
    });
    
    // Basic Interview Routes (Simple HTML Table)
    Route::prefix('basic-interviews')->name('basic-interviews.')->group(function() {
        Route::get('/', [BasicInterviewController::class, 'index'])->name('index');
    });
    
    // Offer Management Routes
    Route::prefix('offers')->name('offers.')->group(function() {
        Route::get('/', [OfferController::class, 'index'])->name('index');
        Route::get('/statistics', [OfferController::class, 'statistics'])->name('statistics');
        Route::get('/create', [OfferController::class, 'create'])->name('create');
        Route::post('/', [OfferController::class, 'store'])->name('store');
        Route::get('/{id}', [OfferController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [OfferController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OfferController::class, 'update'])->name('update');
        Route::delete('/{id}', [OfferController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/send', [OfferController::class, 'send'])->name('send');
        Route::post('/{id}/accept', [OfferController::class, 'accept'])->name('accept');
        Route::post('/{id}/decline', [OfferController::class, 'decline'])->name('decline');
        Route::post('/{id}/withdraw', [OfferController::class, 'withdraw'])->name('withdraw');
    });
    
    // Offer Letter Generator Routes
    Route::prefix('offer-letters')->name('offer-letters.')->group(function() {
        Route::get('/selection', [OfferLetterController::class, 'selection'])->name('selection');
        Route::post('/upload', [OfferLetterController::class, 'processUpload'])->name('upload');
        Route::get('/edit-template', [OfferLetterController::class, 'editFromTemplate'])->name('edit-template');
        Route::post('/store-template', [OfferLetterController::class, 'storeTemplate'])->name('store-template');
        Route::get('/create', [OfferLetterController::class, 'create'])->name('create');
        Route::post('/generate-pdf', [OfferLetterController::class, 'generatePdf'])->name('generate-pdf');
        Route::post('/preview', [OfferLetterController::class, 'preview'])->name('preview');
        Route::post('/store', [OfferLetterController::class, 'store'])->name('store');
        Route::get('/get-candidate-details', [OfferLetterController::class, 'getCandidateDetails'])->name('get-candidate-details');
    });
});

// Employee Offer Portal Routes
Route::middleware(['auth',"subscription"])->prefix('employee')->name('backend.employee.')->group(function() {
    Route::prefix('offers')->name('offers.')->group(function() {
        Route::get('/', [\Modules\Recruitment\Http\Controllers\Employee\OfferPortalController::class, 'index'])->name('index');
        Route::get('/{id}', [\Modules\Recruitment\Http\Controllers\Employee\OfferPortalController::class, 'show'])->name('show');
        Route::post('/{id}/accept', [\Modules\Recruitment\Http\Controllers\Employee\OfferPortalController::class, 'accept'])->name('accept');
        Route::post('/{id}/decline', [\Modules\Recruitment\Http\Controllers\Employee\OfferPortalController::class, 'decline'])->name('decline');
        Route::get('/{id}/download', [\Modules\Recruitment\Http\Controllers\Employee\OfferPortalController::class, 'download'])->name('download');
    });

    // Employee Jobs Routes
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/', [RecruitmentController::class, 'index'])->name('index');
        Route::get('/{id}', [RecruitmentController::class, 'show'])->name('show');
        Route::post('/{id}/apply', [RecruitmentController::class, 'apply'])->name('apply');
    });

    // Employee Applications Routes
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [RecruitmentController::class, 'applications'])->name('index');
        Route::get('/{id}', [RecruitmentController::class, 'applicationDetails'])->name('show');
        Route::get('/{id}/track', [RecruitmentController::class, 'trackStatus'])->name('track');
        Route::post('/{id}/respond', [RecruitmentController::class, 'respondToOffer'])->name('respond');
    });
});
