<?php

use App\Http\Controllers\DepotController;
use App\Http\Controllers\ReciptionStatisticReportController;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\AdviceController;
use App\Http\Controllers\AnesthesiaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryPageController;
use App\Http\Controllers\NursingAssessmentController;
use App\Http\Controllers\NutritionCareController;
use App\Http\Controllers\TestCategoryController;
use App\Http\Controllers\LabTestParameterController;
use App\Http\Controllers\PatientTestRegistrationController;
use App\Http\Controllers\TestResultController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\HospitalizationController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabTypeController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperationTypeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\BloodBankController;
use App\Http\Controllers\BloodBranchTransferController;
use App\Http\Controllers\BloodUnitController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ConsultationCommentController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DailyIcuProgressController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DiagnoseController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\FoodTypeController;
use App\Http\Controllers\ICUController;
use App\Http\Controllers\ICUProcedureController;
use App\Http\Controllers\ICUProcedureTypeController;
use App\Http\Controllers\LabItemController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineTypeController;
use App\Http\Controllers\MedicineUsageTypeController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\PACUController;
use App\Http\Controllers\PatientComplaintController;
use App\Http\Controllers\PrescriptionItemController;
use App\Http\Controllers\PrescriptionAlternativeItemController;
use App\Http\Controllers\RelationController;
use App\Http\Controllers\UnderReviewController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\HospitalizationPrescriptionAjaxController;
use App\Models\Prescription;
use App\Http\Controllers\MiliteryTypeController;
use App\Http\Controllers\PrescriptionStockController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\OutcomeController;
use App\Http\Controllers\PharmacyFulfillmentController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PhysiotherapyProcedureController;
use App\Http\Controllers\PhysiotherapyReportController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PhysiotherapyTypeController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\DiabetesChartController;
use App\Http\Controllers\MedicationAdministrationRecordController;
use App\Http\Controllers\DentistRegistrationController;
use App\Http\Controllers\DentalExaminationController;
use App\Http\Controllers\DentalTreatmentController;
use App\Http\Controllers\DentalXrayController;
use App\Http\Controllers\DentalNoteController;
use App\Http\Controllers\DentistAjaxController;
use App\Http\Controllers\DentalChartController;
use App\Http\Controllers\DentalChartAjaxController;
use App\Http\Controllers\DentalChartImageController;
use App\Http\Controllers\DentalPeriodontalController;
use App\Http\Controllers\ProstheticsDashboardController;
use App\Http\Controllers\ProstheticReferralController;
use App\Http\Controllers\ProstheticCaseController;
use App\Http\Controllers\ProstheticCatalogController;
use App\Http\Controllers\ProstheticStockController;
use App\Http\Controllers\ProstheticAttachmentController;
use App\Http\Controllers\ProstheticsPdfController;
use App\Http\Controllers\ProstheticsReportController;
Route::group(['middleware' => ['auth']], function () {

    // Home default route
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Log viewer route
    Route::get('/log-viewer', function () {
        return route('log-viewer');
    })->name('log-viewer');

    // Language change route
    Route::get('change_language/{lang?}', [HomeController::class, 'changeLanguage'])->name('change_language');

    // Recipients routes
    Route::prefix('recipients')->name('recipients.')->group(function () {
        Route::get('index', [RecipientController::class, 'index'])->name('index');
        Route::get('create', [RecipientController::class, 'create'])->name('create');
        Route::post('store', [RecipientController::class, 'store'])->name('store');
        Route::get('edit/{recipient}', [RecipientController::class, 'edit'])->name('edit');
        Route::put('update/{recipient}', [RecipientController::class, 'update'])->name('update');
        Route::get('destroy/{recipient}', [RecipientController::class, 'destroy'])->name('destroy');

    });

    // Users routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('index', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('store', [UserController::class, 'store'])->name('store');
        Route::get('edit/{user}', [UserController::class, 'edit'])->name('edit');
        Route::put('update/{user}', [UserController::class, 'update'])->name('update');
        Route::get('show/{user}', [UserController::class, 'show'])->name('show');
        Route::get('destroy/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('account/{user}', [UserController::class, 'account'])->name('account');
        Route::get('profile', [UserController::class, 'viewProfile'])->name('profile');
        Route::put('change-password', [UserController::class, 'changePassword'])->name('change-password');
        Route::post('update-status', [UserController::class, 'updateStatus'])->name('update-status');
        Route::post('/update-avatar', [UserController::class, 'updateAvatar'])->name('update-avatar');
    });

    // Doctor Performance Report routes
    Route::prefix('doctor-performance-report')->name('doctor-performance-report.')->group(function () {
        Route::get('performance', [\App\Http\Controllers\DoctorPerformanceReportController::class, 'index'])->name('performance');
        Route::post('fetch', [\App\Http\Controllers\DoctorPerformanceReportController::class, 'fetch'])->name('fetch');
    });

    // Roles routes
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('', [RoleController::class, 'index'])->name('index');
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::get('show/{role}', [RoleController::class, 'show'])->name('show');
        Route::post('store', [RoleController::class, 'store'])->name('store');
        Route::get('edit/{role}', [RoleController::class, 'edit'])->name('edit');
        Route::put('update/{role}', [RoleController::class, 'update'])->name('update');
        Route::get('destroy/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    // Permissions routes
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('', [PermissionController::class, 'index'])->name('index');
        Route::get('create', [PermissionController::class, 'create'])->name('create');
        Route::get('show/{permission}', [PermissionController::class, 'show'])->name('show');
        Route::post('store', [PermissionController::class, 'store'])->name('store');
        Route::get('edit/{permission}', [PermissionController::class, 'edit'])->name('edit');
        Route::put('update/{permission}', [PermissionController::class, 'update'])->name('update');
        Route::get('destroy/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });

    // Patients routes
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('index', [PatientController::class, 'index'])->name('index');
        Route::get('history/{patient:id}', [PatientController::class, 'history'])->name('history');
        Route::get('create', [PatientController::class, 'create'])->name('create');
        Route::get('show/{patient}', [PatientController::class, 'show'])->name('show');
        Route::get('edit/{patient}', [PatientController::class, 'edit'])->name('edit');
        Route::post('store', [PatientController::class, 'store'])->name('store');
        Route::put('update/{patient}', [PatientController::class, 'update'])->name('update');
        Route::get('/print-card/{patient}', [PatientController::class, 'printCard'])->name('print-card');
        Route::get('destroy/{patient}', [PatientController::class, 'destroy'])->name('destroy');
        Route::post('/patients/{id}/add-image', [PatientController::class, 'addImage'])->name('addImage');
        Route::get('webcam/{patient}', [PatientController::class, 'webcam'])->name('webcam');
        Route::post('capture/{id}', [PatientController::class, 'addImage'])->name('capture');
        Route::get('get-tab', [PatientController::class, 'getTab'])->name('get-tab');
        Route::get('get-doctors-by-department/{departmentId}', [PatientController::class, 'getDoctorsByDepartment'])->name('get-doctors-by-department');
        Route::get('report', [PatientController::class, 'report'])->name('report');
        Route::get('reciption-statistic-report', ReciptionStatisticReportController::class         )->name('reciption-statistic-report');
        Route::match(['get', 'post'], 'report-search', [PatientController::class, 'reportSearch'])->name('report-search');
        Route::match(['get', 'post'], 'export-report', [PatientController::class, 'exportReport'])->name('export-report');
        Route::get('{patientId}/printToken', [PatientController::class, 'printToken'])->name('printToken');
    });

    // Departments routes
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('index', [DepartmentController::class, 'index'])->name('index');
        Route::get('create', [DepartmentController::class, 'create'])->name('create');
        Route::get('show/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::post('store', [DepartmentController::class, 'store'])->name('store');
        Route::get('edit/{department}', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('update/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('destroy/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });

    //Sections routes
    Route::prefix('sections')->name('sections.')->group(function () {
        Route::get('index', [SectionController::class, 'index'])->name('index');
        Route::get('create', [SectionController::class, 'create'])->name('create');
        Route::get('show/{section}', [SectionController::class, 'show'])->name('show');
        Route::post('store', [SectionController::class, 'store'])->name('store');
        Route::get('edit/{section}', [SectionController::class, 'edit'])->name('edit');
        Route::put('update/{section}', [SectionController::class, 'update'])->name('update');
        Route::delete('destroy/{section}', [SectionController::class, 'destroy'])->name('destroy');
    });

    // Rooms routes
    Route::prefix('floors')->name('floors.')->group(function () {
        Route::get('index', [FloorController::class, 'index'])->name('index');
        Route::get('create', [FloorController::class, 'create'])->name('create');
        Route::get('show/{floor}', [FloorController::class, 'show'])->name('show');
        Route::post('store', [FloorController::class, 'store'])->name('store');
        Route::get('edit/{floor}', [FloorController::class, 'edit'])->name('edit');
        Route::put('update/{floor}', [FloorController::class, 'update'])->name('update');
        Route::delete('destroy/{floor}', [FloorController::class, 'destroy'])->name('destroy');
    });

    // Rooms routes
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('index', [RoomController::class, 'index'])->name('index');
        Route::get('create', [RoomController::class, 'create'])->name('create');
        Route::get('show/{room}', [RoomController::class, 'show'])->name('show');
        Route::post('store', [RoomController::class, 'store'])->name('store');
        Route::get('edit/{room}', [RoomController::class, 'edit'])->name('edit');
        Route::put('update/{room}', [RoomController::class, 'update'])->name('update');
        Route::delete('destroy/{room}', [RoomController::class, 'destroy'])->name('destroy');
    });

    // Beds routes
    Route::prefix('beds')->name('beds.')->group(function () {
        Route::get('index', [BedController::class, 'index'])->name('index');
        Route::get('create', [BedController::class, 'create'])->name('create');
        Route::get('show/{bed}', [BedController::class, 'show'])->name('show');
        Route::post('store', [BedController::class, 'store'])->name('store');
        Route::get('edit/{bed}', [BedController::class, 'edit'])->name('edit');
        Route::put('update/{bed}', [BedController::class, 'update'])->name('update');
        Route::delete('destroy/{bed}', [BedController::class, 'destroy'])->name('destroy');
    });

    // Hospitalizations routes
    Route::prefix('hospitalizations')->name('hospitalizations.')->group(function () {
        Route::get('/', [HospitalizationController::class, 'index'])->name('index');
        Route::get('discharged', [HospitalizationController::class, 'discharged'])->name('discharged');
        Route::get('create', [HospitalizationController::class, 'create'])->name('create');
        Route::get('show/{hospitalization}', [HospitalizationController::class, 'show'])->name('show');
        Route::post('store', [HospitalizationController::class, 'store'])->name('store');
        Route::get('edit/{hospitalization}', [HospitalizationController::class, 'edit'])->name('edit');
        Route::put('update/{hospitalization}', [HospitalizationController::class, 'update'])->name('update');
        Route::delete('destroy/{hospitalization}', [HospitalizationController::class, 'destroy'])->name('destroy');
        Route::get('report', [HospitalizationController::class, 'report'])->name('report');
        Route::post('report-search', [HospitalizationController::class, 'ReportSearch'])->name('report-search');
        Route::post('export-report', [HospitalizationController::class, 'exportReport'])->name('export-report');
        Route::put('hospitalizations/{id}', [HospitalizationController::class, 'updateHospitalization'])->name('updateHospitalization');
        Route::post('assign-doctor/{hospitalization}', [HospitalizationController::class, 'assignDoctor'])->name('assign-doctor');
        Route::get('change-room-bed/{hospitalization}', [HospitalizationController::class, 'changeRoomBed'])->name('changeRoomBed');
        Route::get('rooms-by-department', [HospitalizationController::class, 'roomsByDepartment'])->name('roomsByDepartment');
        Route::put('update-room-bed/{hospitalization}', [HospitalizationController::class, 'updateRoomBed'])->name('updateRoomBed');
        Route::get('room-management', [HospitalizationController::class, 'roomManagement'])->name('roomManagement')->middleware('role:admin|super_admin');
        Route::post('{hospitalization}/unoccupy-bed', [HospitalizationController::class, 'unoccupyBed'])->name('unoccupyBed')->middleware('role:admin|super_admin');
        Route::post('{hospitalization}/swap-bed', [HospitalizationController::class, 'swapBed'])->name('swapBed')->middleware('role:admin|super_admin');
        Route::post('{hospitalization}/swap-room', [HospitalizationController::class, 'swapRoom'])->name('swapRoom')->middleware('role:admin|super_admin');

        // AJAX section routes
        Route::get('diabetes-charts-section/{morphable_type}/{morphable_id}', [HospitalizationController::class, 'diabetesChartsSection'])->name('diabetes-charts-section');
        Route::get('medication-administration-records-section/{morphable_type}/{morphable_id}', [HospitalizationController::class, 'medicationAdministrationRecordsSection'])->name('medication-administration-records-section');
        Route::get('vital-signs-section/{morphable_type}/{morphable_id}', [HospitalizationController::class, 'vitalSignsSection'])->name('vital-signs-section');
        Route::get('nutrition-care-section/{morphable_type}/{morphable_id}', [HospitalizationController::class, 'nutritionCareSection'])->name('nutrition-care-section');
    });

    // Under Review routes
    Route::prefix('under_reviews')->name('under_reviews.')->group(function () {
        Route::get('index', [UnderReviewController::class, 'index'])->name('index');
        Route::get('create', [UnderReviewController::class, 'create'])->name('create');
        Route::get('show/{underReview}', [UnderReviewController::class, 'show'])->name('show');
        Route::post('store', [UnderReviewController::class, 'store'])->name('store');
        Route::get('edit/{underReview}', [UnderReviewController::class, 'edit'])->name('edit');
        Route::put('update/{underReview}', [UnderReviewController::class, 'update'])->name('update');
        Route::put('updateUnderReview/{underReview}', [UnderReviewController::class, 'updateUnderReview'])->name('updateUnderReview');
        Route::get('destroy/{underReview}', [UnderReviewController::class, 'destroy'])->name('destroy');
    });

    // Visits routes
    Route::prefix('visits')->name('visits.')->group(function () {
        Route::get('index', [VisitController::class, 'index'])->name('index');
        Route::get('create', [VisitController::class, 'create'])->name('create');
        // Route::get('show/{visit}', [VisitController::class, 'show'])->name('show');
        Route::post('store', [VisitController::class, 'store'])->name('store');
        Route::get('editUnderReviewVisit/{visit}', [VisitController::class, 'editUnderReviewVisit'])->name('edit');
        Route::put('update/{visit}', [VisitController::class, 'update'])->name('update');
        Route::put('updateUnderReviewVisit/{visit}', [VisitController::class, 'updateUnderReviewVisit'])->name('updateUnderReviewVisit');
        Route::delete('destroyUnderReviewVisit/{visit}', [VisitController::class, 'destroyUnderReviewVisit'])->name('destroyUnderReviewVisit');
        Route::delete('destroy/{visit}', [VisitController::class, 'destroyUnderReviewVisit'])->name('destroy');
    });

    // Doctors routes (admin and super_admin only)
    Route::prefix('doctors')->name('doctors.')->middleware('role:admin|super_admin')->group(function () {
        Route::get('index', [DoctorController::class, 'index'])->name('index');
        Route::get('create', [DoctorController::class, 'create'])->name('create');
        Route::get('show/{doctor}', [DoctorController::class, 'show'])->name('show');
        Route::post('store', [DoctorController::class, 'store'])->name('store');
        Route::get('edit/{doctor}', [DoctorController::class, 'edit'])->name('edit');
        Route::put('update/{doctor}', [DoctorController::class, 'update'])->name('update');
        Route::get('destroy/{doctor}', [DoctorController::class, 'destroy'])->name('destroy');
    });

    // Appointments routes
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('index', [AppointmentController::class, 'index'])->name('index');
        Route::get('doctorAppointments', [AppointmentController::class, 'doctorAppointments'])->name('doctorAppointments');
        Route::get('completedAppointments', [AppointmentController::class, 'completedAppointments'])->name('completedAppointments');
        Route::get('departmentAppointments', [AppointmentController::class, 'departmentAppointments'])->name('departmentAppointments');
        Route::get('get-doctors-by-clinic-type', [AppointmentController::class, 'getDoctorsByClinicType'])->name('get-doctors-by-clinic-type');
        Route::get('get-departments', [AppointmentController::class, 'getDepartments'])->name('get-departments');
        Route::post('assign-doctor/{appointment}', [AppointmentController::class, 'assignDoctor'])->name('assign-doctor');
        Route::post('accept/{appointment}', [AppointmentController::class, 'acceptAppointment'])->name('accept');
        Route::put('change-department/{appointment}', [AppointmentController::class, 'changeDepartment'])->name('change-department');
        Route::get('create', [AppointmentController::class, 'create'])->name('create');
        Route::get('show/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::post('store', [AppointmentController::class, 'store'])->name('store');
        // Route::get('edit/{appointment}', [AppointmentController::class, 'edit'])->name('edit');
        // Route::put('update/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::put('changeStatus/{appointment}', [AppointmentController::class, 'changeStatus'])->name('changeStatus');
        Route::get('destroy/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
        Route::get('report', [AppointmentController::class, 'report'])->name('report');
        Route::post('export-report', [AppointmentController::class, 'exportReport'])->name('export-report');
        Route::get('department-report', [AppointmentController::class, 'departmentReport'])->name('department-report');
        Route::get('{appointment}/printToken', [AppointmentController::class, 'printToken'])->name('printToken');
    });

    // Diagnoses routes
    Route::prefix('diagnoses')->name('diagnoses.')->group(function () {
        Route::get('index', [DiagnoseController::class, 'index'])->name('index');
        Route::get('create_diagnose/{appointment}', [DiagnoseController::class, 'createDiagnose'])->name('create_diagnose');
        Route::get('create', [DiagnoseController::class, 'create'])->name('create');
        Route::get('show/{diagnose}', [DiagnoseController::class, 'show'])->name('show');
        Route::post('store', [DiagnoseController::class, 'store'])->name('store');
        Route::get('edit/{diagnose}', [DiagnoseController::class, 'edit'])->name('edit');
        Route::put('update/{diagnose}', [DiagnoseController::class, 'update'])->name('update');
        Route::get('destroy/{diagnose}', [DiagnoseController::class, 'destroy'])->name('destroy');
    });

    // Diagnosis AJAX routes for Vue component
    Route::prefix('diagnosis-ajax')->name('diagnosis-ajax.')->group(function () {
        Route::get('appointment-diagnoses/{appointment}', [DiagnoseController::class, 'getAppointmentDiagnoses'])->name('appointment-diagnoses');
        Route::post('store', [DiagnoseController::class, 'ajaxStore'])->name('store');
        Route::put('update/{diagnose}', [DiagnoseController::class, 'ajaxUpdate'])->name('update');
        Route::delete('delete/{diagnose}', [DiagnoseController::class, 'ajaxDelete'])->name('delete');
    });

    // Prescriptions routes
    Route::prefix('prescriptions')
        ->middleware('pharmacy_role:manager,staff')
        ->name('prescriptions.')->group(function () {
        Route::get('/', [PrescriptionController::class, 'index'])->name('index');
        Route::get('delivered', [PrescriptionController::class, 'delivered'])->name('delivered');
        Route::get('create', [PrescriptionController::class, 'create'])->name('create');
        Route::get('show/{prescription}', [PrescriptionController::class, 'show'])->name('show');
        Route::post('store', [PrescriptionController::class, 'store'])->name('store');
        Route::get('edit/{prescription}', [PrescriptionController::class, 'edit'])->name('edit');
        Route::put('update/{prescription}', [PrescriptionController::class, 'update'])->name('update');
        Route::get('destroy/{prescription}', [PrescriptionController::class, 'destroy'])->name('destroy');
        Route::get('/print-card/{appointment}{prescriptionId}', [PrescriptionController::class, 'printCard'])->name('print-card');
        Route::get('/thermal-receipt/{prescription}', [PrescriptionController::class, 'printThermalReceipt'])->name('thermal-receipt');
        Route::get('/issue/{prescription}', [PrescriptionController::class, 'issue'])->name('issue');
        Route::get('/reject/{prescription}', [PrescriptionController::class, 'reject'])->name('reject');
        Route::post('/update-status/{prescriptionId}/{key}', [PrescriptionController::class, 'updateStatus']);
        Route::put('changeStatus/{prescription}', [PrescriptionController::class, 'changeStatus'])->name('changeStatus');
        Route::get('report', [PrescriptionController::class, 'report'])->middleware('pharmacy_role:manager')->name('report');
        Route::get('report-pharmacy-users/{pharmacy}', [PrescriptionController::class, 'reportPharmacyUsers'])->middleware('pharmacy_role:manager')->name('report-pharmacy-users');
        Route::post('report-search', [PrescriptionController::class, 'ReportSearch'])->middleware('pharmacy_role:manager')->name('report-search');
        Route::post('export-report', [PrescriptionController::class, 'exportReport'])->middleware('pharmacy_role:manager')->name('export-report');
        Route::post('export-prescriptions', [PrescriptionController::class, 'exportPrescriptions'])->middleware('pharmacy_role:manager')->name('export-prescriptions');
        Route::post('bulk-update-status', [PrescriptionController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::post('bulk-delete', [PrescriptionController::class, 'bulkDelete'])->name('bulk-delete');

    });

    // Labratory routes
    Route::prefix('lab_tests')->name('lab_tests.')->group(function () {
        Route::get('index', [LabController::class, 'index'])->name('index');
        Route::get('completed', [LabController::class, 'completed'])->name('completed');
        Route::get('create', [LabController::class, 'create'])->name('create');
        Route::get('show/{lab}', [LabController::class, 'show'])->name('show');
        Route::post('store', [LabController::class, 'store'])->name('store');
        Route::get('edit/{lab}', [LabController::class, 'edit'])->name('edit');
        Route::put('update/{lab}', [LabController::class, 'update'])->name('update');
        Route::delete('destroy/{lab}', [LabController::class, 'destroy'])->name('destroy');
        Route::get('/print-card/{lab}', [LabController::class, 'printCard'])->name('print-card');
        Route::get('report', [LabController::class, 'report'])->name('report');
        Route::post('report-search', [LabController::class, 'ReportSearch'])->name('report-search');
        Route::post('export-report', [LabController::class, 'exportReport'])->name('export-report');
    });

    // Relations routes
    Route::prefix('relations')->name('relations.')->group(function () {
        Route::get('index', [RelationController::class, 'index'])->name('index');
        Route::get('create', [RelationController::class, 'create'])->name('create');
        Route::get('show/{relation}', [RelationController::class, 'show'])->name('show');
        Route::post('store', [RelationController::class, 'store'])->name('store');
        Route::get('edit/{relation}', [RelationController::class, 'edit'])->name('edit');
        Route::put('update/{relation}', [RelationController::class, 'update'])->name('update');
        Route::delete('destroy/{relation}', [RelationController::class, 'destroy'])->name('destroy');
    });

    // Complaints routes
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('index', [PatientComplaintController::class, 'index'])->name('index');
        Route::get('create', [PatientComplaintController::class, 'create'])->name('create');
        Route::get('show/{complaint}', [PatientComplaintController::class, 'show'])->name('show');
        Route::post('store', [PatientComplaintController::class, 'store'])->name('store');
        Route::get('edit/{complaint}', [PatientComplaintController::class, 'edit'])->name('edit');
        Route::put('update/{complaint}', [PatientComplaintController::class, 'update'])->name('update');
        Route::get('destroy/{complaint}', [PatientComplaintController::class, 'destroy'])->name('destroy');
    });


    // Laboratory test types routes
    Route::prefix('lab_types')->name('lab_types.')->group(function () {
        Route::get('/', [LabTypeController::class, 'index'])->name('index');
        Route::get('create', [LabTypeController::class, 'create'])->name('create');
        Route::post('store', [LabTypeController::class, 'store'])->name('store');
        Route::get('{labType}/edit', [LabTypeController::class, 'edit'])->name('edit');
        Route::put('{labType}', [LabTypeController::class, 'update'])->name('update');
        Route::delete('{labType}', [LabTypeController::class, 'destroy'])->name('destroy');
    });

    // Lab Types API routes
    Route::prefix('api/lab-types')->name('api.lab-types.')->group(function () {
        Route::get('/', [LabTypeController::class, 'index'])->name('index');
        Route::post('/', [LabTypeController::class, 'store'])->name('store');
        Route::get('{labType}', [LabTypeController::class, 'show'])->name('show');
        Route::put('{labType}', [LabTypeController::class, 'update'])->name('update');
        Route::delete('{labType}', [LabTypeController::class, 'destroy'])->name('destroy');
        Route::post('{id}/restore', [LabTypeController::class, 'restore'])->name('restore');
        Route::get('select/dropdown', [LabTypeController::class, 'getLabTypesForSelect'])->name('select');
        Route::get('{labType}/parameters', [LabTestParameterController::class, 'apiIndexByLabType'])->name('parameters');
    });

    // Categories API routes
    Route::prefix('api/categories')->name('api.categories.')->group(function () {
        Route::get('/', function() {
            $categories = Cache::remember('lab_types_categories', 300, function () {
                return \App\Models\Category::select('id', 'name')->orderBy('name')->get();
            });
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        })->name('index');
    });

    // Test route
    Route::get('/test-api', function() {
        return response()->json(['success' => true, 'message' => 'API is working']);
    });


    Route::prefix('lab_items')->name('lab_items.')->group(function () {
        Route::get('getItems/{id}', [LabItemController::class, 'getItems'])->name('getItems');
        Route::get('updateStatus/{id}/update-status', [LabItemController::class, 'updateStatus'])->name('updateStatus');
        Route::get('deleteItem/{id}/delete-item', [LabItemController::class, 'deleteItem'])->name('deleteItem');
        Route::get('editItem/{item}/edit-item', [LabItemController::class, 'edit'])->name('editItem');
        Route::put('/lab-items/{item}', [LabItemController::class, 'update'])->name('updateItem');
        Route::delete('destroy/{item}', [LabItemController::class, 'destroy'])->name('destroyItem');

    });

    Route::prefix('prescription_items')->name('prescription_items.')->group(function () {
        Route::get('getItems/{id}', [PrescriptionItemController::class, 'getItems'])->name('getItems');
        Route::get('changeStatus/{id}/update-status', [PrescriptionItemController::class, 'changeStatus'])->name('changeStatus');
        Route::get('deleteItem/{id}/delete-item', [PrescriptionItemController::class, 'deleteItem'])->name('deleteItem');
        Route::get('editItem/{item}/edit-item', [PrescriptionItemController::class, 'edit'])->name('editItem');
        Route::put('/prescription-items/{item}', [PrescriptionItemController::class, 'update'])->name('updateItem');

    });

    // Prescription Alternative Items routes
    Route::prefix('prescription_alternative_items')->name('prescription_alternative_items.')->group(function () {
        Route::post('store', [PrescriptionAlternativeItemController::class, 'store'])->name('store');
        Route::put('update/{alternativeItem}', [PrescriptionAlternativeItemController::class, 'update'])->name('update');
        Route::delete('destroy/{alternativeItem}', [PrescriptionAlternativeItemController::class, 'destroy'])->name('destroy');
        Route::get('select/{alternativeItem}', [PrescriptionAlternativeItemController::class, 'selectAlternative'])->name('select');
        Route::get('changeStatus/{alternativeItem}', [PrescriptionAlternativeItemController::class, 'changeStatus'])->name('changeStatus');
    });

    // Branches routes
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('index', [BranchController::class, 'index'])->name('index');
        Route::get('create', [BranchController::class, 'create'])->name('create');
        Route::get('show/{branch}', [BranchController::class, 'show'])->name('show');
        Route::post('store', [BranchController::class, 'store'])->name('store');
        Route::get('edit/{branch}', [BranchController::class, 'edit'])->name('edit');
        Route::put('update/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('destroy/{branch}', [BranchController::class, 'destroy'])->name('destroy');
    });

    // Consultations routes
    Route::prefix('consultations')->name('consultations.')->group(function () {
        Route::get('index', [ConsultationController::class, 'index'])->name('index');
        Route::get('create', [ConsultationController::class, 'create'])->name('create');
        Route::get('show/{consultation}', [ConsultationController::class, 'show'])->name('show');
        Route::post('store', [ConsultationController::class, 'store'])->name('store');
        Route::get('edit/{consultation}', [ConsultationController::class, 'edit'])->name('edit');
        Route::put('update/{consultation}', [ConsultationController::class, 'update'])->name('update');
        Route::get('destroy/{consultation}', [ConsultationController::class, 'destroy'])->name('destroy');
    });

    // Consultation comments routes
    Route::prefix('consultation_comments')->name('consultation_comments.')->group(function () {
        Route::get('index', [ConsultationCommentController::class, 'index'])->name('index');
        Route::get('create', [ConsultationCommentController::class, 'create'])->name('create');
        Route::get('show/{comment}', [ConsultationCommentController::class, 'show'])->name('show');
        Route::post('store', [ConsultationCommentController::class, 'store'])->name('store');
        Route::get('edit/{comment}', [ConsultationCommentController::class, 'edit'])->name('edit');
        Route::put('update/{comment}', [ConsultationCommentController::class, 'update'])->name('update');
        Route::get('destroy/{comment}', [ConsultationCommentController::class, 'destroy'])->name('destroy');
    });

    // Operation types routes
    Route::prefix('operation_types')->name('operation_types.')->group(function () {
        Route::get('index', [OperationTypeController::class, 'index'])->name('index');
        Route::get('create', [OperationTypeController::class, 'create'])->name('create');
        Route::get('show/{operationType}', [OperationTypeController::class, 'show'])->name('show');
        Route::post('store', [OperationTypeController::class, 'store'])->name('store');
        Route::get('edit/{operationType}', [OperationTypeController::class, 'edit'])->name('edit');
        Route::put('update/{operationType}', [OperationTypeController::class, 'update'])->name('update');
        Route::delete('destroy/{operationType}', [OperationTypeController::class, 'destroy'])->name('destroy');
    });

    // Operations routes
    Route::prefix('operations')->name('operations.')->group(function () {
        Route::get('new', [OperationController::class, 'new'])->name('new');
        Route::get('approved', [OperationController::class, 'approved'])->name('approved');
        Route::get('reserved', [OperationController::class, 'reserved'])->name('reserved');
        Route::get('completed', [OperationController::class, 'completed'])->name('completed');
        Route::get('create', [OperationController::class, 'create'])->name('create');
        Route::get('show/{operation}', [OperationController::class, 'show'])->name('show');
        Route::post('store', [OperationController::class, 'store'])->name('store');
        Route::get('edit/{operation}', [OperationController::class, 'edit'])->name('edit');
        Route::put('update/{operation}', [OperationController::class, 'update'])->name('update');
        Route::put('complete/{operation:id}', [OperationController::class, 'complete'])->name('complete');
        Route::get('destroy/{operation}', [OperationController::class, 'destroy'])->name('destroy');
        Route::put('/operation/{operationId}/reserve', [OperationController::class, 'reserveOperation'])->name('reserve');
        Route::get('/operation/{operationId}/unreserve', [OperationController::class, 'unreserveOperation'])->name('unreserve');
        Route::get('report', [OperationController::class, 'report'])->name('report');
        Route::post('report-search', [OperationController::class, 'ReportSearch'])->name('report-search');
        Route::post('export-report', [OperationController::class, 'exportReport'])->name('export-report');
    });

    // ICUs routes
    Route::prefix('icus')->name('icus.')->group(function () {
        Route::get('index', [ICUController::class, 'index'])->name('index');
        Route::get('new', [ICUController::class, 'new'])->name('new');
        Route::get('approved', [ICUController::class, 'approved'])->name('approved');
        Route::get('rejected', [ICUController::class, 'rejected'])->name('rejected');
        Route::get('create', [ICUController::class, 'create'])->name('create');
        Route::get('show/{icu}', [ICUController::class, 'show'])->name('show');
        Route::post('store', [ICUController::class, 'store'])->name('store');
        Route::get('edit/{icu}', [ICUController::class, 'edit'])->name('edit');
        Route::put('update/{icu}', [ICUController::class, 'update'])->name('update');
        Route::put('update/{icu}/note', [ICUController::class, 'updateICU'])->name('updateICU');
        Route::delete('destroy/{icu}', [ICUController::class, 'destroy'])->name('destroy');
        Route::get('report', [ICUController::class, 'report'])->name('report');
        Route::post('report-search', [ICUController::class, 'ReportSearch'])->name('report-search');
        Route::post('export-report', [ICUController::class, 'exportReport'])->name('export-report');
        Route::get('/print-death-card/{icu}', [ICUController::class, 'printDeathCard'])->name('print-death-card');
        Route::get('/print-move-card/{icu}', [ICUController::class, 'printMoveCard'])->name('print-move-card');
    });

    // PACUs routes
    Route::prefix('pacus')->name('pacus.')->group(function () {
        Route::get('index', [PACUController::class, 'index'])->name('index');
        Route::get('completed', [PACUController::class, 'completed'])->name('completed');
        Route::get('create', [PACUController::class, 'create'])->name('create');
        Route::get('show/{pacu}', [PACUController::class, 'show'])->name('show');
        Route::post('store', [PACUController::class, 'store'])->name('store');
        Route::get('edit/{pacu}', [PACUController::class, 'edit'])->name('edit');
        Route::put('update/{pacu}', [PACUController::class, 'update'])->name('update');
        Route::delete('destroy/{pacu}', [PACUController::class, 'destroy'])->name('destroy');
        Route::get('complete/{pacuId}', [PACUController::class, 'complete'])->name('complete');
        Route::get('report', [PACUController::class, 'report'])->name('report');
        Route::post('report-search', [PACUController::class, 'ReportSearch'])->name('report-search');
        Route::post('export-report', [PACUController::class, 'exportReport'])->name('export-report');

    });

    // ICU Procedure types routes
    Route::prefix('procedure_types')->name('procedure_types.')->group(function () {
        Route::get('index', [ICUProcedureTypeController::class, 'index'])->name('index');
        Route::get('create', [ICUProcedureTypeController::class, 'create'])->name('create');
        Route::get('show/{iCUProcedureType}', [ICUProcedureTypeController::class, 'show'])->name('show');
        Route::post('store', [ICUProcedureTypeController::class, 'store'])->name('store');
        Route::get('edit/{iCUProcedureType}', [ICUProcedureTypeController::class, 'edit'])->name('edit');
        Route::put('update/{iCUProcedureType}', [ICUProcedureTypeController::class, 'update'])->name('update');
        Route::delete('destroy/{iCUProcedureType}', [ICUProcedureTypeController::class, 'destroy'])->name('destroy');

    });

    // ICU Procedures routes
    Route::prefix('procedures')->name('procedures.')->group(function () {
        Route::get('index', [ICUProcedureController::class, 'index'])->name('index');
        Route::get('create', [ICUProcedureController::class, 'create'])->name('create');
        Route::get('show/{iCUProcedure}', [ICUProcedureController::class, 'show'])->name('show');
        Route::post('store', [ICUProcedureController::class, 'store'])->name('store');
        Route::get('edit/{iCUProcedure}', [ICUProcedureController::class, 'edit'])->name('edit');
        Route::put('update/{iCUProcedure}', [ICUProcedureController::class, 'update'])->name('update');
        Route::delete('destroy/{iCUProcedure}', [ICUProcedureController::class, 'destroy'])->name('destroy');

    });

    // Anesthesia routes
    Route::prefix('anesthesias')->name('anesthesias.')->group(function () {
        Route::get('index', [AnesthesiaController::class, 'index'])->name('index');
        Route::get('new', [AnesthesiaController::class, 'new'])->name('new');
        Route::get('approved', [AnesthesiaController::class, 'approved'])->name('approved');
        Route::get('rejected', [AnesthesiaController::class, 'rejected'])->name('rejected');
        Route::get('create', [AnesthesiaController::class, 'create'])->name('create');
        Route::get('show/{anesthesia}', [AnesthesiaController::class, 'show'])->name('show');
        Route::post('store', [AnesthesiaController::class, 'store'])->name('store');
        Route::get('edit/{anesthesia}', [AnesthesiaController::class, 'edit'])->name('edit');
        Route::put('update/{anesthesia}', [AnesthesiaController::class, 'update'])->name('update');
        Route::put('updateAnesthesia/{anesthesia}', [AnesthesiaController::class, 'updateAnesthesia'])->name('updateAnesthesia');
        Route::delete('destroy/{anesthesia}', [AnesthesiaController::class, 'destroy'])->name('destroy');
        Route::get('report', [AnesthesiaController::class, 'report'])->name('report');
        Route::post('report-search', [AnesthesiaController::class, 'ReportSearch'])->name('report-search');
        Route::post('export-report', [AnesthesiaController::class, 'exportReport'])->name('export-report');
    });

    // Medicine types routes
    Route::prefix('medicine_types')->name('medicine_types.')->group(function () {
        Route::get('index', [MedicineTypeController::class, 'index'])->name('index');
        Route::get('create', [MedicineTypeController::class, 'create'])->name('create');
        Route::get('show/{medicineType}', [MedicineTypeController::class, 'show'])->name('show');
        Route::post('store', [MedicineTypeController::class, 'store'])->name('store');
        Route::get('edit/{medicineType}', [MedicineTypeController::class, 'edit'])->name('edit');
        Route::put('update/{medicineType}', [MedicineTypeController::class, 'update'])->name('update');
        Route::delete('destroy/{medicineType}', [MedicineTypeController::class, 'destroy'])->name('destroy');
    });

    // Medicines routes
    Route::prefix('medicines')->name('medicines.')->group(function () {
        Route::get('index', [MedicineController::class, 'index'])->name('index');
        Route::get('create', [MedicineController::class, 'create'])->name('create');
        Route::get('show/{medicine}', [MedicineController::class, 'show'])->name('show');
        Route::post('store', [MedicineController::class, 'store'])->name('store');
        Route::get('edit/{medicine}', [MedicineController::class, 'edit'])->name('edit');
        Route::put('update/{medicine}', [MedicineController::class, 'update'])->name('update');
        Route::delete('destroy/{medicine}', [MedicineController::class, 'destroy'])->name('destroy');
    });

    // Daily ICU Progress routes
    Route::prefix('daily_icu_progress')->name('daily_icu_progress.')->group(function () {
        Route::get('index', [DailyIcuProgressController::class, 'index'])->name('index');
        Route::get('create', [DailyIcuProgressController::class, 'create'])->name('create');
        Route::get('show/{dailyIcuProgress}', [DailyIcuProgressController::class, 'show'])->name('show');
        Route::post('store', [DailyIcuProgressController::class, 'store'])->name('store');
        Route::get('edit/{dailyIcuProgress}', [DailyIcuProgressController::class, 'edit'])->name('edit');
        Route::put('update/{dailyIcuProgress}', [DailyIcuProgressController::class, 'update'])->name('update');
        Route::get('destroy/{dailyIcuProgress}', [DailyIcuProgressController::class, 'destroy'])->name('destroy');
    });

    // Food types routes
    Route::prefix('food_types')->name('food_types.')->group(function () {
        Route::get('index', [FoodTypeController::class, 'index'])->name('index');
        Route::get('create', [FoodTypeController::class, 'create'])->name('create');
        Route::get('show/{foodType}', [FoodTypeController::class, 'show'])->name('show');
        Route::post('store', [FoodTypeController::class, 'store'])->name('store');
        Route::get('edit/{foodType}', [FoodTypeController::class, 'edit'])->name('edit');
        Route::put('update/{foodType}', [FoodTypeController::class, 'update'])->name('update');
        Route::delete('destroy/{foodType}', [FoodTypeController::class, 'destroy'])->name('destroy');
    });

    // Blood bank routes
    Route::prefix('blood_banks')->name('blood_banks.')->group(function () {
        Route::get('dashboard', [BloodBankController::class, 'dashboard'])->name('dashboard');
        Route::get('inventory', [BloodUnitController::class, 'index'])->name('inventory');
        Route::post('inventory', [BloodUnitController::class, 'store'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('inventory.store');
        Route::get('inventory/{bloodUnit}', [BloodUnitController::class, 'show'])->name('inventory.show');
        Route::post('inventory/{bloodUnit}/tests', [BloodUnitController::class, 'saveTests'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('inventory.tests.save');
        Route::post('inventory/{bloodUnit}/approve-after-tests', [BloodUnitController::class, 'approveAfterTests'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('inventory.tests.approve');
        Route::post('inventory/{bloodUnit}/discard', [BloodUnitController::class, 'discard'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('inventory.discard');
        Route::post('inventory/{bloodUnit}/quarantine', [BloodUnitController::class, 'quarantine'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('inventory.quarantine');
        Route::post('inventory/{bloodUnit}/release-quarantine', [BloodUnitController::class, 'releaseQuarantine'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('inventory.release_quarantine');
        Route::get('movements', [BloodBankController::class, 'stockMovements'])->name('movements');
        Route::get('new', [BloodBankController::class, 'new'])->name('new');
        Route::get('approved', [BloodBankController::class, 'approved'])->name('approved');
        Route::get('rejected', [BloodBankController::class, 'rejected'])->name('rejected');
        Route::get('delivered', [BloodBankController::class, 'delivered'])->name('delivered');
        Route::get('nurses-by-department/{department}', [BloodBankController::class, 'nursesByDepartment'])->name('nurses_by_department');
        Route::get('create', [BloodBankController::class, 'create'])->name('create');
        Route::get('show/{bloodBank}', [BloodBankController::class, 'show'])->name('show');
        Route::post('store', [BloodBankController::class, 'store'])->name('store');
        Route::get('edit/{bloodBank}', [BloodBankController::class, 'edit'])->name('edit');
        Route::put('update/{bloodBank}', [BloodBankController::class, 'update'])->name('update');
        Route::delete('destroy/{bloodBank}', [BloodBankController::class, 'destroy'])->name('destroy');
        Route::get('approve/{bloodBank}', [BloodBankController::class, 'approve'])->name('approve');
        Route::post('deliver/{bloodBank}', [BloodBankController::class, 'deliver'])->name('deliver');
        Route::post('{bloodBank}/crossmatch/samples', [BloodBankController::class, 'storePatientSample'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('crossmatch.samples.store');
        Route::post('{bloodBank}/blood-check', [BloodBankController::class, 'storeBloodCheck'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('blood_check.store');
        Route::post('{bloodBank}/crossmatch/units/{bloodUnit}', [BloodBankController::class, 'saveCrossmatch'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('crossmatch.save');
        Route::post('{bloodBank}/crossmatch/{crossmatch}/override', [BloodBankController::class, 'overrideCrossmatch'])
            ->middleware('permission:manage-blood-inventory')
            ->name('crossmatch.override');
        Route::post('{bloodBank}/crossmatch/{crossmatch}/reserve', [BloodBankController::class, 'reserveCrossmatchUnit'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('crossmatch.reserve');
        Route::post('{bloodBank}/crossmatch/units/{bloodUnit}/unreserve', [BloodBankController::class, 'unreserveCrossmatchUnit'])
            ->middleware('permission:receive-blood-units|manage-blood-inventory')
            ->name('crossmatch.unreserve');
        Route::put('reject/{bloodBank}', [BloodBankController::class, 'reject'])->name('reject');
        Route::get('report', [BloodBankController::class, 'report'])->name('report');
        Route::post('report-search', [BloodBankController::class, 'ReportSearch'])->name('report-search');
        Route::post('export-report', [BloodBankController::class, 'exportReport'])->name('export-report');

        Route::get('branch-transfers', [BloodBranchTransferController::class, 'index'])->name('branch_transfers.index');
        Route::get('branch-transfers/create', [BloodBranchTransferController::class, 'create'])->name('branch_transfers.create');
        Route::post('branch-transfers', [BloodBranchTransferController::class, 'store'])->name('branch_transfers.store');
        Route::get('branch-transfers/{branchTransfer}', [BloodBranchTransferController::class, 'show'])->name('branch_transfers.show');
        Route::put('branch-transfers/{branchTransfer}/reject', [BloodBranchTransferController::class, 'reject'])->name('branch_transfers.reject');
        Route::post('branch-transfers/{branchTransfer}/fulfill', [BloodBranchTransferController::class, 'fulfill'])->name('branch_transfers.fulfill');
        Route::post('branch-transfers/{branchTransfer}/cancel', [BloodBranchTransferController::class, 'cancel'])->name('branch_transfers.cancel');
    });

    // Prosthetics & orthotics (artificial parts) module
    Route::prefix('prosthetics')->name('prosthetics.')->middleware('permission:show-prosthetics-menu')->group(function () {
        Route::get('dashboard', [ProstheticsDashboardController::class, 'index'])->name('dashboard');

        Route::get('referrals/patients/search', [ProstheticReferralController::class, 'searchPatients'])->name('referrals.patients.search');
        Route::post('referrals/{referral}/accept', [ProstheticReferralController::class, 'accept'])->name('referrals.accept');
        Route::post('referrals/{referral}/reject', [ProstheticReferralController::class, 'reject'])->name('referrals.reject');
        Route::post('referrals/{referral}/convert', [ProstheticReferralController::class, 'convertToCase'])->name('referrals.convert');
        Route::resource('referrals', ProstheticReferralController::class);

        Route::resource('cases', ProstheticCaseController::class)->parameters(['cases' => 'prosthetic_case'])->only(['index', 'create', 'store', 'show']);
        Route::post('cases/{prosthetic_case}/assessment', [ProstheticCaseController::class, 'saveAssessment'])->name('cases.assessment');
        Route::post('cases/{prosthetic_case}/measurements', [ProstheticCaseController::class, 'saveMeasurements'])->name('cases.measurements');
        Route::post('cases/{prosthetic_case}/measurements/lock', [ProstheticCaseController::class, 'lockMeasurements'])->name('cases.measurements.lock');
        Route::post('cases/{prosthetic_case}/prescription', [ProstheticCaseController::class, 'savePrescription'])->name('cases.prescription');
        Route::post('cases/{prosthetic_case}/estimate', [ProstheticCaseController::class, 'updateEstimate'])->name('cases.estimate');
        Route::post('cases/{prosthetic_case}/submit-approval', [ProstheticCaseController::class, 'submitForApproval'])->name('cases.submit_approval');
        Route::post('cases/{prosthetic_case}/approve', [ProstheticCaseController::class, 'approveCase'])->name('cases.approve');
        Route::post('cases/{prosthetic_case}/work-order', [ProstheticCaseController::class, 'createWorkOrder'])->name('cases.work_order');
        Route::put('work-orders/{prosthetic_work_order}', [ProstheticCaseController::class, 'updateWorkOrder'])->name('work_orders.update');
        Route::post('cases/{prosthetic_case}/issue-stock', [ProstheticCaseController::class, 'issueStock'])->name('cases.issue_stock');
        Route::post('cases/{prosthetic_case}/fitting', [ProstheticCaseController::class, 'storeFitting'])->name('cases.fitting');
        Route::post('cases/{prosthetic_case}/delivery', [ProstheticCaseController::class, 'storeDelivery'])->name('cases.delivery');
        Route::post('cases/{prosthetic_case}/follow-up', [ProstheticCaseController::class, 'storeFollowUp'])->name('cases.follow_up');
        Route::post('cases/{prosthetic_case}/close', [ProstheticCaseController::class, 'closeCase'])->name('cases.close');

        Route::get('cases/{prosthetic_case}/attachments', [ProstheticAttachmentController::class, 'index'])->name('cases.attachments.index');
        Route::post('cases/{prosthetic_case}/attachments/upload', [ProstheticAttachmentController::class, 'upload'])->name('cases.attachments.upload');
        Route::delete('attachments/{attachment}', [ProstheticAttachmentController::class, 'delete'])->name('attachments.delete');

        Route::get('cases/{prosthetic_case}/print', [ProstheticsPdfController::class, 'caseSummary'])->name('cases.print');

        Route::get('catalog', [ProstheticCatalogController::class, 'index'])->name('catalog.index');
        Route::get('catalog/create', [ProstheticCatalogController::class, 'create'])->middleware('permission:manage-prosthetics-catalog')->name('catalog.create');
        Route::post('catalog', [ProstheticCatalogController::class, 'store'])->middleware('permission:manage-prosthetics-catalog')->name('catalog.store');
        Route::get('catalog/{item}/edit', [ProstheticCatalogController::class, 'edit'])->middleware('permission:manage-prosthetics-catalog')->name('catalog.edit');
        Route::put('catalog/{item}', [ProstheticCatalogController::class, 'update'])->middleware('permission:manage-prosthetics-catalog')->name('catalog.update');

        Route::get('stock', [ProstheticStockController::class, 'index'])->name('stock.index');
        Route::post('stock/receive', [ProstheticStockController::class, 'receive'])->middleware('permission:manage-prosthetics-stock')->name('stock.receive');

        Route::get('reports', [ProstheticsReportController::class, 'index'])->name('reports.index');
    });


    // Advices routes
    Route::prefix('advices')->name('advices.')->group(function () {
        Route::get('index', [AdviceController::class, 'index'])->name('index');
        Route::get('create', [AdviceController::class, 'create'])->name('create');
        Route::get('show/{advice}', [AdviceController::class, 'show'])->name('show');
        Route::post('store', [AdviceController::class, 'store'])->name('store');
        Route::get('edit/{advice}', [AdviceController::class, 'edit'])->name('edit');
        Route::put('update/{advice}', [AdviceController::class, 'update'])->name('update');
        Route::get('destroy/{advice}', [AdviceController::class, 'destroy'])->name('destroy');
    });

    // Medicine Usage Types routes
    Route::prefix('medicine_usage_types')->name('medicine_usage_types.')->group(function () {
        Route::get('index', [MedicineUsageTypeController::class, 'index'])->name('index');
        Route::get('create', [MedicineUsageTypeController::class, 'create'])->name('create');
        Route::get('show/{medicineUsageType}', [MedicineUsageTypeController::class, 'show'])->name('show');
        Route::post('store', [MedicineUsageTypeController::class, 'store'])->name('store');
        Route::get('edit/{medicineUsageType}', [MedicineUsageTypeController::class, 'edit'])->name('edit');
        Route::put('update/{medicineUsageType}', [MedicineUsageTypeController::class, 'update'])->name('update');
        Route::delete('destroy/{medicineUsageType}', [MedicineUsageTypeController::class, 'destroy'])->name('destroy');
    });

    // Diseases routes
    Route::prefix('diseases')->name('diseases.')->group(function () {
        Route::get('index', [DiseaseController::class, 'index'])->name('index');
        Route::get('create', [DiseaseController::class, 'create'])->name('create');
        Route::get('show/{disease}', [DiseaseController::class, 'show'])->name('show');
        Route::post('store', [DiseaseController::class, 'store'])->name('store');
        Route::get('edit/{disease}', [DiseaseController::class, 'edit'])->name('edit');
        Route::put('update/{disease}', [DiseaseController::class, 'update'])->name('update');
        Route::delete('destroy/{disease}', [DiseaseController::class, 'destroy'])->name('destroy');
    });

    // Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('index', [ReportController::class, 'index'])->name('index');

    });

    // Prescription Stock routes
    Route::prefix('prescription_stocks')
        ->middleware(['permission:show-prescriptions-menu', 'pharmacy_role:manager'])
        ->name('prescription_stocks.')->group(function () {
            Route::get('/', [PrescriptionStockController::class, 'index'])->name('index');
        });

    // Income routes
    Route::prefix('incomes')
        ->middleware(['permission:show-prescriptions-menu', 'pharmacy_role:manager'])

        ->name('incomes.')->group(function () {
            Route::get('/', [IncomeController::class, 'index'])->name('index');
            Route::get('create', [IncomeController::class, 'create'])->name('create');
            Route::post('store', [IncomeController::class, 'store'])->name('store');
        });

    // Outcome routes
    Route::prefix('outcomes')
        ->middleware(['permission:show-prescriptions-menu', 'pharmacy_role:manager'])
        ->name('outcomes.')->group(function () {
            Route::get('/', [OutcomeController::class, 'index'])->name('index');
            Route::post('export-index-report', [OutcomeController::class, 'exportIndexReport'])->name('export-index-report');
            Route::get('report', [OutcomeController::class, 'report'])->name('report');
            Route::post('report-search', [OutcomeController::class, 'reportSearch'])->name('report-search');
            Route::post('export-report', [OutcomeController::class, 'exportReport'])->name('export-report');
        });

    // Pharmacy Fulfillment routes
    Route::prefix('pharmacy_fulfillments')
        ->middleware('pharmacy_role:manager,procurement')
        ->name('pharmacy_fulfillments.')->group(function () {
            Route::get('/', [PharmacyFulfillmentController::class, 'index'])->name('index');
            Route::get('stock', [PharmacyFulfillmentController::class, 'stock'])->name('stock');
            Route::get('create', [PharmacyFulfillmentController::class, 'create'])->name('create');
            Route::post('store', [PharmacyFulfillmentController::class, 'store'])->name('store');
            Route::get('show/{pharmacy_fulfillment}', [PharmacyFulfillmentController::class, 'show'])->name('show');
            Route::get('edit/{pharmacy_fulfillment}', [PharmacyFulfillmentController::class, 'edit'])->name('edit');
            Route::put('update/{pharmacy_fulfillment}', [PharmacyFulfillmentController::class, 'update'])->name('update');
            Route::delete('destroy/{pharmacy_fulfillment}', [PharmacyFulfillmentController::class, 'destroy'])->name('destroy');
            Route::post('export-report', [PharmacyFulfillmentController::class, 'exportReport'])->name('export-report');
        });



    // Physiotherapy Procedure routes (only for modals in appointment show page)
    Route::prefix('physiotherapy-procedures')
        ->middleware('permission:show-physiotherapy-menu')
        ->name('physiotherapy-procedures.')->group(function () {
            Route::get('/', [PhysiotherapyProcedureController::class, 'index'])->name('index');
            Route::get('my-procedures', [PhysiotherapyProcedureController::class, 'myProcedures'])->middleware('permission:show-own-physiotherapy-procedures')->name('my-procedures');
            Route::get('by-appointment/{appointment}', [PhysiotherapyProcedureController::class, 'getByAppointment'])->name('by-appointment');
            Route::post('store', [PhysiotherapyProcedureController::class, 'store'])->name('store');

            // Review routes (must come before the general {physiotherapyProcedure} route)
            Route::get('{physiotherapyProcedure}/reviews', [PhysiotherapyProcedureController::class, 'getReviews'])->name('reviews');
            Route::get('{physiotherapyProcedure}/reviews/{review}', [PhysiotherapyProcedureController::class, 'showReview'])->name('show-review');
            Route::post('{physiotherapyProcedure}/reviews', [PhysiotherapyProcedureController::class, 'storeReview'])->name('store-review');
            Route::put('{physiotherapyProcedure}/reviews/{review}', [PhysiotherapyProcedureController::class, 'updateReview'])->name('update-review');
            Route::delete('{physiotherapyProcedure}/reviews/{review}', [PhysiotherapyProcedureController::class, 'destroyReview'])->name('destroy-review');

            // General physiotherapy procedure routes
            Route::get('{physiotherapyProcedure}', [PhysiotherapyProcedureController::class, 'show'])->name('show');
            Route::put('{physiotherapyProcedure}/update', [PhysiotherapyProcedureController::class, 'update'])->name('update');
            Route::delete('{physiotherapyProcedure}/destroy', [PhysiotherapyProcedureController::class, 'destroy'])->name('destroy');
            Route::post('update-counter/{physiotherapyProcedure}', [PhysiotherapyProcedureController::class, 'updateCounter'])->name('update-counter');
        });

    // Physiotherapy Types routes
    Route::prefix('physiotherapy-types')
        ->middleware('permission:show-physiotherapy-menu')
        ->name('physiotherapy-types.')->group(function () {
            Route::get('/', [PhysiotherapyTypeController::class, 'index'])->name('index');
            Route::get('create', [PhysiotherapyTypeController::class, 'create'])->name('create');
            Route::post('store', [PhysiotherapyTypeController::class, 'store'])->name('store');
            Route::get('{physiotherapyType}', [PhysiotherapyTypeController::class, 'show'])->name('show');
            Route::get('{physiotherapyType}/edit', [PhysiotherapyTypeController::class, 'edit'])->name('edit');
            Route::put('{physiotherapyType}/update', [PhysiotherapyTypeController::class, 'update'])->name('update');
            Route::delete('{physiotherapyType}/destroy', [PhysiotherapyTypeController::class, 'destroy'])->name('destroy');
            Route::post('{physiotherapyType}/toggle-status', [PhysiotherapyTypeController::class, 'toggleStatus'])->name('toggle-status');
        });
    // Nurses routes
    Route::prefix('nurses')->name('nurses.')->group(function () {
        Route::get('index', [NurseController::class, 'index'])->name('index');
        Route::get('create', [NurseController::class, 'create'])->name('create');
        Route::get('show/{nurse}', [NurseController::class, 'show'])->name('show');
        Route::post('store', [NurseController::class, 'store'])->name('store');
        Route::get('edit/{nurse}', [NurseController::class, 'edit'])->name('edit');
        Route::put('update/{nurse}', [NurseController::class, 'update'])->name('update');
        Route::delete('destroy/{nurse}', [NurseController::class, 'destroy'])->name('destroy');
    });

    // Diabetes Charts routes
    Route::prefix('diabetes-charts')->name('diabetes-charts.')->group(function () {
        Route::get('index', [DiabetesChartController::class, 'index'])->name('index');
        Route::get('create', [DiabetesChartController::class, 'create'])->name('create');
        Route::get('show/{diabetesChart}', [DiabetesChartController::class, 'show'])->name('show');
        Route::get('print', [DiabetesChartController::class, 'print'])->name('print');
        Route::post('store', [DiabetesChartController::class, 'store'])->name('store');
        Route::get('edit/{diabetesChart}', [DiabetesChartController::class, 'edit'])->name('edit');
        Route::put('update/{diabetesChart}', [DiabetesChartController::class, 'update'])->name('update');
        Route::delete('destroy/{diabetesChart}', [DiabetesChartController::class, 'destroy'])->name('destroy');
    });

    // API Select Routes for Select2 dropdowns (Web-based, requires auth)
    Route::middleware('auth')->prefix('api/select')->group(function () {
        Route::get('physiotherapy-types', [\App\Http\Controllers\Api\SelectController::class, 'getPhysiotherapyTypes'])->name('api.select.physiotherapy-types');
        Route::get('physiotherapists', [\App\Http\Controllers\Api\SelectController::class, 'getPhysiotherapists'])->name('api.select.physiotherapists');
        Route::get('users', [\App\Http\Controllers\Api\SelectController::class, 'users'])->name('api.select.users');
        Route::get('nurses', [NurseController::class, 'getNursesForSelect'])->name('api.select.nurses');
        Route::get('diabetes-charts', [DiabetesChartController::class, 'getDiabetesChartsForSelect'])->name('api.select.diabetes-charts');
        Route::get('branches', [BranchController::class, 'getBranchesForSelect'])->name('api.select.branches');
        Route::get('lab-types', [LabTypeController::class, 'getLabTypesForSelect'])->name('api.select.lab-types');
        Route::get('rooms', [\App\Http\Controllers\Api\SelectController::class, 'getRooms'])->name('api.select.rooms');
    });

    // Physiotherapy Reports routes
    Route::prefix('physiotherapy-reports')
        ->middleware('permission:show-physiotherapy-reports')
        ->name('physiotherapy-reports.')->group(function () {

            Route::get('index', [PhysiotherapyReportController::class, 'index'])->name('index');
            Route::post('generate', [PhysiotherapyReportController::class, 'generateReport'])->name('generate');
            Route::post('export', [PhysiotherapyReportController::class, 'exportReport'])->name('export');
        });

    // General routes
    Route::get('/notification/mark-as-read/{notification}', [NotificationController::class, 'markAsRead'])->name('notification.mark_as_read');
    Route::get('mark-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark_all_as_read');
    Route::get('/scan-qr-code', [PatientController::class, 'scanQrCode'])->name('scanQRCode');
    Route::get('/scan-qr-code-page', [PatientController::class, 'scanCode'])->name('scanCode');
    Route::get('/get_districts/{provinceId}', [HomeController::class, 'getRelatedDistricts']);
    Route::get('/get_departments/{branchId}', [HomeController::class, 'getRelatedDepartments']);
    Route::get('/get_related_beds/{roomId}', [HomeController::class, 'getRelatedBeds']);
    Route::get('/get_sections/{depId}', [HomeController::class, 'getRelatedSections']);
    Route::get('/scan-qr-code-prescription', [PrescriptionController::class, 'scanQrCode'])->name('prescriptions.scanQRCode');
    Route::get('/scan-qr-code-page-prescription', [PrescriptionController::class, 'scanCode'])->name('prescriptions.scanCode');
    Route::get('/get_doctors/{departmentId}', [HomeController::class, 'getRelatedDoctors']);
    Route::get('/get_branch_doctors/{branchId}', [HomeController::class, 'getBranchDoctors']);
    Route::get('/get_labTypes/{labTypeId}', [HomeController::class, 'getRelatedLabTypes']);
    Route::get('/lab-tests/{labTypeId}', [HomeController::class, 'getLabTypeTests']);

    // Lab Ajax routes
    Route::prefix('lab-ajax')->name('lab-ajax.')->group(function () {
        Route::get('lab-types/{sectionId}', [\App\Http\Controllers\Section\LabAjaxController::class, 'getLabTypesBySection']);
        Route::get('lab-type-tests/{labTypeId}', [\App\Http\Controllers\Section\LabAjaxController::class, 'getLabTypeTests']);
        Route::post('store/{type}/{id}', [\App\Http\Controllers\Section\LabAjaxController::class, 'storeLabTest']);
        Route::get('labs/{id}/{type}', [\App\Http\Controllers\Section\LabAjaxController::class, 'loadList']);
        Route::get('lab-items/{labId}', [\App\Http\Controllers\Section\LabAjaxController::class, 'getLabItems']);
        Route::put('update-status/{labId}', [\App\Http\Controllers\Section\LabAjaxController::class, 'updateLabStatus']);
        Route::delete('delete/{labId}', [\App\Http\Controllers\Section\LabAjaxController::class, 'deleteLabTest']);
    });

    // Lab Test Registration Ajax routes
    Route::prefix('lab-test-registration-ajax')->name('lab-test-registration-ajax.')->group(function () {
        Route::get('categories', [\App\Http\Controllers\Section\LabTestRegistrationAjaxController::class, 'getTestCategories']);
        Route::get('all-lab-types', [\App\Http\Controllers\Section\LabTestRegistrationAjaxController::class, 'getAllLabTypes']);
        Route::get('lab-types/{categoryId}', [\App\Http\Controllers\Section\LabTestRegistrationAjaxController::class, 'getLabTypesByCategory']);
        Route::get('lab-type-parameters/{labTypeId}', [\App\Http\Controllers\Section\LabTestRegistrationAjaxController::class, 'getLabTypeParameters']);
        Route::post('store/{type}/{id}', [\App\Http\Controllers\Section\LabTestRegistrationAjaxController::class, 'storeTestRegistration']);
        Route::get('registrations/{id}/{type}', [\App\Http\Controllers\Section\LabTestRegistrationAjaxController::class, 'loadList']);
        Route::get('registration-parameters/{registrationId}', [\App\Http\Controllers\Section\LabTestRegistrationAjaxController::class, 'getRegistrationParameters']);
    });

    // Prescription Ajax routes
    Route::prefix('prescription-ajax')->name('prescription-ajax.')->group(function () {
        Route::get('all-medicines', [\App\Http\Controllers\PrescriptionAjaxController::class, 'getAllMedicines']);
        Route::get('medicine-usage-types', [\App\Http\Controllers\PrescriptionAjaxController::class, 'getMedicineUsageTypes']);
        Route::post('store', [\App\Http\Controllers\PrescriptionAjaxController::class, 'storePrescription']);
        Route::get('appointment-prescriptions/{id}/{type?}', [\App\Http\Controllers\PrescriptionAjaxController::class, 'getAppointmentPrescriptions']);
        Route::get('prescription-items/{prescriptionId}', [\App\Http\Controllers\PrescriptionAjaxController::class, 'getPrescriptionItems']);
        Route::put('update-status/{prescriptionId}', [\App\Http\Controllers\PrescriptionAjaxController::class, 'updatePrescriptionStatus']);
        Route::post('update-item-status/{itemId}', [\App\Http\Controllers\PrescriptionAjaxController::class, 'updatePrescriptionItemStatus']);
        Route::delete('delete/{prescriptionId}', [\App\Http\Controllers\PrescriptionAjaxController::class, 'deletePrescription']);
        Route::delete('delete-item/{itemId}', [\App\Http\Controllers\PrescriptionAjaxController::class, 'deletePrescriptionItem']);
        Route::get('prescriptions-index', [\App\Http\Controllers\PrescriptionAjaxController::class, 'getPrescriptionsIndex']);
    });

    // Prescription Show API routes
    Route::prefix('prescription-show-ajax')->name('prescription-show-ajax.')->group(function () {
        Route::get('prescription-details/{id}', [\App\Http\Controllers\PrescriptionShowApiController::class, 'getPrescriptionDetails']);
        Route::put('update-prescription-status/{id}', [\App\Http\Controllers\PrescriptionShowApiController::class, 'updatePrescriptionStatus']);
        Route::put('update-item-status/{itemId}', [\App\Http\Controllers\PrescriptionShowApiController::class, 'updateItemStatus']);
        Route::put('update-item-amount/{itemId}', [\App\Http\Controllers\PrescriptionShowApiController::class, 'updateItemAmount']);
        Route::get('alternatives/{itemId}', [\App\Http\Controllers\PrescriptionShowApiController::class, 'getAlternatives']);
        Route::post('add-alternative', [\App\Http\Controllers\PrescriptionShowApiController::class, 'addAlternative']);
        Route::put('select-alternative/{alternativeId}', [\App\Http\Controllers\PrescriptionShowApiController::class, 'selectAlternative']);
        Route::put('update-alternative-status/{alternativeId}', [\App\Http\Controllers\PrescriptionShowApiController::class, 'updateAlternativeStatus']);
        Route::delete('delete-alternative/{alternativeId}', [\App\Http\Controllers\PrescriptionShowApiController::class, 'deleteAlternative']);
        Route::get('all-medicines', [\App\Http\Controllers\PrescriptionShowApiController::class, 'getAllMedicines']);
        Route::get('medicine-types', [\App\Http\Controllers\PrescriptionShowApiController::class, 'getMedicineTypes']);
        Route::get('medicine-usage-types', [\App\Http\Controllers\PrescriptionShowApiController::class, 'getMedicineUsageTypes']);
    });

    // Visit Ajax routes
    Route::prefix('visit-ajax')->name('visit-ajax.')->group(function () {
        Route::get('food-types', [\App\Http\Controllers\VisitAjaxController::class, 'getFoodTypes']);
        Route::post('store', [\App\Http\Controllers\VisitAjaxController::class, 'storeVisit']);
        Route::get('hospitalization-visits/{hospitalizationId}', [\App\Http\Controllers\VisitAjaxController::class, 'getHospitalizationVisits']);
        Route::get('visit-details/{visitId}', [\App\Http\Controllers\VisitAjaxController::class, 'getVisitDetails']);
        Route::put('update/{visitId}', [\App\Http\Controllers\VisitAjaxController::class, 'updateVisit']);
        Route::delete('delete/{visitId}', [\App\Http\Controllers\VisitAjaxController::class, 'deleteVisit']);
    });

    // Doctor API routes
    Route::prefix('doctor-api')->name('doctor-api.')->group(function () {
        Route::get('doctors', [\App\Http\Controllers\DoctorApiController::class, 'getDoctors'])->name('doctors');
        Route::get('hospital-doctors', [\App\Http\Controllers\DoctorApiController::class, 'getHospitalDoctors'])->name('hospital-doctors');
    });

    // Hospitalization Prescription Ajax routes
    Route::prefix('hospitalization-prescription-ajax')->name('hospitalization-prescription-ajax.')->group(function () {
        Route::get('medicine-types', [\App\Http\Controllers\HospitalizationPrescriptionAjaxController::class, 'getMedicineTypes']);
        Route::get('medicine-usage-types', [\App\Http\Controllers\HospitalizationPrescriptionAjaxController::class, 'getMedicineUsageTypes']);
        Route::get('all-medicines', [\App\Http\Controllers\HospitalizationPrescriptionAjaxController::class, 'getAllMedicines']);
        Route::get('hospitalization-prescriptions/{hospitalizationId}', [\App\Http\Controllers\HospitalizationPrescriptionAjaxController::class, 'getHospitalizationPrescriptions']);
        Route::get('prescription-items/{prescriptionId}', [\App\Http\Controllers\HospitalizationPrescriptionAjaxController::class, 'getPrescriptionItems']);
        Route::post('store', [\App\Http\Controllers\HospitalizationPrescriptionAjaxController::class, 'storePrescription']);
    });

    // Consultation Ajax routes
    Route::prefix('consultation-ajax')->name('consultation-ajax.')->group(function () {
        Route::get('branches', [\App\Http\Controllers\ConsultationAjaxController::class, 'branches']);
        Route::get('departments', [\App\Http\Controllers\ConsultationAjaxController::class, 'departments']);
        Route::get('appointment-consultations/{appointmentId}/{type?}', [\App\Http\Controllers\ConsultationAjaxController::class, 'appointmentConsultations']);
        Route::post('store', [\App\Http\Controllers\ConsultationAjaxController::class, 'store']);
        Route::put('update/{consultationId}', [\App\Http\Controllers\ConsultationAjaxController::class, 'update']);
        Route::delete('delete/{consultationId}', [\App\Http\Controllers\ConsultationAjaxController::class, 'delete']);
    });

    // Advice Ajax routes
    Route::prefix('advice-ajax')->name('advice-ajax.')->group(function () {
        Route::get('appointment-advices/{appointmentId}', [\App\Http\Controllers\AdviceAjaxController::class, 'getAppointmentAdvices']);
        Route::post('store', [\App\Http\Controllers\AdviceAjaxController::class, 'store']);
        Route::put('update/{advice}', [\App\Http\Controllers\AdviceAjaxController::class, 'update']);
        Route::delete('delete/{advice}', [\App\Http\Controllers\AdviceAjaxController::class, 'delete']);
    });

    // Nurse Notes routes
    Route::prefix('nurse-notes')->name('nurse-notes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NurseNoteController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\NurseNoteController::class, 'create'])->name('create');
        Route::post('store', [\App\Http\Controllers\NurseNoteController::class, 'store'])->name('store');
        Route::get('show/{nurseNote}', [\App\Http\Controllers\NurseNoteController::class, 'show'])->name('show');
        Route::get('edit/{nurseNote}', [\App\Http\Controllers\NurseNoteController::class, 'edit'])->name('edit');
        Route::put('update/{nurseNote}', [\App\Http\Controllers\NurseNoteController::class, 'update'])->name('update');
        Route::delete('destroy/{nurseNote}', [\App\Http\Controllers\NurseNoteController::class, 'destroy'])->name('destroy');
        Route::get('print', [\App\Http\Controllers\NurseNoteController::class, 'print'])->name('print');
        Route::get('section/{morphable_type}/{morphable_id}', [\App\Http\Controllers\NurseNoteController::class, 'section'])->name('section');
    });

    // Medication Administration Records routes
    Route::prefix('medication-administration-records')->name('medication-administration-records.')->group(function () {
        Route::get('/', [MedicationAdministrationRecordController::class, 'index'])->name('index');
        Route::get('create', [MedicationAdministrationRecordController::class, 'create'])->name('create');
        Route::post('store', [MedicationAdministrationRecordController::class, 'store'])->name('store');
        Route::get('show/{medicationAdministrationRecord}', [MedicationAdministrationRecordController::class, 'show'])->name('show');
        Route::get('edit/{medicationAdministrationRecord}', [MedicationAdministrationRecordController::class, 'edit'])->name('edit');
        Route::put('update/{medicationAdministrationRecord}', [MedicationAdministrationRecordController::class, 'update'])->name('update');
        Route::delete('destroy/{medicationAdministrationRecord}', [MedicationAdministrationRecordController::class, 'destroy'])->name('destroy');
        Route::get('print', [MedicationAdministrationRecordController::class, 'print'])->name('print');
        Route::post('{medicationAdministrationRecord}/add-time', [MedicationAdministrationRecordController::class, 'addAdministrationTime'])->name('add-time');
        Route::delete('administration-times/{administrationTime}', [MedicationAdministrationRecordController::class, 'removeAdministrationTime'])->name('remove-time');
    });

    // Vital Sign Types routes
    Route::prefix('vital-sign-types')->name('vital-sign-types.')->group(function () {
        Route::get('/', [\App\Http\Controllers\VitalSignTypeController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\VitalSignTypeController::class, 'create'])->name('create');
        Route::post('store', [\App\Http\Controllers\VitalSignTypeController::class, 'store'])->name('store');
        Route::get('show/{vitalSignType}', [\App\Http\Controllers\VitalSignTypeController::class, 'show'])->name('show');
        Route::get('edit/{vitalSignType}', [\App\Http\Controllers\VitalSignTypeController::class, 'edit'])->name('edit');
        Route::put('update/{vitalSignType}', [\App\Http\Controllers\VitalSignTypeController::class, 'update'])->name('update');
        Route::delete('destroy/{vitalSignType}', [\App\Http\Controllers\VitalSignTypeController::class, 'destroy'])->name('destroy');
    });

    // Vital Signs routes
    Route::prefix('vital-signs')->name('vital-signs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\VitalSignController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\VitalSignController::class, 'create'])->name('create');
        Route::post('store', [\App\Http\Controllers\VitalSignController::class, 'store'])->name('store');
        Route::get('show/{vitalSign}', [\App\Http\Controllers\VitalSignController::class, 'show'])->name('show');
        Route::get('edit/{vitalSign}', [\App\Http\Controllers\VitalSignController::class, 'edit'])->name('edit');
        Route::put('update/{vitalSign}', [\App\Http\Controllers\VitalSignController::class, 'update'])->name('update');
        Route::get('print/{morphable_type}/{morphable_id}', [\App\Http\Controllers\VitalSignController::class, 'print'])->name('print');
        Route::delete('destroy/{vitalSign}', [\App\Http\Controllers\VitalSignController::class, 'destroy'])->name('destroy');
    });

    // Vital Sign Schedules routes (for modal functionality)
    Route::prefix('vital-sign-schedules')->name('vital-sign-schedules.')->group(function () {
        Route::post('store', [\App\Http\Controllers\VitalSignScheduleController::class, 'store'])->name('store');
        Route::get('edit/{vitalSignSchedule}', [\App\Http\Controllers\VitalSignScheduleController::class, 'edit'])->name('edit');
        Route::put('update/{vitalSignSchedule}', [\App\Http\Controllers\VitalSignScheduleController::class, 'update'])->name('update');
        Route::delete('destroy/{vitalSignSchedule}', [\App\Http\Controllers\VitalSignScheduleController::class, 'destroy'])->name('destroy');
    });

    // Laboratory Test Management System routes
    Route::prefix('laboratory')->name('laboratory.')->group(function() {
        
        // Parameters
        Route::get('parameters', [LabTestParameterController::class, 'index'])->name('parameters.index');
        Route::post('parameters', [LabTestParameterController::class, 'store'])->name('parameters.store');
        Route::get('parameters/{id}/edit', [LabTestParameterController::class, 'edit'])->name('parameters.edit');
        Route::post('parameters/{id}', [LabTestParameterController::class, 'update'])->name('parameters.update');
        Route::get('tests-by-category/{category}', [LabTestParameterController::class, 'getTestsByCategory'])->name('tests.by-category');
        
        // Patient Test Registration
        Route::get('registrations', [PatientTestRegistrationController::class, 'getTestList'])->name('registrations.index');
        Route::get('registrations/report', [PatientTestRegistrationController::class, 'report'])->name('registrations.report');
        Route::get('registrations/report-detailed', [PatientTestRegistrationController::class, 'reportDetailed'])->name('registrations.report-detailed');
        Route::post('registrations/export-report', [PatientTestRegistrationController::class, 'exportReport'])->name('registrations.export-report');
        Route::post('registrations/export-report-detailed', [PatientTestRegistrationController::class, 'exportReportDetailed'])->name('registrations.export-report-detailed');
        
        // Status update routes
        Route::post('registrations/{id}/mark-in-progress', [PatientTestRegistrationController::class, 'markInProgress'])->name('registrations.mark-in-progress');
        Route::post('registrations/{id}/mark-completed', [PatientTestRegistrationController::class, 'markCompleted'])->name('registrations.mark-completed');
        Route::post('registrations/{id}/cancel', [PatientTestRegistrationController::class, 'cancel'])->name('registrations.cancel');
        
        // Test Results
        Route::get('results/patients', [TestResultController::class, 'patientList'])->name('results.patients');
        Route::get('results/pending', [TestResultController::class, 'patientList'])->name('results.pending');
        Route::get('results/in-progress', [TestResultController::class, 'patientList'])->name('results.in-progress');
        Route::get('results/completed', [TestResultController::class, 'patientList'])->name('results.completed');
        Route::get('results/registration/{registration_id}', [TestResultController::class, 'showTestResults'])->name('results.show');
        Route::post('results/update', [TestResultController::class, 'ajaxUpdateTestResults'])->name('results.update');
        Route::get('results/load/{test_registration_id}', [TestResultController::class, 'ajaxLoadTestResult'])->name('results.load');
        Route::post('results/{registration_id}/accept', [TestResultController::class, 'acceptTest'])->name('results.accept');
        Route::post('results/load-all-parameters', [TestResultController::class, 'loadAllParameters'])->name('results.load-all-parameters');
        Route::post('results/save-all-parameters', [TestResultController::class, 'saveAllParameters'])->name('results.save-all-parameters');
        Route::get('results/grouped', [TestResultController::class, 'groupedTests'])->name('results.grouped');
        Route::get('reports/print/{ref_no}', [TestResultController::class, 'printResultByRef'])->name('reports.print');
        Route::get('reports/print-group/{category_id}', [TestResultController::class, 'printGroupedTests'])->name('reports.print-group');
        // Attachment routes
        Route::post('results/{test_result_id}/attachments', [TestResultController::class, 'uploadAttachments'])->name('results.attachments.upload');
        Route::get('results/{test_result_id}/attachments', [TestResultController::class, 'getAttachments'])->name('results.attachments.get');
        Route::delete('results/attachments/{attachment_id}', [TestResultController::class, 'deleteAttachment'])->name('results.attachments.delete');
        
        // Scan routes
        Route::get('scan', [TestResultController::class, 'scanCode'])->name('scan');
        Route::get('scan/ref', [TestResultController::class, 'scanRefCode'])->name('scan.ref');
    });

    // Dentist Department routes
    Route::prefix('dentist-registrations')
        ->middleware(['permission:access-dentist-registrations', 'dentist'])
        ->name('dentist-registrations.')->group(function () {
        Route::get('index', [\App\Http\Controllers\DentistRegistrationController::class, 'index'])->name('index');
        Route::get('create/{appointment}', [\App\Http\Controllers\DentistRegistrationController::class, 'create'])->name('create');
        Route::post('store/{appointment}', [\App\Http\Controllers\DentistRegistrationController::class, 'store'])->name('store');
        Route::get('show/{dentistRegistration}', [\App\Http\Controllers\DentistRegistrationController::class, 'show'])->name('show');
        Route::get('edit/{dentistRegistration}', [\App\Http\Controllers\DentistRegistrationController::class, 'edit'])->name('edit');
        Route::put('update/{dentistRegistration}', [\App\Http\Controllers\DentistRegistrationController::class, 'update'])->name('update');
        Route::delete('destroy/{dentistRegistration}', [\App\Http\Controllers\DentistRegistrationController::class, 'destroy'])->name('destroy');
        Route::post('mark-completed/{dentistRegistration}', [\App\Http\Controllers\DentistRegistrationController::class, 'markCompleted'])->name('mark-completed');
        Route::post('mark-in-progress/{dentistRegistration}', [\App\Http\Controllers\DentistRegistrationController::class, 'markInProgress'])->name('mark-in-progress');
        Route::post('cancel/{dentistRegistration}', [\App\Http\Controllers\DentistRegistrationController::class, 'cancel'])->name('cancel');
    });

    // Dental Examinations routes
    Route::prefix('dental-examinations')->name('dental-examinations.')->group(function () {
        Route::post('store/{dentistRegistration}', [\App\Http\Controllers\DentalExaminationController::class, 'store'])->name('store');
        Route::put('update/{dentalExamination}', [\App\Http\Controllers\DentalExaminationController::class, 'update'])->name('update');
        Route::delete('destroy/{dentalExamination}', [\App\Http\Controllers\DentalExaminationController::class, 'destroy'])->name('destroy');
    });

    // Dental Treatments routes
    Route::prefix('dental-treatments')->name('dental-treatments.')->group(function () {
        Route::post('store/{dentistRegistration}', [\App\Http\Controllers\DentalTreatmentController::class, 'store'])->name('store');
        Route::put('update/{dentalTreatment}', [\App\Http\Controllers\DentalTreatmentController::class, 'update'])->name('update');
        Route::delete('destroy/{dentalTreatment}', [\App\Http\Controllers\DentalTreatmentController::class, 'destroy'])->name('destroy');
    });

    // Dental X-rays routes
    Route::prefix('dental-xrays')->name('dental-xrays.')->group(function () {
        Route::post('store/{dentistRegistration}', [\App\Http\Controllers\DentalXrayController::class, 'store'])->name('store');
        Route::put('update/{dentalXray}', [\App\Http\Controllers\DentalXrayController::class, 'update'])->name('update');
        Route::delete('destroy/{dentalXray}', [\App\Http\Controllers\DentalXrayController::class, 'destroy'])->name('destroy');
    });

    // Dental Notes routes
    Route::prefix('dental-notes')->name('dental-notes.')->group(function () {
        Route::post('store/{dentistRegistration}', [\App\Http\Controllers\DentalNoteController::class, 'store'])->name('store');
        Route::put('update/{dentalNote}', [\App\Http\Controllers\DentalNoteController::class, 'update'])->name('update');
        Route::delete('destroy/{dentalNote}', [\App\Http\Controllers\DentalNoteController::class, 'destroy'])->name('destroy');
    });

    // Dentist AJAX routes
    Route::prefix('dentist-ajax')->name('dentist-ajax.')->group(function () {
        Route::get('registrations/{appointmentId}', [\App\Http\Controllers\DentistAjaxController::class, 'getRegistrations'])->name('registrations');
        Route::get('examinations/{dentistRegistration}', [\App\Http\Controllers\DentistAjaxController::class, 'getExaminations'])->name('examinations');
        Route::get('treatments/{dentistRegistration}', [\App\Http\Controllers\DentistAjaxController::class, 'getTreatments'])->name('treatments');
        Route::get('treatments/for-chart/{dentistRegistration}', [\App\Http\Controllers\DentistAjaxController::class, 'getTreatmentsForChart'])->name('treatments.for-chart');
        Route::get('xrays/{dentistRegistration}', [\App\Http\Controllers\DentistAjaxController::class, 'getXrays'])->name('xrays');
        Route::get('notes/{dentistRegistration}', [\App\Http\Controllers\DentistAjaxController::class, 'getNotes'])->name('notes');
        Route::post('examinations/{dentistRegistration}', [\App\Http\Controllers\DentistAjaxController::class, 'storeExamination'])->name('store-examination');
        Route::post('treatments/store/{dentistRegistration}', [\App\Http\Controllers\DentistAjaxController::class, 'storeTreatment'])->name('treatments.store');
        Route::post('treatments/link/{treatment}/{dentalChart}', [\App\Http\Controllers\DentistAjaxController::class, 'linkTreatmentToChart'])->name('treatments.link');
    });

    // Dental Charts routes
    Route::prefix('dental-charts')->name('dental-charts.')->group(function () {
        Route::get('index/{dentistRegistration}', [\App\Http\Controllers\DentalChartController::class, 'index'])->name('index');
        Route::get('create/{dentistRegistration}', [\App\Http\Controllers\DentalChartController::class, 'create'])->name('create');
        Route::post('store/{dentistRegistration}', [\App\Http\Controllers\DentalChartController::class, 'store'])->name('store');
        // Redirect show to dentist-registrations.show with tab parameter
        Route::get('show/{dentistRegistration}', function(\App\Models\DentistRegistration $dentistRegistration) {
            return redirect()->route('dentist-registrations.show', $dentistRegistration) . '?tab=dental-chart';
        })->name('show');
        Route::get('edit/{dentalChart}', [\App\Http\Controllers\DentalChartController::class, 'edit'])->name('edit');
        Route::put('update/{dentalChart}', [\App\Http\Controllers\DentalChartController::class, 'update'])->name('update');
        Route::delete('destroy/{dentalChart}', [\App\Http\Controllers\DentalChartController::class, 'destroy'])->name('destroy');
        Route::get('history/{dentistRegistration}', [\App\Http\Controllers\DentalChartController::class, 'history'])->name('history');
        Route::get('compare/{dentistRegistration}', [\App\Http\Controllers\DentalChartController::class, 'compare'])->name('compare');
        Route::get('export/{dentistRegistration}', [\App\Http\Controllers\DentalChartController::class, 'exportPdf'])->name('export');
        Route::get('print/{dentistRegistration}', [\App\Http\Controllers\DentalChartController::class, 'printView'])->name('print');
    });

    // Dental Chart AJAX routes
    Route::prefix('dental-chart-ajax')->name('dental-chart-ajax.')->group(function () {
        Route::get('charts/{dentistRegistration}', [\App\Http\Controllers\DentalChartAjaxController::class, 'getCharts'])->name('charts');
        Route::get('tooth-chart/{dentistRegistration}/{toothNumber}', [\App\Http\Controllers\DentalChartAjaxController::class, 'getToothChart'])->name('tooth-chart');
        Route::post('store/{dentistRegistration}', [\App\Http\Controllers\DentalChartAjaxController::class, 'storeChart'])->name('store');
        Route::put('update/{dentalChart}', [\App\Http\Controllers\DentalChartAjaxController::class, 'updateChart'])->name('update');
        Route::post('measurements/{dentalChart}', [\App\Http\Controllers\DentalChartAjaxController::class, 'storeMeasurement'])->name('store-measurement');
    });

    // Dental Chart Images routes
    Route::prefix('dental-chart-images')->name('dental-chart-images.')->group(function () {
        Route::post('store/{dentalChart}', [\App\Http\Controllers\DentalChartImageController::class, 'store'])->name('store');
        Route::get('show/{dentalChartImage}', [\App\Http\Controllers\DentalChartImageController::class, 'show'])->name('show');
        Route::delete('destroy/{dentalChartImage}', [\App\Http\Controllers\DentalChartImageController::class, 'destroy'])->name('destroy');
    });

    // Dental Periodontal routes
    Route::prefix('dental-periodontal')->name('dental-periodontal.')->group(function () {
        Route::post('store/{dentalChart}', [\App\Http\Controllers\DentalPeriodontalController::class, 'store'])->name('store');
        Route::put('update/{measurement}', [\App\Http\Controllers\DentalPeriodontalController::class, 'update'])->name('update');
        Route::get('measurements/{dentalChart}', [\App\Http\Controllers\DentalPeriodontalController::class, 'getMeasurements'])->name('measurements');
    });

});

// Routes outside the main auth group
Route::prefix('militery_types')->name('militery_types.')->group(function () {
    Route::get('/', [MiliteryTypeController::class, 'index'])->name('index');
    Route::get('create', [MiliteryTypeController::class, 'create'])->name('create');
    Route::get('show/{militeryType}', [MiliteryTypeController::class, 'show'])->name('show');
    Route::post('store', [MiliteryTypeController::class, 'store'])->name('store');
    Route::get('edit/{militeryType}', [MiliteryTypeController::class, 'edit'])->name('edit');
    Route::put('update/{militeryType}', [MiliteryTypeController::class, 'update'])->name('update');
    Route::delete('destroy/{militeryType}', [MiliteryTypeController::class, 'destroy'])->name('destroy');
});

// Pharmacies routes
Route::prefix('pharmacies')->middleware(['auth', 'pharmacy_role:manager'])->name('pharmacies.')->group(function () {
    Route::get('index', [PharmacyController::class, 'index'])->name('index');
    Route::get('create', [PharmacyController::class, 'create'])->name('create');
    Route::post('store', [PharmacyController::class, 'store'])->name('store');
    Route::get('show/{pharmacy}', [PharmacyController::class, 'show'])->name('show');
    Route::get('edit/{pharmacy}', [PharmacyController::class, 'edit'])->name('edit');
    Route::put('update/{pharmacy}', [PharmacyController::class, 'update'])->name('update');
    Route::delete('destroy/{pharmacy}', [PharmacyController::class, 'destroy'])->name('destroy');
    
    // User management routes
    Route::get('manage-users/{pharmacy}', [PharmacyController::class, 'manageUsers'])->name('manage-users');
    Route::post('add-user/{pharmacy}', [PharmacyController::class, 'addUser'])->name('add-user');
    Route::post('remove-user/{pharmacy}', [PharmacyController::class, 'removeUser'])->name('remove-user');
    Route::post('update-user-role/{pharmacy}', [PharmacyController::class, 'updateUserRole'])->name('update-user-role');
});

// Backup routes
Route::prefix('backups')->name('backups.')->group(function () {
    Route::get('/', [BackupController::class, 'index'])->name('index');
    Route::get('show/{backupName}', [BackupController::class, 'show'])->name('show');
    Route::get('download/{backupName}', [BackupController::class, 'download'])->name('download');
    Route::get('create', [BackupController::class, 'create'])->name('create');
    Route::delete('destroy/{backupName}', [BackupController::class, 'destroy'])->name('destroy');
    Route::post('clean', [BackupController::class, 'clean'])->name('clean');
    Route::get('stats', [BackupController::class, 'stats'])->name('stats');
    Route::get('test', [BackupController::class, 'test'])->name('test');
});

// Nutrition Care Routes

Route::get('nutrition-cares/{nutritionCare}/print', [NutritionCareController::class, 'print'])
    ->name('nutrition-cares.print');
Route::resource('nutrition-cares', NutritionCareController::class);
Route::get('nutrition-cares/{morphable_type}/{morphable_id}', [NutritionCareController::class, 'index'])
    ->name('nutrition-cares.by-morphable');

// Nursing Assessment Routes
Route::get('nursing-assessments/{nursingAssessment}/print', [NursingAssessmentController::class, 'print'])
    ->name('nursing-assessments.print');
// Section
Route::get('nursing-assessments/section/{morphable_type}/{morphable_id}', [NursingAssessmentController::class, 'section'])
    ->name('nursing-assessments.section');


Route::resource('nursing-assessments', NursingAssessmentController::class);
Route::get('nursing-assessments/{morphable_type}/{morphable_id}', [NursingAssessmentController::class, 'index'])
    ->name('nursing-assessments.by-morphable');

// Categories Routes
Route::get('categories', CategoryPageController::class)->middleware('auth')->name('categories.index');

// Categories API Routes
Route::middleware('auth')->prefix('api/categories')->name('api.categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

// Register route should be disabled be default.
Auth::routes(['register' => false]);
Route::group(['prefix' => 'react'], function () {
    include __DIR__ . '/react.php';
});
// Depots routes
Route::prefix('depots')->name('depots.')->group(function () {
    Route::get('/', [DepotController::class, 'index'])->name('index');
    Route::get('/create', [DepotController::class, 'create'])->name('create');
    Route::post('/store', [DepotController::class, 'store'])->name('store');
    Route::get('/show/{depot}', [DepotController::class, 'show'])->name('show');
    Route::get('/edit/{depot}', [DepotController::class, 'edit'])->name('edit');
    Route::put('/update/{depot}', [DepotController::class, 'update'])->name('update');
    Route::delete('/destroy/{depot}', [DepotController::class, 'destroy'])->name('destroy');
});