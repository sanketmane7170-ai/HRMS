<?php

use Illuminate\Support\Facades\Route;
use Modules\Onboarding\Http\Controllers\OnboardingController;

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

// Public Portal Landing & Login
Route::prefix('onboarding-portal')->name('portal.')->group(function() {
    Route::get('/', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'index'])->name('index');
    Route::get('/login', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'login'])->name('authenticate');
    
    // Forgot Password Flow - Added by Sanket for ISSUE 20
    Route::get('/forgot-password', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'sendResetOtp'])->name('password.email');
    Route::get('/verify-otp', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'showVerifyOtp'])->name('password.otp');
    Route::post('/verify-otp', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'verifyOtp'])->name('password.verify-otp');
    Route::get('/reset-password', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'resetPassword'])->name('password.update');
    
    // Protected Portal Routes
    Route::middleware(['auth:portal', 'onboarding.portal.auth',"subscription"])->group(function() {
        Route::get('/dashboard', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'dashboard'])->name('dashboard');
        
        // Form Completion
        Route::get('/personal-info', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'showPersonalInfo'])->name('personal-info');
        Route::post('/personal-info', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'savePersonalInfo'])->name('save-personal-info');
        
        // Photo Upload
        Route::get('/photo-upload', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'showPhotoUpload'])->name('photo-upload');
        Route::post('/photo-upload', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'savePhoto'])->name('save-photo');
        
        // Documents
        Route::get('/documents', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'showDocuments'])->name('documents');
        Route::post('/documents', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'saveDocument'])->name('save-document');
        Route::delete('/documents/{id}', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'deleteDocument'])->name('delete-document'); // Added by Sanket for Bug 17
        
        Route::post('/logout', [\Modules\Onboarding\Http\Controllers\PortalController::class, 'logout'])->name('logout');
    });
});

Route::middleware(['auth',"subscription"])->prefix('onboarding')->name('onboarding.')->group(function() {
    Route::get('/', [OnboardingController::class, 'index'])->name('dashboard');
    
    // New Hires Management
    Route::get('/new-hires', [OnboardingController::class, 'newHires'])->name('new-hires');
    Route::post('/new-hires/store', [OnboardingController::class, 'storeNewHire'])->name('new-hires.store');
    Route::post('/new-hires/import', [OnboardingController::class, 'importNewHires'])->name('new-hires.import');
    Route::get('/new-hires/template', [OnboardingController::class, 'downloadImportTemplate'])->name('new-hires.template'); // Added by Sanket
    
    // Visa Workflow Tracker Routes
    Route::get('/tracker', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'index'])->name('tracker.index');
    Route::post('/tracker/{id}/init', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'initProcess'])->name('tracker.init');
    Route::post('/tracker/{id}/update-status', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'updateVisaStatus'])->name('tracker.update-status');
    Route::post('/tracker/{id}/upload-entry-permit', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'uploadEntryPermit'])->name('tracker.upload-entry-permit');
    Route::post('/tracker/{id}/schedule-medical', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'scheduleMedical'])->name('tracker.schedule-medical');
    Route::post('/tracker/{id}/upload-medical-result', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'uploadMedicalResult'])->name('tracker.upload-medical-result');
    Route::post('/tracker/{id}/stamp-visa', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'stampVisa'])->name('tracker.stamp-visa');
    Route::post('/tracker/{id}/upload-insurance', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'uploadInsurance'])->name('tracker.upload-insurance');
    Route::post('/tracker/{id}/update-compliance', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'updateCompliance'])->name('tracker.update-compliance');
    Route::post('/tracker/{id}/update-readiness', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'updateReadiness'])->name('tracker.update-readiness');
    
    // Secure Downloads (Phase 8)
    Route::get('/secure/download/{type}/{id}/{field}', [\Modules\Onboarding\Http\Controllers\SecureFileController::class, 'download'])->name('secure.download');

    // Phase 6: Conversion
    Route::post('/tracker/{id}/convert-employee', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'convertToEmployee'])->name('tracker.convert-employee');
    
    // Phase 7: Detailed View
    Route::get('/tracker/{id}/details', [\Modules\Onboarding\Http\Controllers\VisaWorkflowController::class, 'show'])->name('tracker.show');

    // Phase 5: Probation Reviews
    Route::get('probation/reviews', [\Modules\Onboarding\Http\Controllers\ProbationReviewController::class, 'index'])->name('probation.index');
    Route::get('probation/review/{id}', [\Modules\Onboarding\Http\Controllers\ProbationReviewController::class, 'edit'])->name('probation.edit');
    Route::post('probation/review/{id}', [\Modules\Onboarding\Http\Controllers\ProbationReviewController::class, 'update'])->name('probation.update');

    // Employee Actions
    Route::get('/{id}', [OnboardingController::class, 'show'])->name('show');
    Route::put('/{id}', [OnboardingController::class, 'update'])->name('update');
    Route::delete('/{id}', [OnboardingController::class, 'destroy'])->name('destroy'); // BUG-ONB-003 Fix: Removed duplicate
    Route::post('/{id}/upload-document', [OnboardingController::class, 'uploadDocument'])->name('upload-document');
    Route::post('/document/{id}/verify', [OnboardingController::class, 'verifyDocument'])->name('verify-document'); // Bug 8
    Route::delete('/document/{id}', [OnboardingController::class, 'deleteDocument'])->name('delete-document');     // Bug 17 Admin
    Route::post('/{id}/provide-access', [OnboardingController::class, 'providePortalAccess'])->name('provide-access');

});
