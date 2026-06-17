<?php

use Illuminate\Support\Facades\Route;
use Modules\Warning\Http\Controllers\EmployeeWarningController;
use Modules\Warning\Http\Controllers\WarningController;
use Modules\Warning\Http\Controllers\UserIncrementgController;
use Modules\Warning\Http\Controllers\UserPromotionController;

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
    Route::get('user-warnings/{userWarning}/generate-document', [WarningController::class, 'generate'])->name('user-warning.generate.document');
    Route::get('performance-reviews', [WarningController::class, 'showReviews'])->name('showReviews');
    Route::resource('user-warnings', WarningController::class);

    Route::any('user-warnings/mydelete/{userWarning}', [WarningController::class, 'destroy'])->name('user-warnings.mydelete');

    Route::as('employee.')->group(function () {
        Route::resource('my-warnings', EmployeeWarningController::class, [
            'names' => 'user-warnings',
            'parameters' => [
                'my-warnings' => 'user-warning'
            ]
        ])->only('index', 'show');
    });

    // appreciation
    Route::get('user-appreciation', [WarningController::class, 'showAppreciation'])->name('user-appreciation');
    Route::get('appreciation-details/{id}', [WarningController::class, 'detailsAppreciation'])->name('appreciation.details');
    Route::get('appreciation-create', [WarningController::class, 'createAppreciation'])->name('appreciation.create');
    Route::post('appreciation-store', [WarningController::class, 'storeAppreciation'])->name('appreciation.store');
    Route::get('appreciation-edit/{id}', [WarningController::class, 'editAppreciation'])->name('appreciation.edit');
    Route::post('appreciation-update/{id}', [WarningController::class, 'updateAppreciation'])->name('appreciation.update');

    Route::any('user-appreciation/mydelete/{id}', [WarningController::class, 'appreciationDestroy'])->name('user-appreciation.mydelete');

    // user increment
    Route::get('user-increment', [UserIncrementgController::class, 'user_increment'])->name('user-increment');
    Route::get('increment-letter-details/{id}', [UserIncrementgController::class, 'increment_letter_details'])->name('increment_letter.details');
    Route::get('increment-letter-create', [UserIncrementgController::class, 'increment_letter_create'])->name('increment_letter.create');
    Route::post('increment-letter-store', [UserIncrementgController::class, 'store_increment_letter'])->name('increment_letter.store');
    Route::get('increment-letter-edit/{id}', [UserIncrementgController::class, 'edit_increment_letter'])->name('increment_letter.edit');
    Route::post('increment-letter-update/{id}', [UserIncrementgController::class, 'update_increment_letter'])->name('increment_letter.update');

    Route::any('increment-letter/delete/{id}', [UserIncrementgController::class, 'increment_letter_delete'])->name('increment_letter.delete');

    // user increment letter
    Route::get('user-increment-letter', [UserIncrementgController::class, 'user_increment_letter'])->name('user_increment_letter');
    Route::get('user-increment-letter-details/{id}', [UserIncrementgController::class, 'user_increment_letter_details'])->name('user_increment_letter.details');
    Route::get('user-increment-letter-create', [UserIncrementgController::class, 'user_increment_letter_create'])->name('user_increment_letter.create');
    Route::post('user-increment-letter-store', [UserIncrementgController::class, 'user_store_increment_letter'])->name('user_increment_letter.store');
    Route::get('user-increment-letter-edit/{id}', [UserIncrementgController::class, 'user_edit_increment_letter'])->name('user_increment_letter.edit');
    Route::post('user-increment-letter-update/{id}', [UserIncrementgController::class, 'user_update_increment_letter'])->name('user_increment_letter.update');
    Route::get('user-increment-letter-download/{id}', [UserIncrementgController::class, 'downloadIncrementLetter'])->name('user_increment_letter.download');
    
    Route::any('user-increment-letter/delete/{id}', [UserIncrementgController::class, 'user_increment_letter_delete'])->name('user_increment_letter.delete');

    // user promotion
    Route::get('user-promotion', [UserPromotionController::class, 'user_promotion'])->name('user-promotion');
    Route::get('details-promotion-letter-type/{id}', [UserPromotionController::class, 'details_promotion_letter_type'])->name('details_promotion_letter_type');
    Route::get('add-promotion-letter-type', [UserPromotionController::class, 'add_promotion_letter_type'])->name('add_promotion_letter_type');
    Route::post('store-promotion-letter-type', [UserPromotionController::class, 'store_promotion_letter_type'])->name('store_promotion_letter_type');
    Route::get('edit-promotion-letter-type/{id}', [UserPromotionController::class, 'edit_promotion_letter_type'])->name('edit_promotion_letter_type');
    Route::post('update-promotion-letter-type/{id}', [UserPromotionController::class, 'update_promotion_letter_type'])->name('update_promotion_letter_type');

    Route::any('delete-promotion-letter-type/{id}', [UserPromotionController::class, 'delete_promotion_letter_type'])->name('delete_promotion_letter_type');

    // user Promotion letter
    Route::get('user-promotion-letter', [UserPromotionController::class, 'user_promotion_letter'])->name('user_promotion_letter');
    Route::get('user-promotion-letter-create', [UserPromotionController::class, 'user_promotion_letter_create'])->name('user_promotion_letter_create');
    Route::post('user-promotion-letter-store', [UserPromotionController::class, 'user_promotion_letter_store'])->name('user_promotion_letter_store');
    Route::get('user-promotion-letter-edit/{id}', [UserPromotionController::class, 'user_promotion_letter_edit'])->name('user_promotion_letter_edit');
    Route::post('user-promotion-letter-update/{id}', [UserPromotionController::class, 'user_promotion_letter_update'])->name('user_promotion_letter_update');
    Route::get('user-promotion-letter-details/{id}', [UserPromotionController::class, 'user_promotion_letter_details'])->name('user_promotion_letter_details');
    Route::get('user-promotion-letter-download/{id}', [UserPromotionController::class, 'user_promotion_letter_download'])->name('user_promotion_letter_download');
    
    Route::any('user-promotion-letter-delete/{id}', [UserPromotionController::class, 'user_promotion_letter_delete'])->name('user_promotion_letter_delete');
});
