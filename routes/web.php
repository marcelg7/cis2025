<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ActivityTypeController;
use App\Http\Controllers\CommitmentPeriodController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


// Protected Routes (Require Authentication)
Route::middleware(['auth'])->group(function () {
    // Customer Routes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers/fetch', [CustomerController::class, 'fetch'])->name('customers.fetch');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/add-mobility', [CustomerController::class, 'addMobilityForm'])->name('customers.add-mobility');
    Route::post('/customers/{customer}/add-mobility', [CustomerController::class, 'storeMobility'])->name('customers.store-mobility');
    Route::get('/customers/{customer}/add-subscriber', [CustomerController::class, 'addSubscriberForm'])->name('customers.add-subscriber');
    Route::post('/customers/{customer}/add-subscriber', [CustomerController::class, 'storeSubscriber'])->name('customers.store-subscriber');
	Route::get('/change-password', function () {
        return view('auth.change-password');
    })->name('password.change');
	Route::get('/settings', [UserSettingsController::class, 'edit'])->name('users.settings.edit');
    Route::patch('/settings', [UserSettingsController::class, 'update'])->name('users.settings.update');
    
	// Search Route
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Contract Routes
    Route::get('/contracts/{subscriber}/create', [ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts/{subscriber}', [ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
    Route::get('/contracts/{contract}/view', [ContractController::class, 'view'])->name('contracts.view');
    Route::post('/contracts/{contract}/email', [ContractController::class, 'email'])->name('contracts.email');
	Route::post('/contracts/{contract}/ftp', [ContractController::class, 'ftp'])->name('contracts.ftp');

	// Admin-Only Routes
	Route::middleware([\App\Http\Middleware\Admin::class])->group(function () {
		Route::resource('devices', DeviceController::class);
		Route::resource('plans', PlanController::class);
		Route::resource('activity-types', ActivityTypeController::class);
		Route::resource('commitment-periods', CommitmentPeriodController::class);
		Route::resource('users', UserController::class);
	});
	
});

// Ensure auth routes are included
require base_path('routes/auth.php');