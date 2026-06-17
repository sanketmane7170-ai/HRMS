<?php

use Illuminate\Support\Facades\Route;
use Modules\PolicySetting\Http\Controllers\PolicySettingController;

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
    Route::resource('policysetting', PolicySettingController::class)->names('settings.policysetting');
    Route::get('settings/leaves_policy', [PolicySettingController::class, 'leaves_policy'])->name('settings.leaves_policy');
    Route::post('settings/leaves_policy', [PolicySettingController::class, 'leaves_policy'])->name('settings.leaves_policy');
    Route::get('settings/edit_leaves_policy/{id}', [PolicySettingController::class, 'edit_leaves_policy'])->name('settings.edit_leaves_policy');
    Route::post('settings/edit_leaves_policy/{id}', [PolicySettingController::class, 'edit_leaves_policy'])->name('settings.edit_leaves_policy');
    Route::get('settings/attendance_policy', [PolicySettingController::class, 'attendance_policy'])->name('settings.attendance_policy');
    Route::post('settings/attendance_policy', [PolicySettingController::class, 'attendance_policy'])->name('settings.attendance_policy');
    Route::get('settings/late_comers_policy', [PolicySettingController::class, 'late_comers_policy'])->name('settings.late_comers_policy');
    Route::get('settings/overtime_policy', [PolicySettingController::class, 'overtime_policy'])->name('settings.overtime_policy');
    Route::post('settings/overtime_policy', [PolicySettingController::class, 'overtime_policy'])->name('settings.overtime_policy');
    Route::get('settings/early_comers_policy', [PolicySettingController::class, 'early_comers_policy'])->name('settings.early_comers_policy');
    Route::get('settings/endOfServicePolicy', [PolicySettingController::class, 'endOfServicePolicy'])->name('settings.endOfServicePolicy');
    Route::any('settings/addendOfServicePolicy', [PolicySettingController::class, 'addendOfServicePolicy'])->name('settings.addendOfServicePolicy');
    Route::any('settings/editendOfServicePolicy/{id}', [PolicySettingController::class, 'editendOfServicePolicy'])->name('settings.editendOfServicePolicy');
    Route::delete('settings/removeservicepolicy/{id}', [PolicySettingController::class, 'removeservicepolicy'])->name('settings.removeservicepolicy');
    Route::match(['get', 'post'], 'settings/shiftPolicy', [PolicySettingController::class, 'addshiftPolicy'])->name('settings.shiftPolicy');
    Route::match(['get', 'post'], 'settings/viewToAllShiftPolicy', [PolicySettingController::class, 'viewToAllShiftPolicy'])->name('settings.viewToAllShiftPolicy');
    Route::match(['get', 'post'], 'settings/leavesPolicy', [PolicySettingController::class, 'leavesPolicy'])->name('settings.leavesPolicy');
    Route::match(['get', 'post'], 'settings/payrollPolicy', [PolicySettingController::class, 'payrollPolicy'])->name('settings.payrollPolicy');
    Route::delete('settings/payroll-policy/{id}', [PolicySettingController::class, 'deletePayrollPolicy'])
    ->name('settings.deletePayrollPolicy');
});
