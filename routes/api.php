<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\StatsController;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::get('/stats/overview', [StatsController::class, 'overview']);
Route::get('/stats/exams-by-type', [StatsController::class, 'examsByType']);
Route::get('/stats/scores-by-grade', [StatsController::class, 'scoresByGrade']);
// 🟦 محمي بالتوكن
Route::middleware('auth:sanctum')->group(function () {
        Route::post('/exams/generate', [ExamController::class, 'generateQuestions']);
    Route::post('/exams/evaluate', [ExamController::class, 'evaluate']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    // Route::get('/profile', function (Request $request) {
    //     return $request->user();
    // });
});
