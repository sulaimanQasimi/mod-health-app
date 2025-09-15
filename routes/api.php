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

// Nurse Notes API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('nurse-notes', \App\Http\Controllers\NurseNoteController::class);
    Route::get('nurse-notes/for-record', [\App\Http\Controllers\NurseNoteController::class, 'getNotesForRecord']);
    Route::get('nurse-notes/by-date-range', [\App\Http\Controllers\NurseNoteController::class, 'getNotesByDateRange']);
});

// Medication Administration Records API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('medication-administration-records', \App\Http\Controllers\MedicationAdministrationRecordController::class);
    Route::get('medication-administration-records/for-morphable', [\App\Http\Controllers\MedicationAdministrationRecordController::class, 'getRecordsForMorphable']);
    Route::post('medication-administration-records/{medicationAdministrationRecord}/add-time', [\App\Http\Controllers\MedicationAdministrationRecordController::class, 'addAdministrationTime']);
    Route::delete('medication-administration-times/{administrationTime}', [\App\Http\Controllers\MedicationAdministrationRecordController::class, 'removeAdministrationTime']);
});

// Note: Select2 API routes moved to web.php for proper web authentication
// These routes are now available at /api/select/* with auth middleware