<?php

use Illuminate\Support\Facades\Route;
use Modules\AirTicketSetting\Http\Controllers\AirTicketSettingController;
use Modules\AirTicketSetting\Http\Controllers\RequestAirTicketController;


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
    // Route::resource('settings/air-ticket-setting', AirTicketSettingController::class)->names('airticketsetting');
    // Route::prefix('settings')->as('settings.')->group(function () {
    //     // Route::resource('airticketsetting', AirTicketSettingController::class);
    //     Route::resource('air-ticket-setting', AirTicketSettingController::class);
    // });
    Route::resource('settings/air-ticket-setting', AirTicketSettingController::class)->names('settings.air-ticket-setting');
    Route::get('airTicketReport', [AirTicketSettingController::class, 'airTicketReport'])->name('airTicketReport');
    Route::get('exportAirTicketReport', [AirTicketSettingController::class, 'exportAirTicketReport'])->name('exportAirTicketReport');
    Route::post('air-ticket/status-update', [AirTicketSettingController::class, 'airTicketupdateStatus'])
        ->name('updateAirTicketStatus');

    // Route::resource('settings/air-ticket-setting', AirTicketSettingController::class)
    //     ->parameters(['air-ticket-setting' => 'air_ticket_setting'])
    //     ->names('air-ticket-setting');
    // Route::prefix('settings/air-ticket-settings')->name('airticketsetting.')->group(function () {
    //     Route::get('/', [AirTicketSettingController::class, 'index'])->name('index');
    //     Route::get('/create', [AirTicketSettingController::class, 'create'])->name('create');
    //     Route::post('/', [AirTicketSettingController::class, 'store'])->name('store');
    //     Route::get('/{airTicketSetting}/edit', [AirTicketSettingController::class, 'edit'])->name('edit');
    //     Route::put('/{airTicketSetting}', [AirTicketSettingController::class, 'update'])->name('update');
    //     Route::delete('/{airTicketSetting}', [AirTicketSettingController::class, 'destroy'])->name('destroy');
    //     Route::get('/{airTicketSetting}/show', [AirTicketSettingController::class, 'show'])->name('show');
    // });
    Route::post('air-ticket-setting/{airticketsetting}/change-status/{status}', [AirTicketSettingController::class, 'updateStatus'])->name('airticketsetting.update-status')
        ->where('status', '0|1');

    Route::get('toMultipleUser/{id}', [AirTicketSettingController::class, 'assign_to_multiple_user'])->name('settings.air-ticket-setting.toMultipleUser');
    Route::Post('toMultipleUser/{id}', [AirTicketSettingController::class, 'assign_to_multiple_user'])->name('settings.air-ticket-setting.toMultipleUser');
    Route::get('settings/air-ticket-setting/get-employees/{departmentId}', [AirTicketSettingController::class, 'getEmployees']);

    //emp can request for air ticket
    Route::get('employee/air-ticket', [RequestAirTicketController::class, 'index'])->name('employee.air-ticket.index');
    Route::get('employee/air-ticket/create', [RequestAirTicketController::class, 'create'])->name('employee.air-ticket.create');
    Route::post('employee/air-ticket/store', [RequestAirTicketController::class, 'store'])->name('employee.air-ticket.store');
    Route::get('employee/air-ticket/edit/{id}', [RequestAirTicketController::class, 'edit'])->name('employee.air-ticket.edit');
    Route::post('employee/air-ticket/update/{id}', [RequestAirTicketController::class, 'update'])->name('employee.air-ticket.update');
    Route::get('employee/air-ticket/policy/', [RequestAirTicketController::class, 'getPolicy'])->name('employee.air-ticket.policy');
    Route::get('employee/air-ticket/info/{id}', [RequestAirTicketController::class, 'requestDetails'])->name('employee.air-ticket.info');

    Route::delete('employee/air-ticket/delete/{id}', [RequestAirTicketController::class, 'destroy'])->name('employee.air-ticket.delete');
});
