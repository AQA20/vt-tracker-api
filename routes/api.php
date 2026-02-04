<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EngineeringSubmissionController;

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('engineering-submissions')->group(function () {
        Route::get('/', [EngineeringSubmissionController::class, 'index']);
        Route::post('/', [EngineeringSubmissionController::class, 'store']);
        Route::post('/import', [EngineeringSubmissionController::class, 'import']);
        Route::get('/export', [EngineeringSubmissionController::class, 'export']);
        Route::get('/{cse}', [EngineeringSubmissionController::class, 'show']);
        Route::put('/{cse}', [EngineeringSubmissionController::class, 'update']);
        
        // PDF Upload/Delete
        Route::post('/{cse}/status-pdfs/{field}', [EngineeringSubmissionController::class, 'uploadStatusPdf']);
        Route::delete('/{cse}/status-pdfs/{field}', [EngineeringSubmissionController::class, 'deleteStatusPdf']);
    });

    // Projects
    Route::get('/projects', [App\Http\Controllers\ProjectController::class, 'index']);
    Route::post('/projects', [App\Http\Controllers\ProjectController::class, 'store']);
    Route::get('/projects/{project}', [App\Http\Controllers\ProjectController::class, 'show']);
    Route::put('/projects/{project}', [App\Http\Controllers\ProjectController::class, 'update']);

    // Units
    Route::get('/projects/{project}/units', [App\Http\Controllers\UnitController::class, 'index']);
    Route::post('/projects/{project}/units', [App\Http\Controllers\UnitController::class, 'store']);
    Route::get('/units/{unit}', [App\Http\Controllers\UnitController::class, 'show']);
    Route::put('/units/{unit}', [App\Http\Controllers\UnitController::class, 'update']);

    // Stages
    Route::get('/units/{unit}/stages', [App\Http\Controllers\StageController::class, 'index']);
    Route::get('/stages/{stage}', [App\Http\Controllers\StageController::class, 'show']);
    Route::put('/stages/{stage}', [App\Http\Controllers\StageController::class, 'update']);

    // Tasks
    Route::get('/stages/{stage}/tasks', [App\Http\Controllers\TaskController::class, 'index']);
    Route::put('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'update']);

    // Ride Comfort
    Route::get('/units/{unit}/ride-comfort', [App\Http\Controllers\RideComfortController::class, 'index']);
    Route::post('/units/{unit}/ride-comfort', [App\Http\Controllers\RideComfortController::class, 'store']);

    // Progress
    Route::get('/units/{unit}/progress', [App\Http\Controllers\ProgressController::class, 'show']);
});
