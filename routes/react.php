<?php

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
use App\Http\Controllers\V1\AppointmentSections\PhysiotherapyController as AppointmentPhysiotherapyController;
use App\Http\Controllers\V1\AppointmentSections\PrescriptionController as AppointmentPrescriptionController;
use App\Http\Controllers\V1\AppointmentSections\ReferDepartmentController;
use App\Http\Controllers\V1\AppointmentSections\RelatedVisitsController;
use App\Http\Controllers\V1\AppointmentSections\UnderReviewController as AppointmentUnderReviewController;
use App\Http\Controllers\V1\AppointmentController;
use App\Http\Controllers\V1\BackupController;
use App\Http\Controllers\V1\BedController;
use App\Http\Controllers\V1\BloodBankController;
use App\Http\Controllers\V1\BloodBranchTransferController;
use App\Http\Controllers\V1\BranchController;
use App\Http\Controllers\V1\CategoryController;
use App\Http\Controllers\V1\ConsultationController;
use App\Http\Controllers\V1\DashboardController;
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
use App\Http\Controllers\V1\ICUController;
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
use App\Http\Controllers\V1\OutcomeController;
use App\Http\Controllers\V1\PACUController;
use App\Http\Controllers\V1\PatientController;
use App\Http\Controllers\V1\PermissionController;
use App\Http\Controllers\V1\PharmacyController;
use App\Http\Controllers\V1\PharmacyFulfillmentController;
use App\Http\Controllers\V1\PhysiotherapyProcedureController;
use App\Http\Controllers\V1\PhysiotherapyReportController;
use App\Http\Controllers\V1\PhysiotherapyTypeController;
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
use App\Http\Controllers\V1\RelationController;
use App\Http\Controllers\V1\RoleController;
use App\Http\Controllers\V1\RoomController;
use App\Http\Controllers\V1\ScanCodeController;
use App\Http\Controllers\V1\SectionController;
use App\Http\Controllers\V1\ToolController;
use App\Http\Controllers\V1\UnderReviewController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\VitalSignController;
use App\Http\Controllers\V1\VitalSignTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    Route::get('/scan-code', [ScanCodeController::class, 'index'])->name('scan-code');

    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');
        Route::get('/districts/{provinceId}', [PatientController::class, 'districts'])->name('districts');
        Route::get('/doctors-by-department/{departmentId}', [PatientController::class, 'doctorsByDepartment'])->name('doctors-by-department');
        Route::get('/report', [PatientController::class, 'report'])->name('report');
        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit');
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
            Route::get('refer-department', [ReferDepartmentController::class, 'index'])->name('refer-department.index');
            Route::get('under-review', [AppointmentUnderReviewController::class, 'index'])->name('under-review.index');
            Route::delete('under-review/{underReview}', [AppointmentUnderReviewController::class, 'destroy'])->name('under-review.destroy');
            Route::get('related-visits', [RelatedVisitsController::class, 'index'])->name('related-visits.index');
            Route::get('hospitalization', [AppointmentHospitalizationController::class, 'index'])->name('hospitalization.index');
            Route::delete('hospitalization/{hospitalization}', [AppointmentHospitalizationController::class, 'destroy'])->name('hospitalization.destroy');
            Route::get('hospitalization-visits', [HospitalizationVisitsController::class, 'index'])->name('hospitalization-visits.index');
            Route::get('anesthesia', [AppointmentAnesthesiaController::class, 'index'])->name('anesthesia.index');
            Route::delete('anesthesia/{anesthesia}', [AppointmentAnesthesiaController::class, 'destroy'])->name('anesthesia.destroy');
            Route::get('operations', [AppointmentOperationController::class, 'index'])->name('operations.index');
            Route::get('icu', [AppointmentIcuController::class, 'index'])->name('icu.index');
            Route::delete('icu/{icu}', [AppointmentIcuController::class, 'destroy'])->name('icu.destroy');
            Route::get('icu-visits', [IcuVisitsController::class, 'index'])->name('icu-visits.index');
            Route::get('physiotherapy', [AppointmentPhysiotherapyController::class, 'index'])->name('physiotherapy.index');
            Route::get('dentist', [AppointmentDentistController::class, 'index'])->name('dentist.index');
            Route::get('nephrology', [AppointmentNephrologyController::class, 'index'])->name('nephrology.index');
        });
        Route::post('/{appointment}/accept', [AppointmentController::class, 'accept'])->name('accept');
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
    });

    Route::get('/physiotherapy-reports', [PhysiotherapyReportController::class, 'index'])->name('physiotherapy-reports.index');
    Route::get('/physiotherapy-types', [PhysiotherapyTypeController::class, 'index'])->name('physiotherapy-types.index');

    Route::get('/dentist-registrations', [DentistRegistrationController::class, 'index'])->name('dentist-registrations.index');
    Route::get('/nephrology-registrations', [NephrologyRegistrationController::class, 'index'])->name('nephrology-registrations.index');
    Route::get('/hemodialysis-sessions', [HemodialysisSessionController::class, 'index'])->name('hemodialysis-sessions.index');
    Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');

    Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
        Route::get('/scan-code', [PrescriptionController::class, 'scanCode'])->name('scan-code');
        Route::post('/scan', [PrescriptionController::class, 'scan'])->name('scan');
        Route::get('/', [PrescriptionController::class, 'index'])->name('index');
        Route::get('/delivered', [PrescriptionController::class, 'delivered'])->name('delivered');
        Route::get('/report', [PrescriptionController::class, 'report'])->name('report');
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

    Route::prefix('pharmacy-fulfillments')->name('pharmacy-fulfillments.')->group(function () {
        Route::get('/', [PharmacyFulfillmentController::class, 'index'])->name('index');
        Route::get('/stock', [PharmacyFulfillmentController::class, 'stock'])->name('stock');
        Route::get('/create', [PharmacyFulfillmentController::class, 'create'])->name('create');
        Route::post('/', [PharmacyFulfillmentController::class, 'store'])->name('store');
        Route::get('/{pharmacyFulfillment}', [PharmacyFulfillmentController::class, 'show'])->name('show');
        Route::get('/{pharmacyFulfillment}/edit', [PharmacyFulfillmentController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{pharmacyFulfillment}', [PharmacyFulfillmentController::class, 'update'])->name('update');
        Route::delete('/{pharmacyFulfillment}', [PharmacyFulfillmentController::class, 'destroy'])->name('destroy');
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

    Route::prefix('medicine-types')->name('medicine-types.')->group(function () {
        Route::get('/', [MedicineTypeController::class, 'index'])->name('index');
        Route::get('/create', [MedicineTypeController::class, 'create'])->name('create');
        Route::post('/', [MedicineTypeController::class, 'store'])->name('store');
        Route::get('/{medicineType}/edit', [MedicineTypeController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{medicineType}', [MedicineTypeController::class, 'update'])->name('update');
        Route::delete('/{medicineType}', [MedicineTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('depots')->name('depots.')->group(function () {
        Route::get('/', [DepotController::class, 'index'])->name('index');
        Route::get('/transactions', [DepotTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/requests', [DepotRequestController::class, 'index'])->name('requests.index');
        Route::get('/movements/depot-to-depot', [DepotMovementController::class, 'depotToDepot'])->name('movements.depot-to-depot');
        Route::get('/movements/depot-to-pharmacy', [DepotMovementController::class, 'depotToPharmacy'])->name('movements.depot-to-pharmacy');
        Route::get('/reports', [DepotReportController::class, 'index'])->name('reports.index');
    });

    Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');

    Route::prefix('blood-banks')->name('blood-banks.')->group(function () {
        Route::get('/dashboard', [BloodBankController::class, 'dashboard'])->name('dashboard');
        Route::get('/new', [BloodBankController::class, 'new'])->name('new');
        Route::get('/approved', [BloodBankController::class, 'approved'])->name('approved');
        Route::get('/delivered', [BloodBankController::class, 'delivered'])->name('delivered');
        Route::get('/rejected', [BloodBankController::class, 'rejected'])->name('rejected');
        Route::get('/inventory', [BloodBankController::class, 'inventory'])->name('inventory');
        Route::get('/movements', [BloodBankController::class, 'movements'])->name('movements');
        Route::get('/branch-transfers', [BloodBranchTransferController::class, 'index'])->name('branch-transfers.index');
        Route::get('/report', [BloodBankController::class, 'report'])->name('report');
    });

    Route::prefix('prosthetics')->name('prosthetics.')->group(function () {
        Route::get('/dashboard', [ProstheticsDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/referrals', [ProstheticReferralController::class, 'index'])->name('referrals.index');
        Route::get('/cases', [ProstheticCaseController::class, 'index'])->name('cases.index');
        Route::get('/catalog', [ProstheticCatalogController::class, 'index'])->name('catalog.index');
        Route::get('/stock', [ProstheticStockController::class, 'index'])->name('stock.index');
        Route::get('/reports', [ProstheticsReportController::class, 'index'])->name('reports.index');
    });

    Route::get('/under-reviews', [UnderReviewController::class, 'index'])->name('under-reviews.index');

    Route::prefix('hospitalizations')->name('hospitalizations.')->group(function () {
        Route::get('/', [HospitalizationController::class, 'index'])->name('index');
        Route::get('/discharged', [HospitalizationController::class, 'discharged'])->name('discharged');
        Route::get('/room-management', [HospitalizationController::class, 'roomManagement'])->name('room-management');
        Route::get('/report', [HospitalizationController::class, 'report'])->name('report');
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
            Route::post('/{registration}/accept', [LaboratoryController::class, 'accept'])->name('accept');
        });
        Route::prefix('registrations')->name('registrations.')->group(function () {
            Route::get('/report', [LaboratoryController::class, 'registrationReport'])->name('report');
            Route::get('/report-detailed', [LaboratoryController::class, 'registrationReportDetailed'])->name('report-detailed');
            Route::post('/{registration}/mark-completed', [LaboratoryController::class, 'markCompleted'])->name('mark-completed');
            Route::post('/{registration}/cancel', [LaboratoryController::class, 'cancel'])->name('cancel');
        });
    });

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/lab-types', [LabTypeController::class, 'index'])->name('lab-types.index');

    Route::prefix('icus')->name('icus.')->group(function () {
        Route::get('/new', [ICUController::class, 'new'])->name('new');
        Route::get('/approved', [ICUController::class, 'approved'])->name('approved');
        Route::get('/rejected', [ICUController::class, 'rejected'])->name('rejected');
        Route::get('/report', [ICUController::class, 'report'])->name('report');
    });

    Route::prefix('pacus')->name('pacus.')->group(function () {
        Route::get('/', [PACUController::class, 'index'])->name('index');
        Route::get('/completed', [PACUController::class, 'completed'])->name('completed');
        Route::get('/report', [PACUController::class, 'report'])->name('report');
    });

    Route::prefix('anesthesias')->name('anesthesias.')->group(function () {
        Route::get('/new', [AnesthesiaController::class, 'new'])->name('new');
        Route::get('/approved', [AnesthesiaController::class, 'approved'])->name('approved');
        Route::get('/rejected', [AnesthesiaController::class, 'rejected'])->name('rejected');
        Route::get('/report', [AnesthesiaController::class, 'report'])->name('report');
    });

    Route::prefix('operations')->name('operations.')->group(function () {
        Route::get('/new', [OperationController::class, 'new'])->name('new');
        Route::get('/approved', [OperationController::class, 'approved'])->name('approved');
        Route::get('/reserved', [OperationController::class, 'reserved'])->name('reserved');
        Route::get('/completed', [OperationController::class, 'completed'])->name('completed');
        Route::get('/report', [OperationController::class, 'report'])->name('report');
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
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::prefix('recipients')->name('recipients.')->group(function () {
        Route::get('/', [RecipientController::class, 'index'])->name('index');
        Route::get('/create', [RecipientController::class, 'create'])->name('create');
        Route::post('/', [RecipientController::class, 'store'])->name('store');
        Route::get('/{recipient}/edit', [RecipientController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{recipient}', [RecipientController::class, 'update'])->name('update');
        Route::delete('/{recipient}', [RecipientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('relations')->name('relations.')->group(function () {
        Route::get('/', [RelationController::class, 'index'])->name('index');
        Route::get('/create', [RelationController::class, 'create'])->name('create');
        Route::post('/', [RelationController::class, 'store'])->name('store');
        Route::get('/{relation}/edit', [RelationController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '/{relation}', [RelationController::class, 'update'])->name('update');
        Route::delete('/{relation}', [RelationController::class, 'destroy'])->name('destroy');
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
});
