<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\ApiErrorLogController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware(["subscription"])->group(function () {
Route::post('getEmployees', [ApiController::class, 'getEmployees'])->name('getEmployees');
Route::post('performCheckInCheckOut', [ApiController::class, 'performCheckInCheckOut'])->name('performCheckInCheckOut');
Route::post('performBreakInBreakOut', [ApiController::class, 'performBreakInBreakOut'])->name('performBreakInBreakOut');

Route::get('getdepartments', [ApiController::class, 'getDepartments'])->name('getDepartments');
Route::post('getdepartments', [ApiController::class, 'getDepartments'])->name('getDepartments');
// Route::get('getwarninglist', [ApiController::class, 'getWarningList'])->name('getWarningList');
// Route::post('updateacknowledgement', [ApiController::class, 'updateAcknowledgement'])->name('updateAcknowledgement');

Route::get('portal/settings', [ApiController::class, 'portalSettings'])->name('portalSettings');
Route::post('/log-api-error', [ApiController::class, 'logError']);

});





// Route::prefix('v1')->group(function () {
//     Route::middleware(['auth:sanctum', 'throttle:1000,1'])->group(function () {
//         Route::prefix('{unique_code}/{user_id}')->middleware(['handle.portal.user'])->group(function () {
//             Route::post('getEmployees', [ApiController::class, 'getEmployees'])->name('getEmployees');
//             Route::post('performCheckInCheckOut', [ApiController::class, 'performCheckInCheckOut'])->name('performCheckInCheckOut');

//             Route::get('getdepartments', [ApiController::class, 'getDepartments'])->name('getDepartments');
//             // Route::get('getwarninglist', [ApiController::class, 'getWarningList'])->name('getWarningList');
//             // Route::post('updateacknowledgement', [ApiController::class, 'updateAcknowledgement'])->name('updateAcknowledgement');

//             Route::get('portal/settings', [ApiController::class, 'portalSettings'])->name('portalSettings');
//             Route::post('/log-api-error', [ApiController::class, 'logError']);
//         });
//     });
// });
