<?php

use Illuminate\Support\Facades\Route;
use Modules\Expense\Http\Controllers\ExpenseTypeController;
use Modules\Expense\Http\Controllers\ExpenseController;
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
    Route::resource('expense-types', ExpenseTypeController::class);
    Route::resource('expense', ExpenseController::class);
    Route::put('expense/{expense}/reject', [ExpenseController::class, 'rejectExpense'])->name('expense.reject');
    Route::post('expense/{expense}/{action}', [ExpenseController::class, 'takeAction'])->name('expense.action')->where(['action' => 'approve|reject']);
    Route::delete('expense_documents/{id}/{user_id}', [ExpenseController::class, 'document_delete'])->name('expense.document_delete');
    Route::get('users', [ExpenseController::class, 'getUsers'])->name('expense.users');

});
