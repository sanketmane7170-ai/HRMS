<?php

use Illuminate\Support\Facades\Route;
use Modules\PerformanceReview\Http\Controllers\IncrementCriteriaController;
use Modules\PerformanceReview\Http\Controllers\KeyPerformanceIndicatorController;
use Modules\PerformanceReview\Http\Controllers\KpiAssignmentController;
use Modules\PerformanceReview\Http\Controllers\KpiScoreLevelController;
use Modules\PerformanceReview\Http\Controllers\ReviewDurationController;
use Modules\PerformanceReview\Http\Controllers\QuestionSetController;
use Modules\PerformanceReview\Http\Controllers\QuestionController;
use Modules\PerformanceReview\Http\Controllers\PerformanceReviewController;
use Modules\PerformanceReview\Http\Controllers\ReviewEvaluationController;
use Modules\PerformanceReview\Http\Controllers\ScoreCriterionController;

// Prefix for the module (optional if handled by main app routes)
Route::middleware(['auth',"subscription"])->prefix('performance-review')->group(function () {

    // Review Durations
    Route::resource('review-duration', ReviewDurationController::class)->names('reviewduration');
    Route::resource('question-set', QuestionSetController::class)->names('questionset');
    Route::resource('score-criteria', ScoreCriterionController::class)->names('scorecriterion');
    Route::resource('question', QuestionController::class)->names('question');
    Route::resource('performancereview', PerformanceReviewController::class)->names('performancereview');
    Route::resource('increment-criteria', IncrementCriteriaController::class)->names('incrementcriteria');
    Route::resource('kpis', KeyPerformanceIndicatorController::class)->names('kpi');
    Route::get('kpi-assignments', [KpiAssignmentController::class, 'index'])->name('kpi.assignments.index');
    Route::resource('kpi-score-levels', KpiScoreLevelController::class)->names('kpi.scorelevels');
    Route::get('kpi/assignments/{assignment}/score', [KpiAssignmentController::class, 'score'])->name('kpi.assignments.score');
    Route::post('kpi/assignments/{assignment}/score', [KpiAssignmentController::class, 'submitScore'])->name('kpi.assignments.submitScore');



    Route::get('reviews', [ReviewEvaluationController::class, 'index'])->name('evaluate.index');
    Route::get('reviews/{review_id}/user/{user_id}', [ReviewEvaluationController::class, 'view'])->name('evaluate.view');
    Route::post('reviews/{review_id}/user/{user_id}', [ReviewEvaluationController::class, 'storeScore'])->name('evaluate.submit');
    Route::post('/{review}/hr-submit/{user}', [ReviewEvaluationController::class, 'submitHrReview'])->name('evaluate.hr.submit');

    Route::post('importQuesFromExcel', [QuestionController::class, 'importQuesFromExcel'])->name('importQuesFromExcel');
    Route::get('samplequesexportexcel', [QuestionController::class, 'samplequesexportexcel'])->name('samplequesexportexcel');
});
