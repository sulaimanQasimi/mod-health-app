<?php

use App\Http\Controllers\Api\SelectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Nurse Notes API Routes (JSON helpers only; CRUD lives in web + react routes)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('nurse-notes/for-record', [\App\Http\Controllers\NurseNoteController::class, 'getNotesForRecord'])
        ->name('api.nurse-notes.for-record');
    Route::get('nurse-notes/by-date-range', [\App\Http\Controllers\NurseNoteController::class, 'getNotesByDateRange'])
        ->name('api.nurse-notes.by-date-range');
});

// Medication Administration Records API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('medication-administration-records', \App\Http\Controllers\MedicationAdministrationRecordController::class)
        ->names('api.medication-administration-records');
    Route::get('medication-administration-records/for-morphable', [\App\Http\Controllers\MedicationAdministrationRecordController::class, 'getRecordsForMorphable']);
    Route::post('medication-administration-records/{medicationAdministrationRecord}/add-time', [\App\Http\Controllers\MedicationAdministrationRecordController::class, 'addAdministrationTime']);
    Route::delete('medication-administration-times/{administrationTime}', [\App\Http\Controllers\MedicationAdministrationRecordController::class, 'removeAdministrationTime']);
});

// Vital Sign Types API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('vital-sign-types', \App\Http\Controllers\VitalSignTypeController::class)
        ->names('api.vital-sign-types');
});

// Vital Signs API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('vital-signs', \App\Http\Controllers\VitalSignController::class)
        ->names('api.vital-signs');
    Route::get('vital-signs/for-morphable', [\App\Http\Controllers\VitalSignController::class, 'getVitalSignsForMorphable']);
});


// Note: Select2 API routes moved to web.php for proper web authentication
// These routes are now available at /api/select/* with auth middleware