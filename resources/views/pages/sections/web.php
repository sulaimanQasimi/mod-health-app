<?php

use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SectionController;

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


Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home_filter/{departmentFilter?}', [HomeController::class, 'index'])->name('home_filter');
    Route::get('/log-viewer', function() {
        return route('log-viewer');
    })->name('log-viewer');
    Route::get('change_language/{lang?}', [HomeController::class, 'changeLanguage'])->name('change_language');

    // recipient routes

    Route::prefix('recipients')->name('recipients.')->group(function () {
        Route::get('index', [RecipientController::class, 'index'])->name('index');
        Route::get('create', [RecipientController::class, 'create'])->name('create');
        Route::post('store', [RecipientController::class, 'store'])->name('store');
        Route::get('edit/{recipient}', [RecipientController::class, 'edit'])->name('edit');
        Route::put('update/{recipient}', [RecipientController::class, 'update'])->name('update');
        Route::get('show/{recipient}', [RecipientController::class, 'show'])->name('show');
        Route::get('destroy/{recipient}', [RecipientController::class, 'destroy'])->name('destroy');
        Route::get('get-search/{name?}', [RecipientController::class, 'recipientSearch'])->name('get-search');
        Route::post('get-search/{name?}', [RecipientController::class, 'recipientSearch'])->name('get-search-post');
        Route::post('get-recipients', [RecipientController::class, 'getRecipients'])->name('get-recipients');

    });

    // User routes

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

    // roles and permissions

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('', [RoleController::class, 'index'])->name('index');
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::get('show/{role}', [RoleController::class, 'show'])->name('show');
        Route::post('store', [RoleController::class, 'store'])->name('store');
        Route::get('edit/{role}', [RoleController::class, 'edit'])->name('edit');
        Route::put('update/{role}', [RoleController::class, 'update'])->name('update');
        Route::get('destroy/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    // Permission Resources
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('', [PermissionController::class, 'index'])->name('index');
        Route::get('create', [PermissionController::class, 'create'])->name('create');
        Route::get('show/{permission}', [PermissionController::class, 'show'])->name('show');
        Route::post('store', [PermissionController::class, 'store'])->name('store');
        Route::get('edit/{permission}', [PermissionController::class, 'edit'])->name('edit');
        Route::put('update/{permission}', [PermissionController::class, 'update'])->name('update');
        Route::get('destroy/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('index', [PatientController::class, 'index'])->name('index');
        Route::get('create', [PatientController::class, 'create'])->name('create');
        Route::get('show/{patient}', [PatientController::class, 'show'])->name('show');
        Route::post('store', [PatientController::class, 'store'])->name('store');
        Route::get('edit/{patient}', [PatientController::class, 'edit'])->name('edit');
        Route::put('update/{patient}', [PatientController::class, 'update'])->name('update');
        Route::get('destroy/{patient}', [PatientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('index', [DepartmentController::class, 'index'])->name('index');
        Route::get('create', [DepartmentController::class, 'create'])->name('create');
        Route::get('show/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::post('store', [DepartmentController::class, 'store'])->name('store');
        Route::get('edit/{department}', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('update/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::get('destroy/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('sections')->name('sections.')->group(function () {
        Route::get('index', [SectionController::class, 'index'])->name('index');
        Route::get('create', [SectionController::class, 'create'])->name('create');
        Route::get('show/{section}', [SectionController::class, 'show'])->name('show');
        Route::post('store', [SectionController::class, 'store'])->name('store');
        Route::get('edit/{section}', [SectionController::class, 'edit'])->name('edit');
        Route::put('update/{section}', [SectionController::class, 'update'])->name('update');
        Route::get('destroy/{section}', [SectionController::class, 'destroy'])->name('destroy');
    });

    Route::get('/notification/mark-as-read/{notification}', [NotificationController::class, 'markAsRead'])->name('notification.mark_as_read');
    Route::get('mark-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark_all_as_read');

});

Auth::routes();
