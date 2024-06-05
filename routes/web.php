<?php

use App\Http\Controllers\AnesthesiaController;
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
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ConsultationCommentController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DailyIcuProgressController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DiagnoseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\FoodTypeController;
use App\Http\Controllers\ICUController;
use App\Http\Controllers\LabItemController;
use App\Http\Controllers\LabTypeSectionController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineTypeController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\PatientComplaintController;
use App\Http\Controllers\PrescriptionItemController;
use App\Http\Controllers\RelationController;
use App\Http\Controllers\UnderReviewController;
use App\Http\Controllers\VisitController;
use App\Models\Prescription;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Auth Middleware
|--------------------------------------------------------------------------
|
| By default all routes are protected with auth middleware, which means users
| have to login before using any route.
|
*/
Route::group(['middleware' => ['auth']], function () {

    // Home default route
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Log viewer route
    Route::get('/log-viewer', function() {
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
        Route::get('history/{patient}', [PatientController::class, 'history'])->name('history');
        Route::get('create', [PatientController::class, 'create'])->name('create');
        Route::get('show/{patient}', [PatientController::class, 'show'])->name('show');
        Route::post('store', [PatientController::class, 'store'])->name('store');
        Route::get('edit/{patient}', [PatientController::class, 'edit'])->name('edit');
        Route::put('update/{patient}', [PatientController::class, 'update'])->name('update');
        Route::get('/print-card/{patient}', [PatientController::class, 'printCard'])->name('print-card');
        Route::get('destroy/{patient}', [PatientController::class, 'destroy'])->name('destroy');
        Route::post('/patients/{id}/add-image', [PatientController::class, 'addImage'])->name('addImage');
        Route::get('webcam/{patient}', [PatientController::class, 'webcam'])->name('webcam');
        Route::post('capture/{id}', [PatientController::class, 'addImage'])->name('capture');
    });

    // Departments routes
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('index', [DepartmentController::class, 'index'])->name('index');
        Route::get('create', [DepartmentController::class, 'create'])->name('create');
        Route::get('show/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::post('store', [DepartmentController::class, 'store'])->name('store');
        Route::get('edit/{department}', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('update/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::get('destroy/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });

    //Sections routes
    Route::prefix('sections')->name('sections.')->group(function () {
        Route::get('index', [SectionController::class, 'index'])->name('index');
        Route::get('create', [SectionController::class, 'create'])->name('create');
        Route::get('show/{section}', [SectionController::class, 'show'])->name('show');
        Route::post('store', [SectionController::class, 'store'])->name('store');
        Route::get('edit/{section}', [SectionController::class, 'edit'])->name('edit');
        Route::put('update/{section}', [SectionController::class, 'update'])->name('update');
        Route::get('destroy/{section}', [SectionController::class, 'destroy'])->name('destroy');
    });

    // Rooms routes
    Route::prefix('floors')->name('floors.')->group(function () {
        Route::get('index', [FloorController::class, 'index'])->name('index');
        Route::get('create', [FloorController::class, 'create'])->name('create');
        Route::get('show/{floor}', [FloorController::class, 'show'])->name('show');
        Route::post('store', [FloorController::class, 'store'])->name('store');
        Route::get('edit/{floor}', [FloorController::class, 'edit'])->name('edit');
        Route::put('update/{floor}', [FloorController::class, 'update'])->name('update');
        Route::get('destroy/{floor}', [FloorController::class, 'destroy'])->name('destroy');
    });

    // Rooms routes
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('index', [RoomController::class, 'index'])->name('index');
        Route::get('create', [RoomController::class, 'create'])->name('create');
        Route::get('show/{room}', [RoomController::class, 'show'])->name('show');
        Route::post('store', [RoomController::class, 'store'])->name('store');
        Route::get('edit/{room}', [RoomController::class, 'edit'])->name('edit');
        Route::put('update/{room}', [RoomController::class, 'update'])->name('update');
        Route::get('destroy/{room}', [RoomController::class, 'destroy'])->name('destroy');
    });

    // Beds routes
    Route::prefix('beds')->name('beds.')->group(function () {
        Route::get('index', [BedController::class, 'index'])->name('index');
        Route::get('create', [BedController::class, 'create'])->name('create');
        Route::get('show/{bed}', [BedController::class, 'show'])->name('show');
        Route::post('store', [BedController::class, 'store'])->name('store');
        Route::get('edit/{bed}', [BedController::class, 'edit'])->name('edit');
        Route::put('update/{bed}', [BedController::class, 'update'])->name('update');
        Route::get('destroy/{bed}', [BedController::class, 'destroy'])->name('destroy');
    });

    // Hospitalizations routes
    Route::prefix('hospitalizations')->name('hospitalizations.')->group(function () {
        Route::get('index', [HospitalizationController::class, 'index'])->name('index');
        Route::get('create', [HospitalizationController::class, 'create'])->name('create');
        Route::get('show/{hospitalization}', [HospitalizationController::class, 'show'])->name('show');
        Route::post('store', [HospitalizationController::class, 'store'])->name('store');
        Route::get('edit/{hospitalization}', [HospitalizationController::class, 'edit'])->name('edit');
        Route::put('update/{hospitalization}', [HospitalizationController::class, 'update'])->name('update');
        Route::get('destroy/{hospitalization}', [HospitalizationController::class, 'destroy'])->name('destroy');
    });

    // Under Review routes
    Route::prefix('under_reviews')->name('under_reviews.')->group(function () {
        Route::get('index', [UnderReviewController::class, 'index'])->name('index');
        Route::get('create', [UnderReviewController::class, 'create'])->name('create');
        Route::get('show/{underReview}', [UnderReviewController::class, 'show'])->name('show');
        Route::post('store', [UnderReviewController::class, 'store'])->name('store');
        Route::get('edit/{underReview}', [UnderReviewController::class, 'edit'])->name('edit');
        Route::put('update/{underReview}', [UnderReviewController::class, 'update'])->name('update');
        Route::get('destroy/{underReview}', [UnderReviewController::class, 'destroy'])->name('destroy');
    });

    // Visits routes
    Route::prefix('visits')->name('visits.')->group(function () {
        Route::get('index', [VisitController::class, 'index'])->name('index');
        Route::get('create', [VisitController::class, 'create'])->name('create');
        Route::get('show/{visit}', [VisitController::class, 'show'])->name('show');
        Route::post('store', [VisitController::class, 'store'])->name('store');
        Route::get('edit/{visit}', [VisitController::class, 'edit'])->name('edit');
        Route::put('update/{visit}', [VisitController::class, 'update'])->name('update');
        Route::get('destroy/{visit}', [VisitController::class, 'destroy'])->name('destroy');
    });

    // Doctors routes
    Route::prefix('doctors')->name('doctors.')->group(function () {
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
        Route::get('create', [AppointmentController::class, 'create'])->name('create');
        Route::get('show/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::post('store', [AppointmentController::class, 'store'])->name('store');
        Route::get('edit/{appointment}', [AppointmentController::class, 'edit'])->name('edit');
        Route::put('update/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::put('changeStatus/{appointment}', [AppointmentController::class, 'changeStatus'])->name('changeStatus');
        Route::get('destroy/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
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

    // Prescriptions routes
    Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
        Route::get('index', [PrescriptionController::class, 'index'])->name('index');
        Route::get('delivered', [PrescriptionController::class, 'delivered'])->name('delivered');
        Route::get('create', [PrescriptionController::class, 'create'])->name('create');
        Route::get('show/{prescription}', [PrescriptionController::class, 'show'])->name('show');
        Route::post('store', [PrescriptionController::class, 'store'])->name('store');
        Route::get('edit/{prescription}', [PrescriptionController::class, 'edit'])->name('edit');
        Route::put('update/{prescription}', [PrescriptionController::class, 'update'])->name('update');
        Route::get('destroy/{prescription}', [PrescriptionController::class, 'destroy'])->name('destroy');
        Route::get('/print-card/{appointment}', [PrescriptionController::class, 'printCard'])->name('print-card');
        Route::get('/issue/{prescription}', [PrescriptionController::class, 'issue'])->name('issue');
        Route::get('/reject/{prescription}', [PrescriptionController::class, 'reject'])->name('reject');
        Route::post('/update-status/{prescriptionId}/{key}', [PrescriptionController::class, 'updateStatus']);
        Route::put('changeStatus/{prescription}', [PrescriptionController::class, 'changeStatus'])->name('changeStatus');

    });

    // Labratory routes
    Route::prefix('lab_tests')->name('lab_tests.')->group(function () {
        Route::get('index', [LabController::class, 'index'])->name('index');
        Route::get('create', [LabController::class, 'create'])->name('create');
        Route::get('show/{lab}', [LabController::class, 'show'])->name('show');
        Route::post('store', [LabController::class, 'store'])->name('store');
        Route::get('edit/{lab}', [LabController::class, 'edit'])->name('edit');
        Route::put('update/{lab}', [LabController::class, 'update'])->name('update');
        Route::get('destroy/{lab}', [LabController::class, 'destroy'])->name('destroy');
        Route::get('/print-card/{lab}', [LabController::class, 'printCard'])->name('print-card');
    });

    // Relations routes
    Route::prefix('relations')->name('relations.')->group(function () {
        Route::get('index', [RelationController::class, 'index'])->name('index');
        Route::get('create', [RelationController::class, 'create'])->name('create');
        Route::get('show/{relation}', [RelationController::class, 'show'])->name('show');
        Route::post('store', [RelationController::class, 'store'])->name('store');
        Route::get('edit/{relation}', [RelationController::class, 'edit'])->name('edit');
        Route::put('update/{relation}', [RelationController::class, 'update'])->name('update');
        Route::get('destroy/{relation}', [RelationController::class, 'destroy'])->name('destroy');
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

    // Laboratory test type sections routes
    Route::prefix('lab_type_sections')->name('lab_type_sections.')->group(function () {
        Route::get('index', [LabTypeSectionController::class, 'index'])->name('index');
        Route::get('create', [LabTypeSectionController::class, 'create'])->name('create');
        Route::get('show/{labTypeSection}', [LabTypeSectionController::class, 'show'])->name('show');
        Route::post('store', [LabTypeSectionController::class, 'store'])->name('store');
        Route::get('edit/{labTypeSection}', [LabTypeSectionController::class, 'edit'])->name('edit');
        Route::put('update/{labTypeSection}', [LabTypeSectionController::class, 'update'])->name('update');
        Route::get('destroy/{labTypeSection}', [LabTypeSectionController::class, 'destroy'])->name('destroy');
    });

    // Laboratory test types routes
    Route::prefix('lab_types')->name('lab_types.')->group(function () {
        Route::get('index', [LabTypeController::class, 'index'])->name('index');
        Route::get('create', [LabTypeController::class, 'create'])->name('create');
        Route::get('show/{labType}', [LabTypeController::class, 'show'])->name('show');
        Route::post('store', [LabTypeController::class, 'store'])->name('store');
        Route::get('edit/{labType}', [LabTypeController::class, 'edit'])->name('edit');
        Route::put('update/{labType}', [LabTypeController::class, 'update'])->name('update');
        Route::get('destroy/{labType}', [LabTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('lab_items')->name('lab_items.')->group(function () {
        Route::get('getItems/{id}', [LabItemController::class, 'getItems'])->name('getItems');

    });

    Route::prefix('prescription_items')->name('prescription_items.')->group(function () {
        Route::get('getItems/{id}', [PrescriptionItemController::class, 'getItems'])->name('getItems');

    });

    // Branches routes
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('index', [BranchController::class, 'index'])->name('index');
        Route::get('create', [BranchController::class, 'create'])->name('create');
        Route::get('show/{branch}', [BranchController::class, 'show'])->name('show');
        Route::post('store', [BranchController::class, 'store'])->name('store');
        // Route::get('edit/{prescription}', [BranchController::class, 'edit'])->name('edit');
        // Route::put('update/{prescription}', [BranchController::class, 'update'])->name('update');
        // Route::get('destroy/{prescription}', [BranchController::class, 'destroy'])->name('destroy');
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
        Route::get('destroy/{operationType}', [OperationTypeController::class, 'destroy'])->name('destroy');
    });

    // Operations routes
    Route::prefix('operations')->name('operations.')->group(function () {
        Route::get('new', [OperationController::class, 'new'])->name('new');
        Route::get('completed', [OperationController::class, 'completed'])->name('completed');
        Route::get('create', [OperationController::class, 'create'])->name('create');
        Route::get('show/{operation}', [OperationController::class, 'show'])->name('show');
        Route::post('store', [OperationController::class, 'store'])->name('store');
        Route::get('edit/{operation}', [OperationController::class, 'edit'])->name('edit');
        Route::put('update/{operation}', [OperationController::class, 'update'])->name('update');
        Route::get('destroy/{operation}', [OperationController::class, 'destroy'])->name('destroy');
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
        Route::get('destroy/{icu}', [ICUController::class, 'destroy'])->name('destroy');
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
        Route::get('destroy/{anesthesia}', [AnesthesiaController::class, 'destroy'])->name('destroy');
    });

    // Medicine types routes
    Route::prefix('medicine_types')->name('medicine_types.')->group(function () {
        Route::get('index', [MedicineTypeController::class, 'index'])->name('index');
        Route::get('create', [MedicineTypeController::class, 'create'])->name('create');
        Route::get('show/{medicineType}', [MedicineTypeController::class, 'show'])->name('show');
        Route::post('store', [MedicineTypeController::class, 'store'])->name('store');
        Route::get('edit/{medicineType}', [MedicineTypeController::class, 'edit'])->name('edit');
        Route::put('update/{medicineType}', [MedicineTypeController::class, 'update'])->name('update');
        Route::get('destroy/{medicineType}', [MedicineTypeController::class, 'destroy'])->name('destroy');
    });

    // Medicines routes
    Route::prefix('medicines')->name('medicines.')->group(function () {
        Route::get('index', [MedicineController::class, 'index'])->name('index');
        Route::get('create', [MedicineController::class, 'create'])->name('create');
        Route::get('show/{medicine}', [MedicineController::class, 'show'])->name('show');
        Route::post('store', [MedicineController::class, 'store'])->name('store');
        Route::get('edit/{medicine}', [MedicineController::class, 'edit'])->name('edit');
        Route::put('update/{medicine}', [MedicineController::class, 'update'])->name('update');
        Route::get('destroy/{medicine}', [MedicineController::class, 'destroy'])->name('destroy');
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
        Route::get('destroy/{foodType}', [FoodTypeController::class, 'destroy'])->name('destroy');
    });

    // Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('index', [ReportController::class, 'index'])->name('index');

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

});

// Register route should be disabled be default.
Auth::routes(['register' => false]);
