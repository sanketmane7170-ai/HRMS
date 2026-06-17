<?php
use Illuminate\Support\Facades\Route;
use Modules\FileManager\Http\Controllers\FileManagerController;

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
    Route::resource('filemanager', FileManagerController::class);
    Route::any('filemanager-update/{filemanager}','FileManagerController@update')->name('filemanager.fupdate');
    Route::get('download-file/{id}','FileManagerController@download')->name('filemanager.download');
    Route::get('ExportSampleToFileDetails', [FileManagerController::class, 'ExportSampleToFileDetails'])->name('ExportSampleToFileDetails');
    Route::post('updateFileDetailsToExcel', [FileManagerController::class, 'updateFileDetailsToExcel'])->name('updateFileDetailsToExcel');

    Route::get('branch/{id}/file','FileManagerController@getfilesbyid')->name('filemanager.file');
    Route::post('branch/file/store','FileManagerController@branchfilestore')->name('filemanager.store');
    Route::get('branch/file/delete/{id}','FileManagerController@branchfiledelete')->name('filemanager.delete');

});