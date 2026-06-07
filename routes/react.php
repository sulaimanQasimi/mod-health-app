<?php

use App\Http\Controllers\V1\AnesthesiaController;
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
        Route::get('/department-report', [AppointmentController::class, 'departmentReport'])->name('department-report');
        Route::get('/department', [AppointmentController::class, 'department'])->name('department');
        Route::get('/doctor', [AppointmentController::class, 'doctor'])->name('doctor');
        Route::get('/completed', [AppointmentController::class, 'completed'])->name('completed');
        Route::get('/report', [AppointmentController::class, 'report'])->name('report');
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
        Route::get('/', [PrescriptionController::class, 'index'])->name('index');
        Route::get('/delivered', [PrescriptionController::class, 'delivered'])->name('delivered');
        Route::get('/report', [PrescriptionController::class, 'report'])->name('report');
    });

    Route::get('/pharmacies', [PharmacyController::class, 'index'])->name('pharmacies.index');
    Route::get('/prescription-stocks', [PrescriptionStockController::class, 'index'])->name('prescription-stocks.index');

    Route::prefix('pharmacy-fulfillments')->name('pharmacy-fulfillments.')->group(function () {
        Route::get('/', [PharmacyFulfillmentController::class, 'index'])->name('index');
        Route::get('/stock', [PharmacyFulfillmentController::class, 'stock'])->name('stock');
    });

    Route::get('/incomes', [IncomeController::class, 'index'])->name('incomes.index');

    Route::prefix('outcomes')->name('outcomes.')->group(function () {
        Route::get('/', [OutcomeController::class, 'index'])->name('index');
        Route::get('/report', [OutcomeController::class, 'report'])->name('report');
    });

    Route::get('/medicine-types', [MedicineTypeController::class, 'index'])->name('medicine-types.index');

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

    Route::get('/vital-sign-types', [VitalSignTypeController::class, 'index'])->name('vital-sign-types.index');
    Route::get('/vital-signs', [VitalSignController::class, 'index'])->name('vital-signs.index');

    Route::prefix('laboratory')->name('laboratory.')->group(function () {
        Route::get('/scan', [LaboratoryController::class, 'scan'])->name('scan');
        Route::prefix('results')->name('results.')->group(function () {
            Route::get('/pending', [LaboratoryController::class, 'pending'])->name('pending');
            Route::get('/in-progress', [LaboratoryController::class, 'inProgress'])->name('in-progress');
            Route::get('/completed', [LaboratoryController::class, 'completed'])->name('completed');
            Route::get('/grouped', [LaboratoryController::class, 'grouped'])->name('grouped');
        });
        Route::prefix('registrations')->name('registrations.')->group(function () {
            Route::get('/report', [LaboratoryController::class, 'registrationReport'])->name('report');
            Route::get('/report-detailed', [LaboratoryController::class, 'registrationReportDetailed'])->name('report-detailed');
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

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::get('/recipients', [RecipientController::class, 'index'])->name('recipients.index');
    Route::get('/relations', [RelationController::class, 'index'])->name('relations.index');
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
    Route::get('/floors', [FloorController::class, 'index'])->name('floors.index');
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/beds', [BedController::class, 'index'])->name('beds.index');
    Route::get('/militery-types', [MiliteryTypeController::class, 'index'])->name('militery-types.index');
    Route::get('/procedure-types', [ProcedureTypeController::class, 'index'])->name('procedure-types.index');
    Route::get('/operation-types', [OperationTypeController::class, 'index'])->name('operation-types.index');
    Route::get('/medicines', [MedicineController::class, 'index'])->name('medicines.index');
    Route::get('/medicine-usage-types', [MedicineUsageTypeController::class, 'index'])->name('medicine-usage-types.index');
    Route::get('/food-types', [FoodTypeController::class, 'index'])->name('food-types.index');
    Route::get('/diseases', [DiseaseController::class, 'index'])->name('diseases.index');
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('/nurses', [NurseController::class, 'index'])->name('nurses.index');
});
