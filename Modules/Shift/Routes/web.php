<?php
use Modules\Shift\Http\Controllers\ShiftController;

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
    Route::resource('shift', ShiftController::class)->except('show');
    Route::get('roster', [Modules\Shift\Http\Controllers\ShiftController::class, 'roster'])->name('shift.roster');
    Route::post('roster', [Modules\Shift\Http\Controllers\ShiftController::class, 'roster'])->name('shift.roster.search');
    Route::get('roster/print/{departmentId?}/{startDate?}', [Modules\Shift\Http\Controllers\ShiftController::class, 'printRoster'])->name('shift.roster.print');
    Route::get('assign/shift', [Modules\Shift\Http\Controllers\ShiftController::class, 'as_index'])->name('assign_shift.index');
    Route::get('calendar/{user}', [Modules\Shift\Http\Controllers\ShiftController::class, 'openCalendar'])->name('assign_shift.openCalendar');
    Route::post('assign/shift/{user}', [Modules\Shift\Http\Controllers\ShiftController::class, 'assignShift'])->name('assign_shift.toUser');
    Route::delete('delete/schedule/{shift}', [Modules\Shift\Http\Controllers\ShiftController::class, 'destroy_schedule'])->name('schedule.destroy');
    Route::get('assign/multishift/{user}', [Modules\Shift\Http\Controllers\ShiftController::class, 'as_create'])->name('assign_shift.multiple');
    Route::post('assign/multishift/{user}', [Modules\Shift\Http\Controllers\ShiftController::class, 'assignMultipleShift'])->name('assign_multishift.toUser');
    Route::get('assign/toMultipleUser/{id}', [Modules\Shift\Http\Controllers\ShiftController::class, 'assign_shifts_to_multiple_user'])->name('assign_shift.toMultipleUser');
    Route::Post('assign/toMultipleUser/{id}', [Modules\Shift\Http\Controllers\ShiftController::class, 'assign_shifts_to_multiple_user'])->name('assign_shift.toMultipleUser');
    Route::get('/get-employees/{departmentId}/{search?}', [ShiftController::class, 'getEmployees']);
});
