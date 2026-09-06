<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the framework and assigned to the "api"
| middleware group.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('nurse-notes/for-record', [\App\Http\Controllers\NurseNoteController::class, 'getNotesForRecord'])
        ->name('api.nurse-notes.for-record');
    Route::get('nurse-notes/by-date-range', [\App\Http\Controllers\NurseNoteController::class, 'getNotesByDateRange'])
        ->name('api.nurse-notes.by-date-range');

    Route::apiResource('vital-signs', \App\Http\Controllers\VitalSignController::class)
        ->names('api.vital-signs');
    Route::get('vital-signs/for-morphable', [\App\Http\Controllers\VitalSignController::class, 'getVitalSignsForMorphable']);
});
