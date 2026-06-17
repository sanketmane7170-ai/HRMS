<?php

use Illuminate\Support\Facades\Route;
use Modules\GeneralRequest\Http\Controllers\GeneralRequestController;


Route::middleware('auth',"subscription")->as('backend.')->group(function () {
    Route::get('general', [GeneralRequestController::class, 'show'])->name('general');
    Route::get('general/create', [GeneralRequestController::class, 'create'])->name('general.create');
    Route::post('general/save', [GeneralRequestController::class, 'store'])->name('general.store');

    Route::get('general/edit/{id}', [GeneralRequestController::class, 'edit'])->name('general.edit');
    Route::post('general/update/{id}', [GeneralRequestController::class, 'update'])->name('general.update');

    Route::delete('general/remove/{id}', [GeneralRequestController::class, 'destroy'])->name('general.remove');

    // general request
    Route::get('admin/generalRequest', [GeneralRequestController::class, 'showRequest'])->name('admin.generalRequest');

    Route::get('admin/generalRequest/create', [GeneralRequestController::class, 'createRequest'])->name('admin.generalRequest.create');
    Route::post('admin/generalRequest/store', [GeneralRequestController::class, 'storeRequest'])->name('admin.generalRequest.store');

    Route::get('admin/generalRequest/edit/{id}', [GeneralRequestController::class, 'editRequest'])->name('admin.generalRequest.edit');
    Route::post('admin/generalRequest/update/{id}', [GeneralRequestController::class, 'updateRequest'])->name('admin.generalRequest.update');
    
    Route::get('generalRequest/approved/{id}', [GeneralRequestController::class, 'approvedRequest'])->name('admin.generalRequest.approved');
    Route::get('generalRequest/rejected/{id}', [GeneralRequestController::class, 'rejectedRequest'])->name('admin.generalRequest.rejected');

    Route::delete('generalRequest/remove/{id}', [GeneralRequestController::class, 'removeRequest'])->name('admin.generalRequest.remove');

    Route::get('generalRequest/generalRequestTransaction', [GeneralRequestController::class, 'generalRequestTransaction'])->name('generalRequest.generalRequestTransaction');

});
