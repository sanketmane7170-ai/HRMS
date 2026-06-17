<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Resignation\Http\Controllers\ResignationController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
*/

// Routes moved to web.php to use session authentication for internal Blade views
// Route::prefix('v1/resignation')->middleware('auth:sanctum')->group(function() {
//     // Employee Routes
//     Route::post('/apply', [ResignationController::class, 'store'])->middleware('throttle:6,1');
//     Route::get('/my', [ResignationController::class, 'index']);
//     Route::delete('/{id}/withdraw', [ResignationController::class, 'destroy']);

//     // Admin/Manager Routes
//     Route::middleware('role:admin|hr|Manager|CEO|Director')->group(function() {
//         Route::post('/{id}/action', [ResignationController::class, 'action']);
//     });

//     Route::middleware('role:admin|hr|CEO')->group(function() {
//         Route::get('/all', [ResignationController::class, 'adminIndex']);
//         Route::post('/policy', [ResignationController::class, 'updatePolicy']);
//     });

//     Route::middleware('role:Manager|CEO|Director|admin|hr')->group(function() {
//         Route::get('/team', [ResignationController::class, 'teamIndex']);
//     });
    
//     // Policy (Public fetch for employees to see how it works)
//     Route::get('/policy', [ResignationController::class, 'getPolicy']);
// });
