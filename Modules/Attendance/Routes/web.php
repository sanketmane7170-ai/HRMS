<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\AttendanceController;
use Modules\Attendance\Http\Controllers\CalendarController;
use Modules\Attendance\Http\Controllers\DownloadController;
use Modules\Attendance\Http\Controllers\EmployeeAttendanceController;
use Modules\Attendance\Http\Controllers\EmployeeCheckInController;
use Modules\Attendance\Http\Controllers\HolidayController;
use Modules\Attendance\Http\Controllers\Select2Controller;

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

    Route::post('work-status/update', [\Modules\Attendance\Http\Controllers\WorkStatusController::class, 'update'])->name('work-status.update');
    Route::get('work-status/live-board', [\Modules\Attendance\Http\Controllers\WorkStatusController::class, 'liveBoard'])->name('work-status.live-board');

    Route::get('download-attendance-csv', [DownloadController::class, 'csv'])->name('download.attendance.csv');
    Route::post('download-attendance-csv', [DownloadController::class, 'csv'])->name('download.attendance.csv');
    Route::get('download-bulk-attendance-csv', [DownloadController::class, 'bulkcsv'])->name('download.bulk.attendance.csv');
    Route::get('generate-today-attendance', [AttendanceController::class, 'generate'])->name('attendances.generate');
    Route::put('attendance/{user}/update-attendance/{date}', [AttendanceController::class, 'updateUserDayAttendance'])->name('attendance.user-day-attendance.update');
    Route::get('attendance/{user}/fetch-attendance/{date}', [AttendanceController::class, 'getUserDayAttendance'])->name('attendance.user-day-attendance.fetch');
    Route::resource('attendances', AttendanceController::class)->only('index');
    Route::get('show-attendance-report', [AttendanceController::class, 'showAttendanceReport'])->name('showAttendanceReport');
    Route::post('show-attendance-report', [AttendanceController::class, 'showAttendanceReport'])->name('showAttendanceReport');
    Route::get('attendance/export-pdf', [AttendanceController::class, 'exportPdf'])->name('attendance.export-pdf');

    Route::get('attendance/getBulkUserAttendance', [AttendanceController::class, 'getBulkUserAttendance'])->name('attendance.getBulkUserAttendance');
    Route::post('attendance/sampleDownload', [AttendanceController::class, 'sampleDownload'])->name('attendance.sampleDownload');
    Route::post('attendance/updateBulkUserAttendance', [AttendanceController::class, 'updateBulkUserAttendance'])->name('attendance.updateBulkUserAttendance');

    Route::get('extra-work', [AttendanceController::class, 'extra_work_show'])->name('extra.show');
    Route::get('extra-work-report', [AttendanceController::class, 'extra_work_show_report'])->name('extra.show_report');
    Route::get('add-request', [AttendanceController::class, 'addEmpExtraHours'])->name('addEmpExtraHours');
    Route::post('storeEmpExtraHours', [AttendanceController::class, 'storeEmpExtraHours'])->name('storeEmpExtraHours');
    Route::get('update-request/{id}/{status}', [AttendanceController::class, 'updateRequest'])->name('updateRequest');
    Route::post('allRequestUpdate', [AttendanceController::class, 'allRequestUpdate'])->name('allRequestUpdate');
    Route::get('revert-request/{id}/{status}', [AttendanceController::class, 'revertRequest'])->name('revertRequest');
    Route::get('show-extra-hours-report', [AttendanceController::class, 'show_extra_hours_report'])->name('showExtraHoursReport');
    Route::match(['get', 'post'], 'edit-request/{id}', [AttendanceController::class, 'admin_edit_request_extra_work'])->name('adminEditRequest');
    Route::get('remove-request/{id}', [AttendanceController::class, 'admin_remove_request_extra_work'])->name('adminRemoveRequest');
    Route::post('autoAddExtraWork', [AttendanceController::class, 'autoAddExtraWork'])->name('autoAddExtraWork');
    // Route::resource('attendances', AttendanceController::class)->except('show', 'store');autoAddPHLeave
    //
    Route::get('late-come', [AttendanceController::class, 'late_come_show'])->name('late_come.show');
    Route::get('update-late-request/{id}/{status}', [AttendanceController::class, 'updateLateRequest'])->name('updateLateRequest');
    Route::match(['get', 'post'], 'editRequest/{id}', [AttendanceController::class, 'editRequest'])->name('editRequest');

    Route::get('holidays/calendar/events', [CalendarController::class, 'getHolidayList'])->name('holidays.calendar.events');
    Route::get('holidays/calendar', [CalendarController::class, 'index'])->name('holidays.calendar');
    Route::resource('holidays', HolidayController::class)->except('show');
    // set holiday without attendance
    Route::post('isWithoutAttendPHLeave', [HolidayController::class, 'isWithoutAttendPHLeave'])->name('isWithoutAttendPHLeave');

    Route::as('employee.')->prefix('employee')->group(function () {
        Route::post('checkins', [EmployeeCheckInController::class, 'userCheckInCheckOut'])->name('checkin');
        // Route::post('checkin/clock-out', [EmployeeCheckInController::class, 'userCheckInCheckOut'])->name('checkin.clock-out');
        // Route::post('checkin/clock-in', [EmployeeCheckInController::class, 'userCheckInCheckOut'])->name('checkin.clock-in');
        Route::resource('attendances', EmployeeAttendanceController::class)->only('index');
    });
    Route::get('download-user-attendance-csv/{user}', [DownloadController::class, 'userattendancecsv'])->name('download.user.attendance.csv');
    Route::get('attendance/history/{user}', [AttendanceController::class, 'userattendancehistory'])->name('user.attendance.history');
    Route::post('attendance/history/{user}', [AttendanceController::class, 'userattendancehistory'])->name('user.attendance.history.export');
    Route::get('attendance/visit/history/{user}', [AttendanceController::class, 'uservisithistory'])->name('user.visit.history');
    Route::post('attendance/visit/report/{user}', [AttendanceController::class, 'userVisitReport'])->name('user.visit.report');
    Route::match(['get', 'post'],'show-user-visit-report', [AttendanceController::class, 'showUserVisitReport'])->name('showUserVisitReport');
    Route::match(['get', 'post'],'exportVisitReport', [AttendanceController::class, 'exportVisitReport'])->name('exportVisitReport');

    Route::post('attendance/import',[AttendanceController::class, 'import'])->name('attendance.import');
    Route::get('attendance/import/sample',[AttendanceController::class, 'sampleExport'])->name('attendance.sample.export');

});

Route::as('ajax.')->prefix('ajax')->group(function () {
    Route::as('select2.')->prefix('select2')->group(function () {
        Route::get('attendance/users', [Select2Controller::class, 'getUsers'])->name('fetch.attendance-users');
    });
});