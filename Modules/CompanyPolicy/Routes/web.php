<?php

use Illuminate\Support\Facades\Route;
use Modules\CompanyPolicy\Http\Controllers\CompanyPolicyController;
use Modules\CompanyPolicy\Http\Controllers\EMPCompanyPolicyController;

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

    Route::get('getCompanyPolicy', [CompanyPolicyController::class, 'getCompanyPolicy'])->name('getCompanyPolicy');
    Route::get('addCompanyPolicy', [CompanyPolicyController::class, 'addCompanyPolicy'])->name('addCompanyPolicy');
    Route::post('storeCompanyPolicy', [CompanyPolicyController::class, 'storeCompanyPolicy'])->name('storeCompanyPolicy');
    Route::get('editCompanyPolicy/{id}', [CompanyPolicyController::class, 'editCompanyPolicy'])->name('editCompanyPolicy');
    Route::post('updateCompanyPolicy/{id}', [CompanyPolicyController::class, 'updateCompanyPolicy'])->name('updateCompanyPolicy');
    Route::delete('deleteCompanyPolicy/{id}', [CompanyPolicyController::class, 'deleteCompanyPolicy'])->name('deleteCompanyPolicy');

    Route::get('userCompanyPolicy', [CompanyPolicyController::class, 'getUserCompanyPolicy'])->name('userCompanyPolicy');
    Route::get('showUserCompanyPolicy/{id}', [CompanyPolicyController::class, 'showUserCompanyPolicy'])->name('showUserCompanyPolicy');


    // emp 
    Route::as('employee.')->group(function () {
        Route::get('showCompanyPolicy', [EMPCompanyPolicyController::class, 'showCompanyPolicy'])->name('showCompanyPolicy');
        Route::get('viewCompanyPolicy/{id}', [EMPCompanyPolicyController::class, 'viewCompanyPolicy'])->name('viewCompanyPolicy');
    });
});
