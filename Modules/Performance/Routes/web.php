<?php

use Illuminate\Support\Facades\Route;
use Modules\Performance\Http\Controllers\AppraisalTemplateController;
use Modules\Performance\Http\Controllers\PerformanceAppraisalController;

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

Route::prefix('performance')->name('performance.')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [PerformanceAppraisalController::class, 'index'])->name('index');
    Route::get('/create', [PerformanceAppraisalController::class, 'create'])->name('create');
    Route::post('/', [PerformanceAppraisalController::class, 'store'])->name('store');
    Route::get('/{appraisal}/edit', [PerformanceAppraisalController::class, 'edit'])->name('edit');
    Route::put('/{appraisal}/update', [PerformanceAppraisalController::class, 'update'])->name('update');

    Route::get('/{appraisal}/show', [PerformanceAppraisalController::class, 'show'])->name('show');
    Route::delete('/{appraisal}/destroy', [PerformanceAppraisalController::class, 'destroy'])->name('destroy');

    Route::get('/{appraisal}/certificate', [PerformanceAppraisalController::class, 'downloadCertificate'])->name('certificate');

    // ✅ Add this:
    Route::get('/export/pdf', [PerformanceAppraisalController::class, 'exportPdf'])->name('exportPdf');
    Route::get('/performance-dashboard', [PerformanceAppraisalController::class, 'dashboard'])->name('performance.dashboard');

    Route::get('template/{id}/criteria', [PerformanceAppraisalController::class, 'getTemplateCriteria']);

    Route::get(
        'monthly-report',
        [PerformanceAppraisalController::class, 'monthlyReport']
    )->name('monthly.report');

    Route::get('templates/by-branch', [
        PerformanceAppraisalController::class,
        'getTemplatesByBranch',
    ]);

    Route::get(
    'employees/by-manager/{manager}',
    [PerformanceAppraisalController::class, 'getEmployeesByManager']
);


    Route::prefix('templates')->name('template.')->group(function () {

        Route::get('/', [AppraisalTemplateController::class, 'index'])->name('index');
        Route::get('/create', [AppraisalTemplateController::class, 'create'])->name('create');
        Route::post('/', [AppraisalTemplateController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AppraisalTemplateController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AppraisalTemplateController::class, 'update'])->name('update');
        Route::delete('/{id}', [AppraisalTemplateController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/show', [AppraisalTemplateController::class, 'show'])
            ->name('show');

    });

});

