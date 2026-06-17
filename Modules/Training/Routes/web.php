<?php

use Illuminate\Support\Facades\Route;
use Modules\Training\Http\Controllers\TrainingChatController;
use Modules\Training\Http\Controllers\TrainingController;

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
    Route::resource('training', TrainingController::class);

    Route::post('training/{training}/chat', [TrainingChatController::class, 'store'])->name('training.chat.ask');
    Route::post('training/chat/{chat}/reply', [TrainingChatController::class, 'reply'])->name('training.chat.reply');
    Route::get('training/{training}/questions', [TrainingController::class, 'manageQuestions'])->name('training.questions');
    Route::post('training/{training}/questions', [TrainingController::class, 'storeQuestion'])->name('training.questions.store');
    Route::get('training/{training}/certificate', [TrainingController::class, 'showQuestions'])->name('training.qa');
    Route::post('training/{training}/submit-answers', [TrainingController::class, 'storeQAAnswers'])->name('training.qa.store');
    Route::get('training/{training}/user-scores', [TrainingController::class, 'userScores'])->name('training.user.scores');
    Route::get('training/{training}/printcertificate', [TrainingController::class, 'generate'])->name('training.certificate');
    Route::delete('training/{training}/questions/{question}', [TrainingController::class, 'questionsdestroy'])
    ->name('backend.training.questions.destroy');




});
