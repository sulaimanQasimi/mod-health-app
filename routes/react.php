<?php

use App\Http\Controllers\V1\DashboardController;
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');