<?php

use Illuminate\Support\Facades\Route;
use Modules\Apparel\Http\Controllers\ApparelController;
use Modules\Apparel\Http\Controllers\EmployeeApparelController;

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
    // Manish Code
    Route::get('apparel', [ApparelController::class, 'show'])->name('apparel');
    Route::get('apparel/create', [ApparelController::class, 'create'])->name('apparel.create');
    Route::post('apparel/save', [ApparelController::class, 'store'])->name('apparel.store');

    Route::get('apparel/edit/{id}', [ApparelController::class, 'edit'])->name('apparel.edit');
    Route::post('apparel/update/{id}', [ApparelController::class, 'update'])->name('apparel.update');

    Route::delete('apparel/remove/{id}', [ApparelController::class, 'destroy'])->name('apparel.remove');

    Route::get('admin/apparelRequest/create', [ApparelController::class, 'createRequest'])->name('admin.apparelRequest.create');
    Route::post('admin/apparelRequest/store', [ApparelController::class, 'storeRequest'])->name('admin.apparelRequest.store');
    Route::get('apparelRequest/remove/{id}', [ApparelController::class, 'removeRequest'])->name('apparelRequest.remove');

    
    // apparel request
    Route::get('apparel-request', [ApparelController::class, 'showRequest'])->name('apparel-request');
    Route::get('apparel/request/edit/{id}', [ApparelController::class, 'requestEdit'])->name('apparel.request.edit');
    Route::post('apparel/request/update/{id}', [ApparelController::class, 'updateRequest'])->name('apparel.request.update');
    Route::get('apparel/approved/{id}', [ApparelController::class, 'requestApproved'])->name('apparel.approved');
    Route::get('apparel/rejected/{id}', [ApparelController::class, 'requestRejected'])->name('apparel.rejected');
    Route::get('apparel-report', [ApparelController::class, 'showApparelReport'])->name('apparel-report');
    Route::post('apparel-report/search', [ApparelController::class, 'showApparelReport'])->name('apparel.report.search');
    Route::get('apparel-report/print/{departmentId?}/{apparelsId?}/{searchEmp?}', [ApparelController::class, 'apparelReportExport'])->name('apparel.report.print');
    Route::post('apparel/getapparelTotal', [ApparelController::class, 'apparelTotal'])->name('apparel.getapparelTotal');
    Route::get('apparel/apparelTransaction', [ApparelController::class, 'showApparelTransaction'])->name('apparel.apparelTransaction');

    // Employee side
    Route::as('employee.')->group(function () {
        // Manish Code
        Route::get('my-apparel', [EmployeeApparelController::class, 'myApparel'])->name('my-apparel');
        Route::get('myApparel/show/{id}', [EmployeeApparelController::class, 'details'])->name('myApparel.details');
        Route::get('myApparel/create', [EmployeeApparelController::class, 'create'])->name('myApparel.create');
        Route::post('myApparel/save', [EmployeeApparelController::class, 'store'])->name('myApparel.store');

        Route::get('myApparel/edit/{id}', [EmployeeApparelController::class, 'edit'])->name('myApparel.edit');
        Route::post('myApparel/update/{id}', [EmployeeApparelController::class, 'update'])->name('myApparel.update');

        Route::delete('myApparel/remove/{id}', [EmployeeApparelController::class, 'destroy'])->name('myApparel.remove');
    });
});
