<?php

use App\Http\Controllers\AnesthesiaController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DiabetesChartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HospitalizationController;
use App\Http\Controllers\ICUController;
use App\Http\Controllers\NurseNoteController;
use App\Http\Controllers\NutritionCareController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\OutcomeController;
use App\Http\Controllers\PACUController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientTestRegistrationController;
use App\Http\Controllers\PhysiotherapyReportController;
use App\Http\Controllers\TestResultController;
use App\Http\Controllers\VitalSignController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
    'confirm' => false,
]);

Route::middleware(['auth'])->group(function () {
    Route::get('change_language/{lang?}', [HomeController::class, 'changeLanguage'])
        ->name('change_language');

    // Thin document endpoints still used by the React/Inertia app (print / PDF / webcam).
    // Registered before react.php so static path segments win over {model} wildcards.
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/print-card/{patient}', [PatientController::class, 'printCard'])->name('print-card');
        Route::get('webcam/{patient}', [PatientController::class, 'webcam'])->name('webcam');
        Route::post('capture/{id}', [PatientController::class, 'addImage'])->name('capture');
        Route::match(['get', 'post'], 'export-report', [PatientController::class, 'exportReport'])->name('export-report');
    });

    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::post('export-report', [AppointmentController::class, 'exportReport'])->name('export-report');
    });

    Route::prefix('hospitalizations')->name('hospitalizations.')->group(function () {
        Route::post('export-report', [HospitalizationController::class, 'exportReport'])->name('export-report');
    });

    Route::prefix('icus')->name('icus.')->group(function () {
        Route::post('export-report', [ICUController::class, 'exportReport'])->name('export-report');
        Route::get('/print-death-card/{icu}', [ICUController::class, 'printDeathCard'])->name('print-death-card');
        Route::get('/print-move-card/{icu}', [ICUController::class, 'printMoveCard'])->name('print-move-card');
    });

    Route::prefix('pacus')->name('pacus.')->group(function () {
        Route::post('export-report', [PACUController::class, 'exportReport'])->name('export-report');
    });

    Route::prefix('anesthesias')->name('anesthesias.')->group(function () {
        Route::post('export-report', [AnesthesiaController::class, 'exportReport'])->name('export-report');
    });

    Route::prefix('operations')->name('operations.')->group(function () {
        Route::post('export-report', [OperationController::class, 'exportReport'])->name('export-report');
    });

    Route::prefix('outcomes')->name('outcomes.')->group(function () {
        Route::post('export-index-report', [OutcomeController::class, 'exportIndexReport'])->name('export-index-report');
    });

    Route::prefix('laboratory')->name('laboratory.')->group(function () {
        Route::post('registrations/export-report', [PatientTestRegistrationController::class, 'exportReport'])
            ->name('registrations.export-report');
        Route::post('registrations/export-report-detailed', [PatientTestRegistrationController::class, 'exportReportDetailed'])
            ->name('registrations.export-report-detailed');
        Route::get('reports/print-group/{category_id}', [TestResultController::class, 'printGroupedTests'])
            ->name('reports.print-group');
    });

    Route::prefix('nurse-notes')->name('nurse-notes.')->group(function () {
        Route::get('print', [NurseNoteController::class, 'print'])->name('print');
    });

    Route::prefix('vital-signs')->name('vital-signs.')->group(function () {
        Route::get('print/{morphable_type}/{morphable_id}', [VitalSignController::class, 'print'])->name('print');
    });

    Route::prefix('diabetes-charts')->name('diabetes-charts.')->group(function () {
        Route::get('print', [DiabetesChartController::class, 'print'])->name('print');
    });

    Route::get('nutrition-cares/{nutritionCare}/print', [NutritionCareController::class, 'print'])
        ->name('nutrition-cares.print');

    Route::prefix('physiotherapy-reports')->name('physiotherapy-reports.')->group(function () {
        Route::post('export', [PhysiotherapyReportController::class, 'exportReport'])->name('export');
    });
});

// React/Inertia app at / (applies its own auth middleware)
include __DIR__.'/react.php';

// Backward-compatible redirects from old /react/* URLs
Route::redirect('/react', '/');
Route::get('/react/{any}', function (string $any) {
    return redirect('/'.$any);
})->where('any', '.*');
