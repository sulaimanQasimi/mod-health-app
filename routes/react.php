<?php

use App\Http\Controllers\V1\ActivityLogController;
use App\Http\Controllers\V1\AnesthesiaController;
use App\Http\Controllers\V1\AppointmentSections\AdviceController;
use App\Http\Controllers\V1\AppointmentSections\AnesthesiaController as AppointmentAnesthesiaController;
use App\Http\Controllers\V1\AppointmentSections\BloodBankController as AppointmentBloodBankController;
use App\Http\Controllers\V1\AppointmentSections\ConsultationController as AppointmentConsultationController;
use App\Http\Controllers\V1\AppointmentSections\DentistController as AppointmentDentistController;
use App\Http\Controllers\V1\AppointmentSections\DiagnosisController;
use App\Http\Controllers\V1\AppointmentSections\HospitalizationCheckupController;
use App\Http\Controllers\V1\AppointmentSections\HospitalizationController as AppointmentHospitalizationController;
use App\Http\Controllers\V1\AppointmentSections\HospitalizationVisitsController;
use App\Http\Controllers\V1\AppointmentSections\IcuController as AppointmentIcuController;
use App\Http\Controllers\V1\AppointmentSections\IcuVisitsController;
use App\Http\Controllers\V1\AppointmentSections\LabTestController;
use App\Http\Controllers\V1\AppointmentSections\NephrologyController as AppointmentNephrologyController;
use App\Http\Controllers\V1\AppointmentSections\OperationController as AppointmentOperationController;
use App\Http\Controllers\V1\AppointmentSections\OphthalmologyController as AppointmentOphthalmologyController;
use App\Http\Controllers\V1\AppointmentSections\EyeGlassesController as AppointmentEyeGlassesController;
use App\Http\Controllers\V1\AppointmentSections\PacuController as AppointmentPacuController;
use App\Http\Controllers\V1\AppointmentSections\PhysiotherapyController as AppointmentPhysiotherapyController;
use App\Http\Controllers\V1\AppointmentSections\PrescriptionController as AppointmentPrescriptionController;
use App\Http\Controllers\V1\AppointmentSections\ProstheticsController as AppointmentProstheticsController;
use App\Http\Controllers\V1\AppointmentSections\ReferDepartmentController;
use App\Http\Controllers\V1\AppointmentSections\RelatedVisitsController;
use App\Http\Controllers\V1\AppointmentSections\UnderReviewController as AppointmentUnderReviewController;
use App\Http\Controllers\V1\AppointmentController;
use App\Http\Controllers\V1\BackupController;
use App\Http\Controllers\V1\BedController;
use App\Http\Controllers\V1\BloodBankController;
use App\Http\Controllers\V1\BloodUnitController;
use App\Http\Controllers\V1\BloodBranchTransferController;
use App\Http\Controllers\V1\BranchController;
use App\Http\Controllers\V1\ConsultationController;
use App\Http\Controllers\V1\DashboardController;
use App\Http\Controllers\V1\DentalChartController;
use App\Http\Controllers\V1\DentistRegistrationController;
use App\Http\Controllers\V1\DepartmentController;
use App\Http\Controllers\V1\DepotController;
use App\Http\Controllers\V1\DepotMovementController;
use App\Http\Controllers\V1\DepotReportController;
use App\Http\Controllers\V1\DepotRequestController;
use App\Http\Controllers\V1\DepotTransactionController;
use App\Http\Controllers\V1\DiseaseController;
use App\Http\Controllers\V1\DoctorController;
use App\Http\Controllers\V1\DoctorPerformanceReportController;
use App\Http\Controllers\V1\FloorController;
use App\Http\Controllers\V1\FoodTypeController;
use App\Http\Controllers\V1\HemodialysisSessionController;
use App\Http\Controllers\V1\HospitalizationController;
use App\Http\Controllers\V1\HospitalizationSections\DiabetesChartController as HospitalizationDiabetesChartController;
use App\Http\Controllers\V1\HospitalizationSections\AnesthesiaController as HospitalizationAnesthesiaController;
use App\Http\Controllers\V1\HospitalizationSections\IcuController as HospitalizationIcuController;
use App\Http\Controllers\V1\HospitalizationSections\PacuController as HospitalizationPacuController;
use App\Http\Controllers\V1\HospitalizationSections\RoomBedController as HospitalizationRoomBedController;
use App\Http\Controllers\V1\HospitalizationSections\UnderReviewController as HospitalizationUnderReviewController;
use App\Http\Controllers\V1\HospitalizationSections\OperationController as HospitalizationOperationController;
use App\Http\Controllers\V1\HospitalizationSections\NurseNoteController as HospitalizationNurseNoteController;
use App\Http\Controllers\V1\HospitalizationSections\NutritionCareController as HospitalizationNutritionCareController;
use App\Http\Controllers\V1\HospitalizationSections\VitalSignController as HospitalizationVitalSignController;
use App\Http\Controllers\V1\HospitalizationSections\VisitController as HospitalizationVisitController;
use App\Http\Controllers\V1\ICUController;
use App\Http\Controllers\V1\ICUSections\DailyProgressController as IcuDailyProgressController;
use App\Http\Controllers\V1\ICUSections\ProcedureController as IcuProcedureController;
use App\Http\Controllers\V1\ICUSections\VisitController as IcuVisitController;
use App\Http\Controllers\V1\IncomeController;
use App\Http\Controllers\V1\LabTypeController;
use App\Http\Controllers\V1\LaboratoryController;
use App\Http\Controllers\V1\MedicineController;
use App\Http\Controllers\V1\MedicineTypeController;
use App\Http\Controllers\V1\MedicineUsageTypeController;
use App\Http\Controllers\V1\MiliteryTypeController;
use App\Http\Controllers\V1\NephrologyRegistrationController;
use App\Http\Controllers\V1\NurseController;
use App\Http\Controllers\V1\OperationController;
use App\Http\Controllers\V1\OperationTypeController;
use App\Http\Controllers\V1\OphthalmologyRegistrationController;
use App\Http\Controllers\V1\EyeGlassesOrderController;
use App\Http\Controllers\V1\OutcomeController;
use App\Http\Controllers\V1\PACUController;
use App\Http\Controllers\V1\PatientController;
use App\Http\Controllers\V1\PatientSections\ForeignCountryReferralController;
use App\Http\Controllers\V1\PermissionController;
use App\Http\Controllers\V1\PharmacyController;
use App\Http\Controllers\V1\PharmacyFulfillmentController;
use App\Http\Controllers\V1\PharmacyTransferRequestController;
use App\Http\Controllers\V1\PhysiotherapyProcedureController;
use App\Http\Controllers\V1\PhysiotherapyReportController;
use App\Http\Controllers\V1\PhysiotherapyTypeController;
use App\Http\Controllers\V1\ProfileController;
use App\Http\Controllers\V1\PrescriptionController;
use App\Http\Controllers\V1\PrescriptionStockController;
use App\Http\Controllers\V1\ProcedureTypeController;
use App\Http\Controllers\V1\ProstheticCaseController;
use App\Http\Controllers\V1\ProstheticCatalogController;
use App\Http\Controllers\V1\ProstheticReferralController;
use App\Http\Controllers\V1\ProstheticStockController;
use App\Http\Controllers\V1\ProstheticsDashboardController;
use App\Http\Controllers\V1\ProstheticsReportController;
use App\Http\Controllers\V1\RecipientController;
use App\Http\Controllers\V1\RecipientPartController;
use App\Http\Controllers\V1\RelationController;
use App\Http\Controllers\V1\Reports\GenralReport;
use App\Http\Controllers\V1\RoleController;
use App\Http\Controllers\V1\RoomController;
use App\Http\Controllers\V1\ScanCodeController;
use App\Http\Controllers\V1\SectionController;
use App\Http\Controllers\V1\ToolController;
use App\Http\Controllers\V1\UnderReviewController;
use App\Http\Controllers\V1\UnitController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\VitalSignController;
use App\Http\Controllers\V1\VitalSignTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    Route::get('/scan-code', [ScanCodeController::class, 'index'])->name('scan-code');
    Route::post('/scan-code', [ScanCodeController::class, 'search'])->name('scan-code.search');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::match(['put', 'post'], '/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
    });

    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');
        Route::get('/districts/{provinceId}', [PatientController::class, 'districts'])->name('districts');
        Route::get('/recipient-parts/{recipientId}', [PatientController::class, 'recipientParts'])->name('recipient-parts');
        Route::get('/doctors-by-department/{departmentId}', [PatientController::class, 'doctorsByDepartment'])->name('doctors-by-department');
        Route::get('/report', [PatientController::class, 'report'])->name('report');
        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit');

        Route::prefix('{patient}')->name('sections.')->group(function () {
            Route::get('foreign-country-referral/meta', [ForeignCountryReferralController::class, 'meta'])->name('foreign-country-referral.meta');
            Route::get('foreign-country-referral', [ForeignCountryReferralController::class, 'index'])->name('foreign-country-referral.index');
            Route::post('foreign-country-referral', [ForeignCountryReferralController::class, 'store'])->name('foreign-country-referral.store');
            Route::get('foreign-country-referral/{foreignCountryReferral}', [ForeignCountryReferralController::class, 'show'])->name('foreign-country-referral.show');
            Route::post('foreign-country-referral/{foreignCountryReferral}/attachments', [ForeignCountryReferralController::class, 'storeAttachments'])->name('foreign-country-referral.attachments.store');
            Route::delete('foreign-country-referral/items/{foreignCountryReferralItem}', [ForeignCountryReferralController::class, 'destroyItem'])->name('foreign-country-referral.items.destroy');
            Route::delete('foreign-country-referral/attachments/{foreignCountryReferralAttachment}', [ForeignCountryReferralController::class, 'destroyAttachment'])->name('foreign-country-referral.attachments.destroy');
            Route::delete('foreign-country-referral/{foreignCountryReferral}', [ForeignCountryReferralController::class, 'destroy'])->name('foreign-country-referral.destroy');
        });

        Route::get('/{patient}', [PatientController::class, 'show'])->name('show');
        Route::match(['put', 'post'], '/{patient}', [PatientController::class, 'update'])->name('update');
        Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/trashed', [AppointmentController::class, 'trashed'])->name('trashed');
        Route::get('/department-report', [AppointmentController::class, 'departmentReport'])->name('department-report');
        Route::get('/department', [AppointmentController::class, 'department'])->name('department');
        Route::get('/doctor', [AppointmentController::class, 'doctor'])->name('doctor');
        Route::get('/completed', [AppointmentController::class, 'completed'])->name('completed');
        Route::get('/report', [AppointmentController::class, 'report'])->name('report');
        Route::put('/{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::prefix('{appointment}')->name('sections.')->group(function () {
            Route::get('blood-bank', [AppointmentBloodBankController::class, 'index'])->name('blood-bank.index');
            Route::post('blood-bank', [AppointmentBloodBankController::class, 'store'])->name('blood-bank.store');
            Route::delete('blood-bank/{bloodBank}', [AppointmentBloodBankController::class, 'destroy'])->name('blood-bank.destroy');

            Route::get('diagnosis', [DiagnosisController::class, 'index'])->name('diagnosis.index');
            Route::post('diagnosis', [DiagnosisController::class, 'store'])->name('diagnosis.store');
            Route::put('diagnosis/{diagnose}', [DiagnosisController::class, 'update'])->name('diagnosis.update');
            Route::delete('diagnosis/{diagnose}', [DiagnosisController::class, 'destroy'])->name('diagnosis.destroy');

            Route::get('prescription/meta', [AppointmentPrescriptionController::class, 'meta'])->name('prescription.meta');
            Route::get('prescription', [AppointmentPrescriptionController::class, 'index'])->name('prescription.index');
            Route::post('prescription', [AppointmentPrescriptionController::class, 'store'])->name('prescription.store');
            Route::get('prescription/{prescription}', [AppointmentPrescriptionController::class, 'show'])->name('prescription.show');
            Route::post('prescription/items/{prescriptionItem}/status', [AppointmentPrescriptionController::class, 'updateItemStatus'])->name('prescription.items.status');
            Route::delete('prescription/items/{prescriptionItem}', [AppointmentPrescriptionController::class, 'destroyItem'])->name('prescription.items.destroy');
            Route::delete('prescription/{prescription}', [AppointmentPrescriptionController::class, 'destroy'])->name('prescription.destroy');

            Route::get('advice', [AdviceController::class, 'index'])->name('advice.index');
            Route::post('advice', [AdviceController::class, 'store'])->name('advice.store');
            Route::put('advice/{advice}', [AdviceController::class, 'update'])->name('advice.update');
            Route::delete('advice/{advice}', [AdviceController::class, 'destroy'])->name('advice.destroy');

            Route::get('lab-tests/meta', [LabTestController::class, 'meta'])->name('lab-tests.meta');
            Route::get('lab-tests', [LabTestController::class, 'index'])->name('lab-tests.index');
            Route::post('lab-tests', [LabTestController::class, 'store'])->name('lab-tests.store');
            Route::get('lab-tests/{registration}', [LabTestController::class, 'show'])->name('lab-tests.show');
            Route::get('hospitalization-checkups', [HospitalizationCheckupController::class, 'index'])->name('hospitalization-checkups.index');
            Route::get('consultations', [AppointmentConsultationController::class, 'index'])->name('consultations.index');
            Route::delete('consultations/{consultation}', [AppointmentConsultationController::class, 'destroy'])->name('consultations.destroy');
            Route::get('refer-department/meta', [ReferDepartmentController::class, 'meta'])->name('refer-department.meta');
            Route::get('refer-department', [ReferDepartmentController::class, 'index'])->name('refer-department.index');
            Route::post('refer-department', [ReferDepartmentController::class, 'store'])->name('refer-department.store');
            Route::get('under-review/meta', [AppointmentUnderReviewController::class, 'meta'])->name('under-review.meta');
            Route::get('under-review', [AppointmentUnderReviewController::class, 'index'])->name('under-review.index');
            Route::post('under-review', [AppointmentUnderReviewController::class, 'store'])->name('under-review.store');
            Route::delete('under-review/{underReview}', [AppointmentUnderReviewController::class, 'destroy'])->name('under-review.destroy');
            Route::get('related-visits', [RelatedVisitsController::class, 'index'])->name('related-visits.index');
            Route::get('hospitalization/meta', [AppointmentHospitalizationController::class, 'meta'])->name('hospitalization.meta');
            Route::get('hospitalization', [AppointmentHospitalizationController::class, 'index'])->name('hospitalization.index');
            Route::post('hospitalization', [AppointmentHospitalizationController::class, 'store'])->name('hospitalization.store');
            Route::delete('hospitalization/{hospitalization}', [AppointmentHospitalizationController::class, 'destroy'])->name('hospitalization.destroy');
            Route::get('hospitalization-visits', [HospitalizationVisitsController::class, 'index'])->name('hospitalization-visits.index');
            Route::get('anesthesia/meta', [AppointmentAnesthesiaController::class, 'meta'])->name('anesthesia.meta');
            Route::get('anesthesia', [AppointmentAnesthesiaController::class, 'index'])->name('anesthesia.index');
            Route::post('anesthesia', [AppointmentAnesthesiaController::class, 'store'])->name('anesthesia.store');
            Route::delete('anesthesia/{anesthesia}', [AppointmentAnesthesiaController::class, 'destroy'])->name('anesthesia.destroy');
            Route::get('operations/meta', [AppointmentOperationController::class, 'meta'])->name('operations.meta');
            Route::get('operations', [AppointmentOperationController::class, 'index'])->name('operations.index');
            Route::post('operations', [AppointmentOperationController::class, 'store'])->name('operations.store');
            Route::get('icu/meta', [AppointmentIcuController::class, 'meta'])->name('icu.meta');
            Route::get('icu', [AppointmentIcuController::class, 'index'])->name('icu.index');
            Route::post('icu', [AppointmentIcuController::class, 'store'])->name('icu.store');
            Route::delete('icu/{icu}', [AppointmentIcuController::class, 'destroy'])->name('icu.destroy');
            Route::get('pacus/meta', [AppointmentPacuController::class, 'meta'])->name('pacus.meta');
            Route::get('pacus', [AppointmentPacuController::class, 'index'])->name('pacus.index');
            Route::post('pacus', [AppointmentPacuController::class, 'store'])->name('pacus.store');
            Route::delete('pacus/{pacu}', [AppointmentPacuController::class, 'destroy'])->name('pacus.destroy');
            Route::get('icu-visits', [IcuVisitsController::class, 'index'])->name('icu-visits.index');
            Route::get('physiotherapy/meta', [AppointmentPhysiotherapyController::class, 'meta'])->name('physiotherapy.meta');
            Route::get('physiotherapy', [AppointmentPhysiotherapyController::class, 'index'])->name('physiotherapy.index');
            Route::post('physiotherapy', [AppointmentPhysiotherapyController::class, 'store'])->name('physiotherapy.store');
            Route::get('physiotherapy/{physiotherapyProcedure}', [AppointmentPhysiotherapyController::class, 'show'])->name('physiotherapy.show');
            Route::get('dentist/meta', [AppointmentDentistController::class, 'meta'])->name('dentist.meta');
            Route::get('dentist', [AppointmentDentistController::class, 'index'])->name('dentist.index');
            Route::post('dentist', [AppointmentDentistController::class, 'store'])->name('dentist.store');
            Route::get('dentist/{dentistRegistration}', [AppointmentDentistController::class, 'show'])->name('dentist.show');

            Route::get('ophthalmology/meta', [AppointmentOphthalmologyController::class, 'meta'])->name('ophthalmology.meta');
            Route::get('ophthalmology', [AppointmentOphthalmologyController::class, 'index'])->name('ophthalmology.index');
            Route::post('ophthalmology', [AppointmentOphthalmologyController::class, 'store'])->name('ophthalmology.store');
            Route::get('eye-glasses/meta', [AppointmentEyeGlassesController::class, 'meta'])->name('eye-glasses.meta');
            Route::get('eye-glasses', [AppointmentEyeGlassesController::class, 'index'])->name('eye-glasses.index');
            Route::post('eye-glasses', [AppointmentEyeGlassesController::class, 'store'])->name('eye-glasses.store');
            Route::get('nephrology', [AppointmentNephrologyController::class, 'index'])->name('nephrology.index');
            Route::post('nephrology', [AppointmentNephrologyController::class, 'store'])->name('nephrology.store');
            Route::get('nephrology/{nephrologyRegistration}', [AppointmentNephrologyController::class, 'show'])->name('nephrology.show');
            Route::get('prosthetics', [AppointmentProstheticsController::class, 'index'])->name('prosthetics.index');
            Route::post('prosthetics/referrals', [AppointmentProstheticsController::class, 'storeReferral'])->name('prosthetics.referrals.store');
            Route::get('prosthetics/referrals/{referral}', [AppointmentProstheticsController::class, 'showReferral'])->name('prosthetics.referrals.show');
        });
        Route::post('/{appointment}/accept', [AppointmentController::class, 'accept'])->name('accept');
        Route::post('/{appointment}/assign-doctor', [AppointmentController::class, 'assignDoctor'])->name('assign-doctor');
        Route::put('/{appointment}/change-department', [AppointmentController::class, 'changeDepartment'])->name('change-department');
        Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
        Route::post('/{appointment}/restore', [AppointmentController::class, 'restore'])->name('restore');
    });

    Route::get('/doctor-performance-report', [DoctorPerformanceReportController::class, 'performance'])->name('doctor-performance-report');

    Route::prefix('physiotherapy-procedures')->name('physiotherapy-procedures.')->group(function () {
        Route::get('/', [PhysiotherapyProcedureController::class, 'index'])->name('index');
        Route::get('/my-procedures', [PhysiotherapyProcedureController::class, 'myProcedures'])->name('my-procedures');
        Route::get('/{physiotherapyProcedure}', [PhysiotherapyProcedureController::class, 'show'])->name('show');
        Route::put('/{physiotherapyProcedure}', [PhysiotherapyProcedureController::class, 'update'])->name('update');
        Route::delete('/{physiotherapyProcedure}', [PhysiotherapyProcedureController::class, 'destroy'])->name('destroy');
        Route::post('/{physiotherapyProcedure}/update-counter', [PhysiotherapyProcedureController::class, 'updateCounter'])->name('update-counter');
        Route::post('/{physiotherapyProcedure}/reviews', [PhysiotherapyProcedureController::class, 'storeReview'])->name('reviews.store');
        Route::put('/{physiotherapyProcedure}/reviews/{review}', [PhysiotherapyProcedureController::class, 'updateReview'])->name('reviews.update');
        Route::delete('/{physiotherapyProcedure}/reviews/{review}', [PhysiotherapyProcedureController::class, 'destroyReview'])->name('reviews.destroy');
    });

    Route::get('/physiotherapy-reports', [PhysiotherapyReportController::class, 'index'])->name('physiotherapy-reports.index');
    Route::prefix('physiotherapy-types')->name('physiotherapy-types.')->group(function () {
        Route::get('/', [PhysiotherapyTypeController::class, 'index'])->name('index');
        Route::get('/create', [PhysiotherapyTypeController::class, 'create'])->name('create');
        Route::post('/', [PhysiotherapyTypeController::class, 'store'])->name('store');
        Route::get('/{physiotherapyType}/edit', [PhysiotherapyTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{physiotherapyType}', [PhysiotherapyTypeController::class, 'update'])->name('update');
        Route::delete('/{physiotherapyType}', [PhysiotherapyTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('dentist-registrations')->name('dentist-registrations.')->group(function () {
        Route::get('/', [DentistRegistrationController::class, 'index'])->name('index');
        Route::get('/{dentistRegistration}', [DentistRegistrationController::class, 'show'])->name('show');
        Route::put('/{dentistRegistration}', [DentistRegistrationController::class, 'update'])->name('update');
        Route::delete('/{dentistRegistration}', [DentistRegistrationController::class, 'destroy'])->name('destroy');
        Route::post('/{dentistRegistration}/mark-completed', [DentistRegistrationController::class, 'markCompleted'])->name('mark-completed');
        Route::post('/{dentistRegistration}/mark-in-progress', [DentistRegistrationController::class, 'markInProgress'])->name('mark-in-progress');
        Route::post('/{dentistRegistration}/cancel', [DentistRegistrationController::class, 'cancel'])->name('cancel');
        Route::post('/{dentistRegistration}/treatments', [DentistRegistrationController::class, 'storeTreatment'])->name('treatments.store');
        Route::delete('/{dentistRegistration}/treatments/{dentalTreatment}', [DentistRegistrationController::class, 'destroyTreatment'])->name('treatments.destroy');
        Route::post('/{dentistRegistration}/xrays', [DentistRegistrationController::class, 'storeXray'])->name('xrays.store');
        Route::delete('/{dentistRegistration}/xrays/{dentalXray}', [DentistRegistrationController::class, 'destroyXray'])->name('xrays.destroy');
        Route::post('/{dentistRegistration}/notes', [DentistRegistrationController::class, 'storeNote'])->name('notes.store');
        Route::delete('/{dentistRegistration}/notes/{dentalNote}', [DentistRegistrationController::class, 'destroyNote'])->name('notes.destroy');
    });

    Route::prefix('ophthalmology-registrations')->name('ophthalmology-registrations.')->group(function () {
        Route::get('/', [OphthalmologyRegistrationController::class, 'index'])->name('index');
        Route::get('/{ophthalmologyRegistration}/print', [OphthalmologyRegistrationController::class, 'print'])->name('print');
        Route::get('/{ophthalmologyRegistration}', [OphthalmologyRegistrationController::class, 'show'])->name('show');
        Route::match(['put', 'post'], '/{ophthalmologyRegistration}', [OphthalmologyRegistrationController::class, 'update'])->name('update');
        Route::delete('/{ophthalmologyRegistration}', [OphthalmologyRegistrationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('eye-glasses-orders')->name('eye-glasses-orders.')->group(function () {
        Route::get('/', [EyeGlassesOrderController::class, 'index'])->name('index');
        Route::get('/{eyeGlassesOrder}/print', [EyeGlassesOrderController::class, 'print'])->name('print');
        Route::get('/{eyeGlassesOrder}', [EyeGlassesOrderController::class, 'show'])->name('show');
        Route::match(['put', 'post'], '/{eyeGlassesOrder}', [EyeGlassesOrderController::class, 'update'])->name('update');
        Route::post('/{eyeGlassesOrder}/process', [EyeGlassesOrderController::class, 'process'])->name('process');
        Route::post('/{eyeGlassesOrder}/payment', [EyeGlassesOrderController::class, 'payment'])->name('payment');
        Route::post('/{eyeGlassesOrder}/deliver', [EyeGlassesOrderController::class, 'deliver'])->name('deliver');
        Route::post('/{eyeGlassesOrder}/cancel', [EyeGlassesOrderController::class, 'cancel'])->name('cancel');
        Route::delete('/{eyeGlassesOrder}', [EyeGlassesOrderController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('dental-charts')->name('dental-charts.')->group(function () {
        Route::get('/{dentistRegistration}', [DentalChartController::class, 'index'])->name('index');
        Route::get('/{dentistRegistration}/create', [DentalChartController::class, 'create'])->name('create');
        Route::post('/{dentistRegistration}', [DentalChartController::class, 'store'])->name('store');
        Route::get('/{dentistRegistration}/history', [DentalChartController::class, 'history'])->name('history');
        Route::get('/{dentistRegistration}/compare', [DentalChartController::class, 'compare'])->name('compare');
        Route::get('/{dentistRegistration}/print', [DentalChartController::class, 'print'])->name('print');
        Route::get('/{dentistRegistration}/export', [DentalChartController::class, 'export'])->name('export');
        Route::get('/entry/{dentalChart}/edit', [DentalChartController::class, 'edit'])->name('edit');
        Route::put('/entry/{dentalChart}', [DentalChartController::class, 'update'])->name('update');
        Route::delete('/entry/{dentalChart}', [DentalChartController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('nephrology-registrations')->name('nephrology-registrations.')->group(function () {
        Route::get('/', [NephrologyRegistrationController::class, 'index'])->name('index');
        Route::get('/{nephrologyRegistration}', [NephrologyRegistrationController::class, 'show'])->name('show');
        Route::put('/{nephrologyRegistration}', [NephrologyRegistrationController::class, 'update'])->name('update');
        Route::delete('/{nephrologyRegistration}', [NephrologyRegistrationController::class, 'destroy'])->name('destroy');
        Route::post('/{nephrologyRegistration}/mark-completed', [NephrologyRegistrationController::class, 'markCompleted'])->name('mark-completed');
        Route::post('/{nephrologyRegistration}/mark-in-progress', [NephrologyRegistrationController::class, 'markInProgress'])->name('mark-in-progress');
        Route::post('/{nephrologyRegistration}/accept', [NephrologyRegistrationController::class, 'accept'])->name('accept');
        Route::post('/{nephrologyRegistration}/cancel', [NephrologyRegistrationController::class, 'cancel'])->name('cancel');
    });
    Route::prefix('hemodialysis-sessions')->name('hemodialysis-sessions.')->group(function () {
        Route::get('/', [HemodialysisSessionController::class, 'index'])->name('index');
        Route::get('/create', [HemodialysisSessionController::class, 'create'])->name('create');
        Route::post('/', [HemodialysisSessionController::class, 'store'])->name('store');
        Route::get('/{hemodialysisSession}', [HemodialysisSessionController::class, 'show'])->name('show');
        Route::get('/{hemodialysisSession}/edit', [HemodialysisSessionController::class, 'edit'])->name('edit');
        Route::put('/{hemodialysisSession}', [HemodialysisSessionController::class, 'update'])->name('update');
        Route::delete('/{hemodialysisSession}', [HemodialysisSessionController::class, 'destroy'])->name('destroy');
    });
    Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');

    Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
        Route::get('/scan-code', [PrescriptionController::class, 'scanCode'])->name('scan-code');
        Route::post('/scan', [PrescriptionController::class, 'scan'])->name('scan');
        Route::get('/', [PrescriptionController::class, 'index'])->name('index');
        Route::get('/delivered', [PrescriptionController::class, 'delivered'])->name('delivered');
        Route::get('/report', [PrescriptionController::class, 'report'])->name('report');
        Route::get('/report/pharmacies/{pharmacy}/users', [PrescriptionController::class, 'reportPharmacyUsers'])->name('report.pharmacy-users');
        Route::post('/bulk-update-status', [PrescriptionController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::post('/bulk-delete', [PrescriptionController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/alternatives', [PrescriptionController::class, 'addAlternative'])->name('alternatives.store');
        Route::put('/alternatives/{alternativeItem}/select', [PrescriptionController::class, 'selectAlternative'])->name('alternatives.select');
        Route::put('/alternatives/{alternativeItem}/status', [PrescriptionController::class, 'updateAlternativeStatus'])->name('alternatives.status');
        Route::delete('/alternatives/{alternativeItem}', [PrescriptionController::class, 'deleteAlternative'])->name('alternatives.destroy');
        Route::put('/items/{prescriptionItem}/status', [PrescriptionController::class, 'updateItemStatus'])->name('items.status');
        Route::put('/items/{prescriptionItem}/amount', [PrescriptionController::class, 'updateItemAmount'])->name('items.amount');
        Route::get('/{prescription}', [PrescriptionController::class, 'show'])->name('show');
        Route::put('/{prescription}/status', [PrescriptionController::class, 'updateStatus'])->name('update-status');
        Route::post('/{prescription}/mark-all-delivered', [PrescriptionController::class, 'markAllDelivered'])->name('mark-all-delivered');
        Route::delete('/{prescription}', [PrescriptionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('pharmacies')->name('pharmacies.')->group(function () {
        Route::get('/', [PharmacyController::class, 'index'])->name('index');
        Route::get('/create', [PharmacyController::class, 'create'])->name('create');
        Route::post('/', [PharmacyController::class, 'store'])->name('store');
        Route::get('/{pharmacy}/manage-users', [PharmacyController::class, 'manageUsers'])->name('manage-users');
        Route::post('/{pharmacy}/users', [PharmacyController::class, 'addUser'])->name('users.store');
        Route::post('/{pharmacy}/users/remove', [PharmacyController::class, 'removeUser'])->name('users.remove');
        Route::match(['put', 'post'], '/{pharmacy}/users/{user}', [PharmacyController::class, 'updateUserRole'])->name('users.update');
        Route::get('/{pharmacy}', [PharmacyController::class, 'show'])->name('show');
        Route::get('/{pharmacy}/edit', [PharmacyController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{pharmacy}', [PharmacyController::class, 'update'])->name('update');
        Route::delete('/{pharmacy}', [PharmacyController::class, 'destroy'])->name('destroy');
    });
    Route::get('/prescription-stocks', [PrescriptionStockController::class, 'index'])->name('prescription-stocks.index');

    Route::get('/pharmacy-stock', [PharmacyFulfillmentController::class, 'stock'])->name('pharmacy-stock.index');

    Route::prefix('pharmacy/transfer-requests')->name('pharmacy.transfer-requests.')->group(function () {
        Route::get('/', [PharmacyTransferRequestController::class, 'index'])->name('index');
        Route::get('/create', [PharmacyTransferRequestController::class, 'create'])->name('create');
        Route::post('/', [PharmacyTransferRequestController::class, 'store'])->name('store');
        Route::get('/{depotRequest}/edit', [PharmacyTransferRequestController::class, 'edit'])->name('edit');
        Route::put('/{depotRequest}', [PharmacyTransferRequestController::class, 'update'])->name('update');
        Route::get('/{depotRequest}', [PharmacyTransferRequestController::class, 'show'])->name('show');
        Route::post('/{depotRequest}/submit', [PharmacyTransferRequestController::class, 'submit'])->name('submit');
        Route::post('/{depotRequest}/cancel', [PharmacyTransferRequestController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('incomes')->name('incomes.')->group(function () {
        Route::get('/', [IncomeController::class, 'index'])->name('index');
        Route::get('/create', [IncomeController::class, 'create'])->name('create');
        Route::post('/', [IncomeController::class, 'store'])->name('store');
    });

    Route::prefix('outcomes')->name('outcomes.')->group(function () {
        Route::get('/', [OutcomeController::class, 'index'])->name('index');
        Route::get('/report', [OutcomeController::class, 'report'])->name('report');
    });

    Route::prefix('depots')->name('depots.')->group(function () {
        Route::get('/', [DepotController::class, 'index'])->name('index');
        Route::get('/create', [DepotController::class, 'create'])->name('create');
        Route::post('/', [DepotController::class, 'store'])->name('store');
        Route::get('/stock/available', [DepotTransactionController::class, 'availableStock'])->name('stock.available');
        Route::get('/transactions', [DepotTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/create', [DepotTransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [DepotTransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{depotTransaction}', [DepotTransactionController::class, 'show'])->name('transactions.show');
        Route::patch('/transactions/{depotTransaction}/cancel', [DepotTransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::get('/requests', [DepotRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [DepotRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [DepotRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{depotRequest}/print', [DepotRequestController::class, 'print'])->name('requests.print');
        Route::get('/requests/{depotRequest}', [DepotRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{depotRequest}/edit', [DepotRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{depotRequest}', [DepotRequestController::class, 'update'])->name('requests.update');
        Route::post('/requests/{depotRequest}/submit', [DepotRequestController::class, 'submit'])->name('requests.submit');
        Route::post('/requests/{depotRequest}/approve', [DepotRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{depotRequest}/reject', [DepotRequestController::class, 'reject'])->name('requests.reject');
        Route::post('/requests/{depotRequest}/fulfill', [DepotRequestController::class, 'fulfill'])->name('requests.fulfill');
        Route::post('/requests/{depotRequest}/cancel', [DepotRequestController::class, 'cancel'])->name('requests.cancel');
        Route::get('/movements/depot-to-depot', [DepotMovementController::class, 'depotToDepot'])->name('movements.depot-to-depot');
        Route::get('/movements/depot-to-pharmacy', [DepotMovementController::class, 'depotToPharmacy'])->name('movements.depot-to-pharmacy');
        Route::get('/reports', [DepotReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [DepotReportController::class, 'export'])->name('reports.export');
        Route::get('/{depot}/stock', [DepotController::class, 'stock'])->name('stock');
        Route::get('/{depot}/edit', [DepotController::class, 'edit'])->name('edit');
        Route::get('/{depot}', [DepotController::class, 'show'])->name('show');
        Route::match(['put', 'post'], '/{depot}', [DepotController::class, 'update'])->name('update');
        Route::delete('/{depot}', [DepotController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tools')->name('tools.')->group(function () {
        Route::get('/', [ToolController::class, 'index'])->name('index');
        Route::get('/create', [ToolController::class, 'create'])->name('create');
        Route::post('/', [ToolController::class, 'store'])->name('store');
        Route::get('/{tool}/edit', [ToolController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{tool}', [ToolController::class, 'update'])->name('update');
        Route::delete('/{tool}', [ToolController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('blood-banks')->name('blood-banks.')->group(function () {
        Route::get('/dashboard', [BloodBankController::class, 'dashboard'])->name('dashboard');
        Route::get('/new', [BloodBankController::class, 'new'])->name('new');
        Route::get('/approved', [BloodBankController::class, 'approved'])->name('approved');
        Route::get('/delivered', [BloodBankController::class, 'delivered'])->name('delivered');
        Route::get('/rejected', [BloodBankController::class, 'rejected'])->name('rejected');
        Route::get('/inventory/create', [BloodUnitController::class, 'create'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('inventory.create');
        Route::post('/inventory', [BloodUnitController::class, 'store'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('inventory.store');
        Route::get('/inventory', [BloodUnitController::class, 'index'])->name('inventory');
        Route::middleware('permission:receive-blood-units|manage-blood-inventory')->group(function () {
            Route::get('/inventory/{bloodUnit}', [BloodUnitController::class, 'show'])->name('inventory.show');
            Route::post('/inventory/{bloodUnit}/tests', [BloodUnitController::class, 'saveTests'])->name('inventory.tests.save');
            Route::post('/inventory/{bloodUnit}/approve-after-tests', [BloodUnitController::class, 'approveAfterTests'])->name('inventory.tests.approve');
            Route::post('/inventory/{bloodUnit}/discard', [BloodUnitController::class, 'discard'])->name('inventory.discard');
            Route::post('/inventory/{bloodUnit}/quarantine', [BloodUnitController::class, 'quarantine'])->name('inventory.quarantine');
            Route::post('/inventory/{bloodUnit}/release-quarantine', [BloodUnitController::class, 'releaseQuarantine'])->name('inventory.release-quarantine');
        });
        Route::get('/movements', [BloodBankController::class, 'movements'])->name('movements');
        Route::get('/branch-transfers', [BloodBranchTransferController::class, 'index'])->name('branch-transfers.index');
        Route::get('/report', [BloodBankController::class, 'report'])->name('report');
        Route::get('/nurses-by-department/{department}', [BloodBankController::class, 'nursesByDepartment'])->name('nurses-by-department');
        Route::get('/{bloodBank}', [BloodBankController::class, 'show'])->name('show');
        Route::post('/{bloodBank}/approve', [BloodBankController::class, 'approve'])->name('approve');
        Route::put('/{bloodBank}/reject', [BloodBankController::class, 'reject'])->name('reject');
        Route::post('/{bloodBank}/deliver', [BloodBankController::class, 'deliver'])->name('deliver');
        Route::middleware('permission:receive-blood-units|manage-blood-inventory')->group(function () {
            Route::post('/{bloodBank}/blood-check', [BloodBankController::class, 'storeBloodCheck'])->name('blood-check.store');
            Route::post('/{bloodBank}/tests/{bloodBankTest}/fill', [BloodBankController::class, 'fillBloodBankTest'])->name('tests.fill');
            Route::post('/{bloodBank}/crossmatch/samples', [BloodBankController::class, 'storePatientSample'])->name('crossmatch.samples.store');
            Route::post('/{bloodBank}/crossmatch/units/{bloodUnit}', [BloodBankController::class, 'saveCrossmatch'])->name('crossmatch.save');
            Route::post('/{bloodBank}/crossmatch/{crossmatch}/override', [BloodBankController::class, 'overrideCrossmatch'])->name('crossmatch.override');
            Route::post('/{bloodBank}/crossmatch/{crossmatch}/reserve', [BloodBankController::class, 'reserveCrossmatchUnit'])->name('crossmatch.reserve');
            Route::post('/{bloodBank}/crossmatch/units/{bloodUnit}/unreserve', [BloodBankController::class, 'unreserveCrossmatchUnit'])->name('crossmatch.unreserve');
        });
    });

    Route::prefix('prosthetics')->name('prosthetics.')->middleware('permission:show-prosthetics-menu')->group(function () {
        Route::get('/dashboard', [ProstheticsDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/referrals/patients/search', [ProstheticReferralController::class, 'searchPatients'])->name('referrals.patients.search');
        Route::post('/referrals/{referral}/accept', [ProstheticReferralController::class, 'accept'])->name('referrals.accept');
        Route::post('/referrals/{referral}/reject', [ProstheticReferralController::class, 'reject'])->name('referrals.reject');
        Route::post('/referrals/{referral}/convert', [ProstheticReferralController::class, 'convertToCase'])->name('referrals.convert');
        Route::get('/referrals', [ProstheticReferralController::class, 'index'])->name('referrals.index');
        Route::get('/referrals/create', [ProstheticReferralController::class, 'create'])->name('referrals.create');
        Route::post('/referrals', [ProstheticReferralController::class, 'store'])->name('referrals.store');
        Route::get('/referrals/{referral}', [ProstheticReferralController::class, 'show'])->name('referrals.show');
        Route::get('/referrals/{referral}/edit', [ProstheticReferralController::class, 'edit'])->name('referrals.edit');
        Route::match(['put', 'post'], '/referrals/{referral}', [ProstheticReferralController::class, 'update'])->name('referrals.update');

        Route::get('/cases', [ProstheticCaseController::class, 'index'])->name('cases.index');
        Route::get('/cases/create', [ProstheticCaseController::class, 'create'])->name('cases.create');
        Route::post('/cases', [ProstheticCaseController::class, 'store'])->name('cases.store');
        Route::get('/cases/{prosthetic_case}', [ProstheticCaseController::class, 'show'])->name('cases.show');
        Route::post('/cases/{prosthetic_case}/assessment', [ProstheticCaseController::class, 'saveAssessment'])->name('cases.assessment');
        Route::post('/cases/{prosthetic_case}/measurements', [ProstheticCaseController::class, 'saveMeasurements'])->name('cases.measurements');
        Route::post('/cases/{prosthetic_case}/measurements/lock', [ProstheticCaseController::class, 'lockMeasurements'])->name('cases.measurements.lock');
        Route::post('/cases/{prosthetic_case}/prescription', [ProstheticCaseController::class, 'savePrescription'])->name('cases.prescription');
        Route::post('/cases/{prosthetic_case}/estimate', [ProstheticCaseController::class, 'updateEstimate'])->name('cases.estimate');
        Route::post('/cases/{prosthetic_case}/submit-approval', [ProstheticCaseController::class, 'submitForApproval'])->name('cases.submit_approval');
        Route::post('/cases/{prosthetic_case}/approve', [ProstheticCaseController::class, 'approveCase'])->name('cases.approve');
        Route::post('/cases/{prosthetic_case}/work-order', [ProstheticCaseController::class, 'createWorkOrder'])->name('cases.work_order');
        Route::match(['put', 'post'], '/work-orders/{prosthetic_work_order}', [ProstheticCaseController::class, 'updateWorkOrder'])->name('work_orders.update');
        Route::post('/cases/{prosthetic_case}/issue-stock', [ProstheticCaseController::class, 'issueStock'])->name('cases.issue_stock');
        Route::post('/cases/{prosthetic_case}/fitting', [ProstheticCaseController::class, 'storeFitting'])->name('cases.fitting');
        Route::post('/cases/{prosthetic_case}/delivery', [ProstheticCaseController::class, 'storeDelivery'])->name('cases.delivery');
        Route::post('/cases/{prosthetic_case}/follow-up', [ProstheticCaseController::class, 'storeFollowUp'])->name('cases.follow_up');
        Route::post('/cases/{prosthetic_case}/close', [ProstheticCaseController::class, 'closeCase'])->name('cases.close');
        Route::post('/cases/{prosthetic_case}/attachments/upload', [ProstheticCaseController::class, 'uploadAttachments'])->name('cases.attachments.upload');
        Route::delete('/attachments/{attachment}', [ProstheticCaseController::class, 'deleteAttachment'])->name('attachments.delete');

        Route::get('/catalog', [ProstheticCatalogController::class, 'index'])->name('catalog.index');
        Route::get('/catalog/create', [ProstheticCatalogController::class, 'create'])->middleware('permission:manage-prosthetics-catalog')->name('catalog.create');
        Route::post('/catalog', [ProstheticCatalogController::class, 'store'])->middleware('permission:manage-prosthetics-catalog')->name('catalog.store');
        Route::get('/catalog/{item}/edit', [ProstheticCatalogController::class, 'edit'])->middleware('permission:manage-prosthetics-catalog')->name('catalog.edit');
        Route::match(['put', 'post'], '/catalog/{item}', [ProstheticCatalogController::class, 'update'])->middleware('permission:manage-prosthetics-catalog')->name('catalog.update');

        Route::get('/stock', [ProstheticStockController::class, 'index'])->name('stock.index');
        Route::post('/stock/receive', [ProstheticStockController::class, 'receive'])->middleware('permission:manage-prosthetics-stock')->name('stock.receive');

        Route::get('/reports', [ProstheticsReportController::class, 'index'])->name('reports.index');
    });

    Route::prefix('under-reviews')->name('under-reviews.')->middleware('permission:show-under-review-menu')->group(function () {
        Route::get('/', [UnderReviewController::class, 'index'])->name('index');
        Route::get('/pending', [UnderReviewController::class, 'pending'])->name('pending');
        Route::get('/my-cases', [UnderReviewController::class, 'myCases'])->name('my-cases');
        Route::get('/discharged', [UnderReviewController::class, 'discharged'])->name('discharged');
        Route::post('/{underReview}/accept', [UnderReviewController::class, 'accept'])->name('accept');
        Route::get('/{underReview}/edit', [UnderReviewController::class, 'edit'])->name('edit');
        Route::get('/{underReview}', [UnderReviewController::class, 'show'])->name('show');
        Route::match(['put', 'post'], '/{underReview}', [UnderReviewController::class, 'update'])->name('update');
        Route::post('/{underReview}/discharge', [UnderReviewController::class, 'discharge'])->name('discharge');
        Route::post('/{underReview}/visits', [UnderReviewController::class, 'storeVisit'])->name('visits.store');
        Route::match(['put', 'post'], '/{underReview}/visits/{visit}', [UnderReviewController::class, 'updateVisit'])->name('visits.update');
        Route::delete('/{underReview}/visits/{visit}', [UnderReviewController::class, 'destroyVisit'])->name('visits.destroy');
    });

    Route::prefix('hospitalizations')->name('hospitalizations.')->middleware('permission:show-hospitalizations-menu')->group(function () {
        Route::get('/', [HospitalizationController::class, 'index'])->name('index');
        Route::get('/discharged', [HospitalizationController::class, 'discharged'])->name('discharged');
        Route::get('/report', [HospitalizationController::class, 'report'])->name('report');
        Route::get('/room-management', [HospitalizationController::class, 'roomManagement'])
            ->middleware('permission:manage-hospitalization-rooms')
            ->name('room-management');
        Route::get('/{hospitalization}/vital-signs/meta', [HospitalizationVitalSignController::class, 'meta'])->name('vital-signs.meta');
        Route::get('/{hospitalization}/vital-signs', [HospitalizationVitalSignController::class, 'index'])->name('vital-signs.index');
        Route::post('/{hospitalization}/vital-signs', [HospitalizationVitalSignController::class, 'store'])->name('vital-signs.store');
        Route::get('/{hospitalization}/diabetes-charts/meta', [HospitalizationDiabetesChartController::class, 'meta'])->name('diabetes-charts.meta');
        Route::get('/{hospitalization}/diabetes-charts/{diabetesChart}', [HospitalizationDiabetesChartController::class, 'show'])->name('diabetes-charts.show');
        Route::get('/{hospitalization}/diabetes-charts', [HospitalizationDiabetesChartController::class, 'index'])->name('diabetes-charts.index');
        Route::post('/{hospitalization}/diabetes-charts', [HospitalizationDiabetesChartController::class, 'store'])->name('diabetes-charts.store');
        Route::match(['put', 'post'], '/{hospitalization}/diabetes-charts/{diabetesChart}', [HospitalizationDiabetesChartController::class, 'update'])->name('diabetes-charts.update');
        Route::delete('/{hospitalization}/diabetes-charts/{diabetesChart}', [HospitalizationDiabetesChartController::class, 'destroy'])->name('diabetes-charts.destroy');
        Route::prefix('{hospitalization}/nurse-notes')->name('nurse-notes.')->group(function () {
            Route::get('meta', [HospitalizationNurseNoteController::class, 'meta'])->name('meta');
            Route::get('{nurseNote}', [HospitalizationNurseNoteController::class, 'show'])->name('show');
            Route::get('/', [HospitalizationNurseNoteController::class, 'index'])->name('index');
            Route::post('/', [HospitalizationNurseNoteController::class, 'store'])->name('store');
            Route::match(['put', 'post'], '{nurseNote}', [HospitalizationNurseNoteController::class, 'update'])->name('update');
            Route::delete('{nurseNote}', [HospitalizationNurseNoteController::class, 'destroy'])->name('destroy');
        });
        Route::get('/{hospitalization}/nutrition-cares/meta', [HospitalizationNutritionCareController::class, 'meta'])->name('nutrition-cares.meta');
        Route::get('/{hospitalization}/nutrition-cares/{nutritionCare}', [HospitalizationNutritionCareController::class, 'show'])->name('nutrition-cares.show');
        Route::get('/{hospitalization}/nutrition-cares', [HospitalizationNutritionCareController::class, 'index'])->name('nutrition-cares.index');
        Route::post('/{hospitalization}/nutrition-cares', [HospitalizationNutritionCareController::class, 'store'])->name('nutrition-cares.store');
        Route::match(['put', 'post'], '/{hospitalization}/nutrition-cares/{nutritionCare}', [HospitalizationNutritionCareController::class, 'update'])->name('nutrition-cares.update');
        Route::delete('/{hospitalization}/nutrition-cares/{nutritionCare}', [HospitalizationNutritionCareController::class, 'destroy'])->name('nutrition-cares.destroy');
        Route::get('/{hospitalization}/icu/meta', [HospitalizationIcuController::class, 'meta'])->name('icu.meta');
        Route::get('/{hospitalization}/icu', [HospitalizationIcuController::class, 'index'])->name('icu.index');
        Route::post('/{hospitalization}/icu', [HospitalizationIcuController::class, 'store'])->name('icu.store');
        Route::delete('/{hospitalization}/icu/{icu}', [HospitalizationIcuController::class, 'destroy'])->name('icu.destroy');
        Route::get('/{hospitalization}/pacus/meta', [HospitalizationPacuController::class, 'meta'])->name('pacus.meta');
        Route::get('/{hospitalization}/pacus', [HospitalizationPacuController::class, 'index'])->name('pacus.index');
        Route::post('/{hospitalization}/pacus', [HospitalizationPacuController::class, 'store'])->name('pacus.store');
        Route::delete('/{hospitalization}/pacus/{pacu}', [HospitalizationPacuController::class, 'destroy'])->name('pacus.destroy');
        Route::get('/{hospitalization}/under-review/meta', [HospitalizationUnderReviewController::class, 'meta'])->name('under-review.meta');
        Route::get('/{hospitalization}/under-review', [HospitalizationUnderReviewController::class, 'index'])->name('under-review.index');
        Route::post('/{hospitalization}/under-review', [HospitalizationUnderReviewController::class, 'store'])->name('under-review.store');
        Route::delete('/{hospitalization}/under-review/{underReview}', [HospitalizationUnderReviewController::class, 'destroy'])->name('under-review.destroy');
        Route::get('/{hospitalization}/anesthesia/meta', [HospitalizationAnesthesiaController::class, 'meta'])->name('anesthesia.meta');
        Route::get('/{hospitalization}/anesthesia', [HospitalizationAnesthesiaController::class, 'index'])->name('anesthesia.index');
        Route::post('/{hospitalization}/anesthesia', [HospitalizationAnesthesiaController::class, 'store'])->name('anesthesia.store');
        Route::delete('/{hospitalization}/anesthesia/{anesthesia}', [HospitalizationAnesthesiaController::class, 'destroy'])->name('anesthesia.destroy');
        Route::get('/{hospitalization}/operations/meta', [HospitalizationOperationController::class, 'meta'])->name('operations.meta');
        Route::get('/{hospitalization}/operations', [HospitalizationOperationController::class, 'index'])->name('operations.index');
        Route::post('/{hospitalization}/operations', [HospitalizationOperationController::class, 'store'])->name('operations.store');
        Route::get('/{hospitalization}/visits/meta', [HospitalizationVisitController::class, 'meta'])->name('visits.meta');
        Route::get('/{hospitalization}/visits/{visit}', [HospitalizationVisitController::class, 'show'])->name('visits.show');
        Route::get('/{hospitalization}/visits', [HospitalizationVisitController::class, 'index'])->name('visits.index');
        Route::post('/{hospitalization}/visits', [HospitalizationVisitController::class, 'store'])->name('visits.store');
        Route::match(['put', 'post'], '/{hospitalization}/visits/{visit}', [HospitalizationVisitController::class, 'update'])->name('visits.update');
        Route::delete('/{hospitalization}/visits/{visit}', [HospitalizationVisitController::class, 'destroy'])->name('visits.destroy');
        Route::get('/{hospitalization}/room-bed/meta', [HospitalizationRoomBedController::class, 'meta'])->name('room-bed.meta');
        Route::put('/{hospitalization}/room-bed', [HospitalizationRoomBedController::class, 'update'])->name('room-bed.update');
        Route::get('/{hospitalization}/edit', [HospitalizationController::class, 'edit'])->name('edit');
        Route::get('/{hospitalization}', [HospitalizationController::class, 'show'])->name('show');
        Route::match(['put', 'post'], '/{hospitalization}', [HospitalizationController::class, 'update'])->name('update');
        Route::post('/{hospitalization}/discharge', [HospitalizationController::class, 'discharge'])->name('discharge');
    });

    Route::prefix('vital-sign-types')->name('vital-sign-types.')->group(function () {
        Route::get('/', [VitalSignTypeController::class, 'index'])->name('index');
        Route::get('/create', [VitalSignTypeController::class, 'create'])->name('create');
        Route::post('/', [VitalSignTypeController::class, 'store'])->name('store');
        Route::get('/{vitalSignType}', [VitalSignTypeController::class, 'show'])->name('show');
        Route::get('/{vitalSignType}/edit', [VitalSignTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{vitalSignType}', [VitalSignTypeController::class, 'update'])->name('update');
        Route::delete('/{vitalSignType}', [VitalSignTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('vital-signs')->name('vital-signs.')->group(function () {
        Route::get('/', [VitalSignController::class, 'index'])->name('index');
        Route::get('/{vitalSign}', [VitalSignController::class, 'show'])->name('show');
    });

    Route::prefix('laboratory')->name('laboratory.')->group(function () {
        Route::get('/scan', [LaboratoryController::class, 'scan'])->name('scan');
        Route::post('/scan', [LaboratoryController::class, 'scanSubmit'])->name('scan.submit');
        Route::prefix('results')->name('results.')->group(function () {
            Route::get('/pending', [LaboratoryController::class, 'pending'])->name('pending');
            Route::get('/in-progress', [LaboratoryController::class, 'inProgress'])->name('in-progress');
            Route::get('/completed', [LaboratoryController::class, 'completed'])->name('completed');
            Route::get('/grouped', [LaboratoryController::class, 'grouped'])->name('grouped');
            Route::get('/registration/{registration}', [LaboratoryController::class, 'showResults'])->name('show');
            Route::post('/registration/{registration}', [LaboratoryController::class, 'updateResults'])->name('update');
            Route::post('/{registration}/accept', [LaboratoryController::class, 'accept'])->name('accept');
        });
        Route::prefix('registrations')->name('registrations.')->group(function () {
            Route::get('/report', [LaboratoryController::class, 'registrationReport'])->name('report');
            Route::get('/report-detailed', [LaboratoryController::class, 'registrationReportDetailed'])->name('report-detailed');
            Route::post('/{registration}/mark-completed', [LaboratoryController::class, 'markCompleted'])->name('mark-completed');
            Route::post('/{registration}/cancel', [LaboratoryController::class, 'cancel'])->name('cancel');
        });
        Route::get('/reports/print/{ref_no}', [LaboratoryController::class, 'printReport'])->name('reports.print');
    });

    Route::prefix('lab-types')->name('lab-types.')->group(function () {
        Route::get('/', [LabTypeController::class, 'index'])->name('index');
        Route::post('/categories', [LabTypeController::class, 'storeCategory'])->name('categories.store');
        Route::match(['put', 'post'], '/categories/{category}', [LabTypeController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [LabTypeController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('/create', [LabTypeController::class, 'create'])->name('create');
        Route::post('/', [LabTypeController::class, 'store'])->name('store');
        Route::get('/{labType}', [LabTypeController::class, 'show'])->name('show');
        Route::get('/{labType}/edit', [LabTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{labType}', [LabTypeController::class, 'update'])->name('update');
        Route::delete('/{labType}', [LabTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('icus')->name('icus.')->group(function () {
        Route::get('/new', [ICUController::class, 'new'])->name('new');
        Route::get('/approved', [ICUController::class, 'approved'])->name('approved');
        Route::get('/rejected', [ICUController::class, 'rejected'])->name('rejected');
        Route::get('/report', [ICUController::class, 'report'])->name('report');
        Route::get('/{icu}/discharge/meta', [ICUController::class, 'dischargeMeta'])->name('discharge.meta');
        Route::get('/{icu}/procedures/meta', [IcuProcedureController::class, 'meta'])->name('procedures.meta');
        Route::get('/{icu}/procedures/{icuProcedure}', [IcuProcedureController::class, 'show'])->name('procedures.show');
        Route::get('/{icu}/procedures', [IcuProcedureController::class, 'index'])->name('procedures.index');
        Route::post('/{icu}/procedures', [IcuProcedureController::class, 'store'])->name('procedures.store');
        Route::match(['put', 'post'], '/{icu}/procedures/{icuProcedure}', [IcuProcedureController::class, 'update'])->name('procedures.update');
        Route::delete('/{icu}/procedures/{icuProcedure}', [IcuProcedureController::class, 'destroy'])->name('procedures.destroy');
        Route::get('/{icu}/daily-progress/meta', [IcuDailyProgressController::class, 'meta'])->name('daily-progress.meta');
        Route::get('/{icu}/daily-progress/{dailyIcuProgress}', [IcuDailyProgressController::class, 'show'])->name('daily-progress.show');
        Route::get('/{icu}/daily-progress', [IcuDailyProgressController::class, 'index'])->name('daily-progress.index');
        Route::post('/{icu}/daily-progress', [IcuDailyProgressController::class, 'store'])->name('daily-progress.store');
        Route::match(['put', 'post'], '/{icu}/daily-progress/{dailyIcuProgress}', [IcuDailyProgressController::class, 'update'])->name('daily-progress.update');
        Route::delete('/{icu}/daily-progress/{dailyIcuProgress}', [IcuDailyProgressController::class, 'destroy'])->name('daily-progress.destroy');
        Route::get('/{icu}/visits/meta', [IcuVisitController::class, 'meta'])->name('visits.meta');
        Route::get('/{icu}/visits/{visit}', [IcuVisitController::class, 'show'])->name('visits.show');
        Route::get('/{icu}/visits', [IcuVisitController::class, 'index'])->name('visits.index');
        Route::post('/{icu}/visits', [IcuVisitController::class, 'store'])->name('visits.store');
        Route::match(['put', 'post'], '/{icu}/visits/{visit}', [IcuVisitController::class, 'update'])->name('visits.update');
        Route::delete('/{icu}/visits/{visit}', [IcuVisitController::class, 'destroy'])->name('visits.destroy');
        Route::get('/{icu}', [ICUController::class, 'show'])->name('show');
        Route::put('/{icu}', [ICUController::class, 'update'])->name('update');
        Route::delete('/{icu}', [ICUController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('pacus')->name('pacus.')->group(function () {
        Route::get('/', [PACUController::class, 'index'])->name('index');
        Route::get('/completed', [PACUController::class, 'completed'])->name('completed');
        Route::get('/report', [PACUController::class, 'report'])->name('report');
        Route::post('/{pacu}/complete', [PACUController::class, 'complete'])->name('complete');
        Route::post('/{pacu}/visits', [PACUController::class, 'storeVisit'])->name('visits.store');
        Route::get('/{pacu}', [PACUController::class, 'show'])->name('show');
    });

    Route::prefix('anesthesias')->name('anesthesias.')->group(function () {
        Route::get('/new', [AnesthesiaController::class, 'new'])->name('new');
        Route::get('/approved', [AnesthesiaController::class, 'approved'])->name('approved');
        Route::get('/rejected', [AnesthesiaController::class, 'rejected'])->name('rejected');
        Route::get('/report', [AnesthesiaController::class, 'report'])->name('report');
        Route::get('/{anesthesia}/edit', [AnesthesiaController::class, 'edit'])->name('edit');
        Route::put('/{anesthesia}/details', [AnesthesiaController::class, 'updateDetails'])->name('update-details');
        Route::post('/{anesthesia}/refer-to-operation', [AnesthesiaController::class, 'referToOperation'])->name('refer-to-operation');
        Route::get('/{anesthesia}', [AnesthesiaController::class, 'show'])->name('show');
        Route::put('/{anesthesia}', [AnesthesiaController::class, 'update'])->name('update');
        Route::delete('/{anesthesia}', [AnesthesiaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('operations')->name('operations.')->group(function () {
        Route::get('/new', [OperationController::class, 'new'])->name('new');
        Route::get('/approved', [OperationController::class, 'approved'])->name('approved');
        Route::get('/reserved', [OperationController::class, 'reserved'])->name('reserved');
        Route::get('/completed', [OperationController::class, 'completed'])->name('completed');
        Route::get('/report', [OperationController::class, 'report'])->name('report');
        Route::get('/{operation}', [OperationController::class, 'show'])->name('show');
        Route::put('/{operation}', [OperationController::class, 'update'])->name('update');
        Route::put('/{operation}/complete', [OperationController::class, 'complete'])->name('complete');
        Route::put('/{operation}/reserve', [OperationController::class, 'reserve'])->name('reserve');
        Route::post('/{operation}/unreserve', [OperationController::class, 'unreserve'])->name('unreserve');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/status', [UserController::class, 'updateStatus'])->name('update-status');
    });
    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/', [DoctorController::class, 'index'])->name('index');
        Route::get('/create', [DoctorController::class, 'create'])->name('create');
        Route::post('/', [DoctorController::class, 'store'])->name('store');
        Route::get('/{doctor}', [DoctorController::class, 'show'])->name('show');
        Route::get('/{doctor}/edit', [DoctorController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{doctor}', [DoctorController::class, 'update'])->name('update');
        Route::post('/{doctor}/status', [DoctorController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{doctor}', [DoctorController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/create', [PermissionController::class, 'create'])->name('create');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{permission}', [PermissionController::class, 'update'])->name('update');
    });
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::prefix('activity-logs')->name('activity-logs.')->middleware('role:super_admin')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');
    });
    Route::prefix('recipients')->name('recipients.')->group(function () {
        Route::get('/', [RecipientController::class, 'index'])->name('index');
        Route::get('/create', [RecipientController::class, 'create'])->name('create');
        Route::post('/', [RecipientController::class, 'store'])->name('store');
        Route::get('/{recipient}/edit', [RecipientController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{recipient}', [RecipientController::class, 'update'])->name('update');
        Route::delete('/{recipient}', [RecipientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('recipient-parts')->name('recipient-parts.')->group(function () {
        Route::get('/', [RecipientPartController::class, 'index'])->name('index');
        Route::get('/create', [RecipientPartController::class, 'create'])->name('create');
        Route::post('/', [RecipientPartController::class, 'store'])->name('store');
        Route::get('/{recipientPart}/edit', [RecipientPartController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{recipientPart}', [RecipientPartController::class, 'update'])->name('update');
        Route::delete('/{recipientPart}', [RecipientPartController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('relations')->name('relations.')->group(function () {
        Route::get('/', [RelationController::class, 'index'])->name('index');
        Route::get('/create', [RelationController::class, 'create'])->name('create');
        Route::post('/', [RelationController::class, 'store'])->name('store');
        Route::get('/{relation}/edit', [RelationController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{relation}', [RelationController::class, 'update'])->name('update');
        Route::delete('/{relation}', [RelationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('units')->name('units.')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('index');
        Route::get('/create', [UnitController::class, 'create'])->name('create');
        Route::post('/', [UnitController::class, 'store'])->name('store');
        Route::get('/{unit}/edit', [UnitController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{unit}', [UnitController::class, 'update'])->name('update');
        Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('sections')->name('sections.')->group(function () {
        Route::get('/', [SectionController::class, 'index'])->name('index');
        Route::get('/create', [SectionController::class, 'create'])->name('create');
        Route::post('/', [SectionController::class, 'store'])->name('store');
        Route::get('/{section}/edit', [SectionController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{section}', [SectionController::class, 'update'])->name('update');
        Route::delete('/{section}', [SectionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('floors')->name('floors.')->group(function () {
        Route::get('/', [FloorController::class, 'index'])->name('index');
        Route::get('/create', [FloorController::class, 'create'])->name('create');
        Route::post('/', [FloorController::class, 'store'])->name('store');
        Route::get('/{floor}/edit', [FloorController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{floor}', [FloorController::class, 'update'])->name('update');
        Route::delete('/{floor}', [FloorController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::get('/create', [RoomController::class, 'create'])->name('create');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/{room}', [RoomController::class, 'show'])->name('show');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{room}', [RoomController::class, 'update'])->name('update');
        Route::delete('/{room}', [RoomController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('beds')->name('beds.')->group(function () {
        Route::get('/', [BedController::class, 'index'])->name('index');
        Route::get('/create', [BedController::class, 'create'])->name('create');
        Route::post('/', [BedController::class, 'store'])->name('store');
        Route::get('/{bed}/edit', [BedController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{bed}', [BedController::class, 'update'])->name('update');
        Route::delete('/{bed}', [BedController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('militery-types')->name('militery-types.')->group(function () {
        Route::get('/', [MiliteryTypeController::class, 'index'])->name('index');
        Route::get('/create', [MiliteryTypeController::class, 'create'])->name('create');
        Route::post('/', [MiliteryTypeController::class, 'store'])->name('store');
        Route::get('/{militeryType}/edit', [MiliteryTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{militeryType}', [MiliteryTypeController::class, 'update'])->name('update');
        Route::delete('/{militeryType}', [MiliteryTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('procedure-types')->name('procedure-types.')->group(function () {
        Route::get('/', [ProcedureTypeController::class, 'index'])->name('index');
        Route::get('/create', [ProcedureTypeController::class, 'create'])->name('create');
        Route::post('/', [ProcedureTypeController::class, 'store'])->name('store');
        Route::get('/{icuProcedureType}/edit', [ProcedureTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{icuProcedureType}', [ProcedureTypeController::class, 'update'])->name('update');
        Route::delete('/{icuProcedureType}', [ProcedureTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('operation-types')->name('operation-types.')->group(function () {
        Route::get('/', [OperationTypeController::class, 'index'])->name('index');
        Route::get('/create', [OperationTypeController::class, 'create'])->name('create');
        Route::post('/', [OperationTypeController::class, 'store'])->name('store');
        Route::get('/{operationType}/edit', [OperationTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{operationType}', [OperationTypeController::class, 'update'])->name('update');
        Route::delete('/{operationType}', [OperationTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('medicine-types')->name('medicine-types.')->group(function () {
        Route::get('/', [MedicineTypeController::class, 'index'])->name('index');
        Route::get('/create', [MedicineTypeController::class, 'create'])->name('create');
        Route::post('/', [MedicineTypeController::class, 'store'])->name('store');
        Route::get('/{medicineType}/edit', [MedicineTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{medicineType}', [MedicineTypeController::class, 'update'])->name('update');
        Route::delete('/{medicineType}', [MedicineTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('medicines')->name('medicines.')->group(function () {
        Route::get('/', [MedicineController::class, 'index'])->name('index');
        Route::get('/create', [MedicineController::class, 'create'])->name('create');
        Route::post('/', [MedicineController::class, 'store'])->name('store');
        Route::get('/{medicine}/edit', [MedicineController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{medicine}', [MedicineController::class, 'update'])->name('update');
        Route::delete('/{medicine}', [MedicineController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('medicine-usage-types')->name('medicine-usage-types.')->group(function () {
        Route::get('/', [MedicineUsageTypeController::class, 'index'])->name('index');
        Route::get('/create', [MedicineUsageTypeController::class, 'create'])->name('create');
        Route::post('/', [MedicineUsageTypeController::class, 'store'])->name('store');
        Route::get('/{medicineUsageType}/edit', [MedicineUsageTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{medicineUsageType}', [MedicineUsageTypeController::class, 'update'])->name('update');
        Route::delete('/{medicineUsageType}', [MedicineUsageTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('food-types')->name('food-types.')->group(function () {
        Route::get('/', [FoodTypeController::class, 'index'])->name('index');
        Route::get('/create', [FoodTypeController::class, 'create'])->name('create');
        Route::post('/', [FoodTypeController::class, 'store'])->name('store');
        Route::get('/{foodType}/edit', [FoodTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{foodType}', [FoodTypeController::class, 'update'])->name('update');
        Route::delete('/{foodType}', [FoodTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('diseases')->name('diseases.')->group(function () {
        Route::get('/', [DiseaseController::class, 'index'])->name('index');
        Route::get('/create', [DiseaseController::class, 'create'])->name('create');
        Route::post('/', [DiseaseController::class, 'store'])->name('store');
        Route::post('/categories', [DiseaseController::class, 'storeCategory'])->name('categories.store');
        Route::match(['put', 'post'], '/categories/{diseaseCategory}', [DiseaseController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{diseaseCategory}', [DiseaseController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('/{disease}/edit', [DiseaseController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{disease}', [DiseaseController::class, 'update'])->name('update');
        Route::delete('/{disease}', [DiseaseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('nurses')->name('nurses.')->group(function () {
        Route::get('/', [NurseController::class, 'index'])->name('index');
        Route::get('/create', [NurseController::class, 'create'])->name('create');
        Route::post('/', [NurseController::class, 'store'])->name('store');
        Route::get('/{nurse}', [NurseController::class, 'show'])->name('show');
        Route::get('/{nurse}/edit', [NurseController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{nurse}', [NurseController::class, 'update'])->name('update');
        Route::delete('/{nurse}', [NurseController::class, 'destroy'])->name('destroy');
    });
    Route::get("/reports/general", [GenralReport::class, "index"])->name("reports.general.index");
 
});
Route::prefix("/api")->name("api.")->group(function () {
     Route::get("/reports/general/number-of-patients-base-on-department", [GenralReport::class, "number_of_patients_base_on_department"])->name("reports.general.number-of-patients-base-on-department");
     Route::get("/reports/general/number-of-patients-base-on-patient-militery-types", [GenralReport::class, "number_of_patients_base_on_patient_militery_types"])->name("reports.general.number-of-patients-base-on-patient-militery-types");
     Route::get("/reports/general/hospitalization", [GenralReport::class, "hospitalization"])->name("reports.general.hospitalization");
     Route::get("/reports/general/operations", [GenralReport::class, "operations"])->name("reports.general.operations");
     Route::get("/reports/general/anesthesias", [GenralReport::class, "anesthesias"])->name("reports.general.anesthesias");
     Route::get("/reports/general/icus", [GenralReport::class, "icus"])->name("reports.general.icus");
     Route::get("/reports/general/under-reviews", [GenralReport::class, "under_reviews"])->name("reports.general.under-reviews");
     Route::get("/reports/general/patient-test-registrations", [GenralReport::class, "patient_test_registrations"])->name("reports.general.patient-test-registrations");
     Route::get("/reports/general/prescriptions", [GenralReport::class, "prescriptions"])->name("reports.general.prescriptions");
});
