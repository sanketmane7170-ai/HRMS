<?php

use Illuminate\Support\Facades\Route;
use Modules\Asset\Http\Controllers\AssetController;
use Modules\Asset\Http\Controllers\AssetManufacturerController;
use Modules\Asset\Http\Controllers\AssetTypeController;
use Modules\Asset\Http\Controllers\Select2Controller;

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
    Route::resource('asset-types', AssetTypeController::class)->except('show');
    Route::resource('asset-manufacturers', AssetManufacturerController::class)->except('show');
    Route::post('asset/{asset}/un-assign', [AssetController::class, 'unassignAssetForm'])->name('asset.un-assign-form');
    Route::post('asset/{asset}/un-assign-user', [AssetController::class, 'unassignAsset'])->name('asset.un-assign.user');
    Route::post('asset/assign-user/{user?}', [AssetController::class, 'assignUser'])->name('asset.assign-user');
    Route::get('asset/assign-user/{user?}', [AssetController::class, 'assignUserForm'])->name('asset.assign-user-form');
    Route::resource('asset', AssetController::class);


    Route::as('employee.')->group(function () {
        Route::get('my-assets', EmployeeAssetController::class)->name('assets.index');
    });
});
Route::as('ajax.')->prefix('ajax')->group(function () {
    Route::as('select2.')->prefix('select2')->group(function () {
        Route::get('assets', [Select2Controller::class, 'getOpenAssets'])->name('fetch.assets-open');
        Route::get('asset-types', [Select2Controller::class, 'getAssetTypes'])->name('fetch.asset-types');
        Route::get('asset-manufacturers', [Select2Controller::class, 'getAssetManufacturers'])->name('fetch.asset-manufacturers');
    });
});
