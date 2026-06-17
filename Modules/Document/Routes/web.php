<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentRequestController;
use Modules\Document\Http\Controllers\DocumentTypeController;
use Modules\Document\Http\Controllers\EmployeeDocumentRequestController;

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
    Route::resource('document-types', DocumentTypeController::class)->except('show');
    Route::get('document-requests/{documentRequest}/download', [DocumentRequestController::class, 'download'])->name('document-requests.download');
    Route::post('document-requests/{documentRequest}/generate', [DocumentRequestController::class, 'generate'])->name('document-requests.generate');
    Route::get('document-requests/{documentRequest}/preview', [DocumentRequestController::class, 'preview'])->name('document-requests.preview');
    Route::get('document-requests/{documentRequest}/reject', [DocumentRequestController::class, 'reject'])->name('document-requests.reject');
    Route::resource('document-requests', DocumentRequestController::class)->except('store', 'create');

    Route::as('employee.')->prefix('employee')->group(function () {
        Route::get('my-document-requests/{documentRequest}/download', [EmployeeDocumentRequestController::class, 'download'])->name('document-requests.download');
        Route::post('/document-type/count', [EmployeeDocumentRequestController::class, 'getDocumentTypeCount'])
            ->name('document-type.count');

        Route::resource('my-document-requests', EmployeeDocumentRequestController::class, [
            'names' => 'document-requests',
            'parameters' => [
                'my-document-requests' => 'document-request'
            ]
        ]);
    });
});
