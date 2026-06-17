<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\ProjectController;
use Modules\Task\Http\Controllers\TaskCommentController;
use Modules\Task\Http\Controllers\TaskController;

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

Route::middleware(["auth", "subscription"])->as('backend.')->group(function () {
    Route::resource('task', TaskController::class);
    Route::post('/tasks/{task}/comments', [TaskController::class, 'task_comment_store'])->name('task.comments.store');
    Route::delete('/comments/{comment}', [TaskController::class, 'task_comment_destroy'])->name('task.comments.destroy');
    Route::get('/my_task', [TaskController::class, 'my_task'])->name('task.my_task');
    Route::get('task/{task}/add-user', [TaskController::class, 'addUserForm'])->name('task.user.add.form');
    Route::post('task/{task}/store-user', [TaskController::class, 'addUser'])->name('task.user.add');
});


Route::middleware(["auth", "subscription"])->as('backend.')->group(function () {
    Route::as('employee.')->group(function () {
        Route::get('/my-task', [TaskController::class, 'my_task'])->name('task.my_task');
        Route::post('/create-task', [TaskController::class, 'create_my_task'])->name('task.create_my_task');
        Route::post('tasks/{task}/update-status', [TaskController::class, 'updateStatus']);
    });
    
});
