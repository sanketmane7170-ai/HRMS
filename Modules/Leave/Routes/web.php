<?php

use Illuminate\Support\Facades\Route;
use Modules\Leave\Http\Controllers\EmployeeLeaveController;
use Modules\Leave\Http\Controllers\LeaveController;
use Modules\Leave\Http\Controllers\LeaveTypeController;

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
    Route::resource('leave-types', LeaveTypeController::class)->except('show');
    Route::put('leaves/{leave}/reject', [LeaveController::class, 'rejectLeave'])->name('leaves.reject');
    Route::post('leaves/{leave}/{action}/{level?}', [LeaveController::class, 'takeAction'])->name('leaves.action')->where(['action' => 'approve|reject']);
    Route::get('leaves/pdfexport/{leave_id}', [LeaveController::class, 'pdfexport'])->name('leaves.pdfexport');
    Route::resource('leaves', LeaveController::class, [
        'parameters' => [
            'leaves' => 'leave'
        ]
    ]);
    Route::post('isAllowNegativeLeave', [LeaveController::class, 'isAllowNegativeLeave'])->name('isAllowNegativeLeave');
    Route::post('isAllowAddProbationLeave', [LeaveController::class, 'isAllowAddProbationLeave'])->name('isAllowAddProbationLeave');
    Route::post('dailyLeavePolicy', [LeaveController::class, 'dailyLeavePolicy'])->name('dailyLeavePolicy');
    Route::post('isMonthWiseShowLeave', [LeaveController::class, 'isMonthWiseShowLeave'])->name('isMonthWiseShowLeave');
    Route::post('annualLeavePolicy', [LeaveController::class, 'annualLeavePolicy'])->name('annualLeavePolicy');
    Route::post('isMonthWise2LeaveAdd', [LeaveController::class, 'isMonthWise2LeaveAdd'])->name('isMonthWise2LeaveAdd');
    Route::post('1yearGiven2Leave', [LeaveController::class, 'yearGiven2Leave'])->name('1yearGiven2Leave');
    Route::post('leaveAllowInProbation', [LeaveController::class, 'leaveAllowInProbation'])->name('leaveAllowInProbation');
    Route::post('leaveRecurringPolicy', [LeaveController::class, 'leaveRecurringPolicy'])->name('leaveRecurringPolicy');
    Route::post('newUserdailyLeavePolicy', [LeaveController::class, 'newUserdailyLeavePolicy'])->name('newUserdailyLeavePolicy');
    Route::post('newUserMonthlyLeavePolicy', [LeaveController::class, 'newUserMonthlyLeavePolicy'])->name('newUserMonthlyLeavePolicy');

    Route::get('leaves-planner', [LeaveController::class, 'openCalendar'])->name('leaves.calender');
    Route::post('leaves-calender', [LeaveController::class, 'openCalendar'])->name('leaves.calender.search');

    Route::get('leaves-report', [LeaveController::class, 'leavesReport'])->name('leaves.report');
    Route::post('leaves-report', [LeaveController::class, 'leavesReport'])->name('leaves.report.search');
    Route::get('ph-leaves-report', [LeaveController::class, 'phleavesReport'])->name('phleaves.report');
    Route::post('ph-leaves-report', [LeaveController::class, 'phleavesReport'])->name('phleaves.report');
    Route::get('leaves-report/print/{departmentId?}/{type_id?}/{searchEmp?}', [LeaveController::class, 'leavesReportPrint'])->name('leaves.report.print');
    Route::get('phleaves-report/print/{departmentId?}/{searchEmp?}', [LeaveController::class, 'phleavesReportPrint'])->name('phleaves.report.print');
    Route::get('previous-year-leaves-report', [LeaveController::class, 'previousYearLeavesReport'])->name('previousyear.leaves.report');
    Route::post('previous-year-leaves-report', [LeaveController::class, 'previousYearLeavesReport'])->name('previousyear.leaves.report.search');

    Route::get('sampleUpdateleaveToExcel', [LeaveController::class, 'sampleUpdateleaveToExcel'])->name('sampleUpdateleaveToExcel');
    Route::post('updateLeaveToExcel', [LeaveController::class, 'updateLeaveToExcel'])->name('updateLeaveToExcel');

    Route::as('employee.')->group(function () {
        Route::resource('my-leaves', EmployeeLeaveController::class, [
            'names' => 'leaves',
            'parameters' => [
                'my-leaves' => 'leave'
            ]
        ]);
    });
    Route::get('generate-leave-report', [LeaveController::class, 'generateLeaveReport'])->name('leave.report.generate');

    // Sachin Code
    Route::get('leaves/create', [LeaveController::class, 'createLeaveByAdmin'])->name('leaves.create');
    Route::post('leaves/save', [LeaveController::class, 'storeAdminLeaveByAdmin'])->name('leaves.store');

    // Gagan Code
    Route::get('leave/balance/edit/{user}/{leaveType}', [LeaveController::class, 'getLeaveBalanceEditModal'])->name('leave-balance.edit');
    Route::post('leave/balance/update/{user}/{leaveType}', [LeaveController::class, 'updateLeaveBalance'])->name('leave-balance.update');
    Route::get('leave/balance/update/logs', [LeaveController::class, 'getLeaveBalanceUpdateLogs'])->name('leave-balance.update.logs');
    Route::get('leave/balance/update/transaction', [LeaveController::class, 'getLeaveBalanceUpdateTransaction'])->name('leave-balance.update.transaction');
});
