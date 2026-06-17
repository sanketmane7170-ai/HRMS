<?php

use Illuminate\Support\Facades\Route;
use Modules\Resignation\Http\Controllers\ResignationController;

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

Route::prefix('resignation')->middleware(['auth',"subscription"])->group(function() {
    // Employee View
    Route::get('/my', [ResignationController::class, 'employeeView'])->name('resignation.employee');
    
    // Manager/Admin View
    Route::middleware('role:admin|hr|Manager|CEO|Director')->group(function() {
        Route::get('/manage', [ResignationController::class, 'managerView'])->name('resignation.manager');
    });
    
    // HR/Admin Advanced View (Full override)
    Route::middleware('role:admin|hr|CEO')->group(function() {
        Route::get('/admin/manage', [ResignationController::class, 'adminView'])->name('resignation.admin');
    });
});

// Internal API Routes (Moved from api.php to use Web Session Auth)
Route::prefix('api/v1/resignation')->middleware(['auth',"subscription"])->group(function() {
    // Employee Routes
    Route::post('/apply', [ResignationController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/my', [ResignationController::class, 'index']);
    Route::delete('/{id}/withdraw', [ResignationController::class, 'destroy']);

    // Admin/Manager Routes
    Route::middleware('role:admin|hr|Manager|CEO|Director')->group(function() {
        Route::post('/{id}/action', [ResignationController::class, 'action']);
        Route::post('/{id}/approve', [ResignationController::class, 'approve']); // Author: Sanket
        Route::post('/{id}/reject', [ResignationController::class, 'reject']); // Author: Sanket
    });

    Route::middleware('role:admin|hr|CEO')->group(function() {
        Route::get('/all', [ResignationController::class, 'adminIndex']);
        Route::post('/policy', [ResignationController::class, 'updatePolicy']);
        // Author: Sanket - Admin delete functionality for resignation requests
        Route::delete('/{id}/admin-delete', [ResignationController::class, 'adminDelete']);
    });

    Route::middleware('role:Manager|CEO|Director|admin|hr')->group(function() {
        Route::get('/team', [ResignationController::class, 'teamIndex']);
    });
    
    // Policy (Public fetch for employees to see how it works)
    Route::get('/policy', [ResignationController::class, 'getPolicy']);
});
