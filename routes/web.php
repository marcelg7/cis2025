<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\ActivityTypeController;
use App\Http\Controllers\CommitmentPeriodController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BellPricingController;
use App\Http\Controllers\Api\BellDeviceController;
use App\Http\Controllers\CellularPricingController;
use App\Http\Controllers\RatePlanController;
use App\Http\Controllers\MobileInternetPlanController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\TermsOfServiceController;


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
    })->name('password.custom_change');
    // Admin Routes
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/clear-test-data', [AdminController::class, 'clearTestData'])->name('admin.clear-test-data');
    Route::post('/admin/seed-test-data', [AdminController::class, 'seedTestData'])->name('admin.seed-test-data');
    // Settings Routes
    Route::get('/settings', [UserSettingsController::class, 'edit'])->name('users.settings.edit');
    Route::patch('/settings', [UserSettingsController::class, 'update'])->name('users.settings.update');
    Route::get('/mobile/devices', [MobileController::class, 'devices'])->name('mobile.devices');
    Route::get('/test-wp', [MobileController::class, 'testWordpress'])->name('test.wordpress');
    // Search Route
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    // Contract Routes
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/{subscriber}/create', [ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts/{subscriber}', [ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}/view', [ContractController::class, 'view'])->name('contracts.view');
    Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
    Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    Route::get('/contracts/{contract}/sign', [ContractController::class, 'sign'])->name('contracts.sign');
    Route::post('/contracts/{contract}/sign', [ContractController::class, 'storeSignature'])->name('contracts.storeSignature');
    Route::post('/contracts/{contract}/finalize', [ContractController::class, 'finalize'])->name('contracts.finalize');
    Route::post('/contracts/{contract}/revision', [ContractController::class, 'createRevision'])->name('contracts.revision');
    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
    Route::post('/contracts/{contract}/email', [ContractController::class, 'email'])->name('contracts.email');
    Route::get('/contracts/{contract}/ftp', [ContractController::class, 'ftp'])->name('contracts.ftp');
		
	// Financing Form Routes
	Route::get('/contracts/{contract}/financing', [ContractController::class, 'financingForm'])->name('contracts.financing');
	Route::get('/contracts/{contract}/financing/sign', [ContractController::class, 'signFinancing'])->name('contracts.financing.sign');
	Route::post('/contracts/{contract}/financing/signature', [ContractController::class, 'storeFinancingSignature'])->name('contracts.financing.signature');
	Route::post('/contracts/{contract}/financing/finalize', [ContractController::class, 'finalizeFinancing'])->name('contracts.financing.finalize');
	Route::get('/contracts/{contract}/financing/download', [ContractController::class, 'downloadFinancing'])->name('contracts.financing.download');
	
	// Financing CSR Initials
	Route::get('contracts/{id}/financing/csr-initial', [ContractController::class, 'signCsrFinancing'])->name('contracts.financing.csr-initial');
	Route::post('contracts/{id}/financing/csr-initial', [ContractController::class, 'storeCsrFinancingInitials'])->name('contracts.financing.store-csr-initial');
	
	// DRO Form Routes
	Route::get('/contracts/{contract}/dro', [ContractController::class, 'droForm'])->name('contracts.dro');
	Route::get('/contracts/{contract}/dro/sign', [ContractController::class, 'signDro'])->name('contracts.dro.sign');
	Route::post('/contracts/{contract}/dro/signature', [ContractController::class, 'storeDroSignature'])->name('contracts.dro.signature');
	Route::post('/contracts/{contract}/dro/finalize', [ContractController::class, 'finalizeDro'])->name('contracts.dro.finalize');
	Route::get('/contracts/{contract}/dro/download', [ContractController::class, 'downloadDro'])->name('contracts.dro.download');

	// DRO CSR Initials
	Route::get('contracts/{id}/dro/csr-initial', [ContractController::class, 'signCsrDro'])->name('contracts.dro.csr-initial');
	Route::post('contracts/{id}/dro/csr-initial', [ContractController::class, 'storeCsrDroInitials'])->name('contracts.dro.store-csr-initial');	

	
    // Bell Pricing Routes
    Route::get('/bell-pricing', [BellPricingController::class, 'index'])->name('bell-pricing.index');
    Route::get('/bell-pricing/upload', [BellPricingController::class, 'uploadForm'])->name('bell-pricing.upload');
    Route::post('/bell-pricing/upload', [BellPricingController::class, 'upload'])->name('bell-pricing.upload.store');
    Route::get('/bell-pricing/{id}', [BellPricingController::class, 'show'])->name('bell-pricing.show');
    Route::get('/bell-pricing/{id}/history', [BellPricingController::class, 'history'])->name('bell-pricing.history');
    Route::get('/bell-pricing-compare', [BellPricingController::class, 'compare'])->name('bell-pricing.compare');
    
    // API endpoints for Bell Pricing
    Route::get('/api/bell-pricing', [BellPricingController::class, 'getPricing'])->name('api.bell-pricing');
    Route::get('/api/bell-pricing/device', [BellPricingController::class, 'getDevicePricing'])->name('api.bell-pricing.device');


		
	// Rate Plans - Edit Routes
	Route::get('/cellular-pricing/rate-plans/{ratePlan}/edit', [RatePlanController::class, 'edit'])->name('cellular-pricing.rate-plans.edit');
	Route::put('/cellular-pricing/rate-plans/{ratePlan}', [RatePlanController::class, 'update'])->name('cellular-pricing.rate-plans.update');

	// Mobile Internet Plans - Edit Routes
	Route::get('/cellular-pricing/mobile-internet/{mobileInternetPlan}', [MobileInternetPlanController::class, 'show'])->name('cellular-pricing.mobile-internet.show');
	Route::get('/cellular-pricing/mobile-internet/{mobileInternetPlan}/edit', [MobileInternetPlanController::class, 'edit'])->name('cellular-pricing.mobile-internet.edit');
	Route::put('/cellular-pricing/mobile-internet/{mobileInternetPlan}', [MobileInternetPlanController::class, 'update'])->name('cellular-pricing.mobile-internet.update');
	

	
	// Cellular Pricing Routes
    // Upload interface
    Route::get('/cellular-pricing/upload', [CellularPricingController::class, 'upload'])->name('cellular-pricing.upload');
    Route::post('/cellular-pricing/import', [CellularPricingController::class, 'import'])->name('cellular-pricing.import');
    // Browse interfaces
    Route::get('/cellular-pricing/rate-plans', [CellularPricingController::class, 'ratePlans'])->name('cellular-pricing.rate-plans');
    Route::get('/cellular-pricing/rate-plans/{id}', [CellularPricingController::class, 'ratePlanShow'])->name('cellular-pricing.rate-plan-show');
    Route::get('/cellular-pricing/mobile-internet', [CellularPricingController::class, 'mobileInternet'])->name('cellular-pricing.mobile-internet');
    Route::get('/cellular-pricing/add-ons', [CellularPricingController::class, 'addOns'])->name('cellular-pricing.add-ons');
    // Compare
    Route::get('/cellular-pricing/compare', [CellularPricingController::class, 'compare'])->name('cellular-pricing.compare');
	
	Route::middleware(['auth'])->get('/changelog', [ChangelogController::class, 'index'])->name('changelog');

	// Admin-Only Routes
	Route::middleware([\App\Http\Middleware\Admin::class])->group(function () {
		Route::resource('activity-types', ActivityTypeController::class);
		Route::resource('commitment-periods', CommitmentPeriodController::class);
		Route::resource('users', UserController::class);
		
		// NEW: Terms of Service Management
		Route::get('/terms-of-service', [TermsOfServiceController::class, 'index'])->name('terms-of-service.index');
		Route::get('/terms-of-service/create', [TermsOfServiceController::class, 'create'])->name('terms-of-service.create');
		Route::post('/terms-of-service', [TermsOfServiceController::class, 'store'])->name('terms-of-service.store');
		Route::post('/terms-of-service/{id}/activate', [TermsOfServiceController::class, 'activate'])->name('terms-of-service.activate');
		Route::get('/terms-of-service/{id}/download', [TermsOfServiceController::class, 'download'])->name('terms-of-service.download');
		Route::delete('/terms-of-service/{id}', [TermsOfServiceController::class, 'destroy'])->name('terms-of-service.destroy');
	});
	
	
	Route::middleware(['auth'])->prefix('api')->group(function () {
		// API endpoints for pricing lookups
		Route::get('/cellular-pricing/rate-plan', [CellularPricingController::class, 'getPricing']);
		Route::get('/cellular-pricing/mobile-internet', [CellularPricingController::class, 'getMobileInternetPricing']);
		Route::get('/cellular-pricing/add-on', [CellularPricingController::class, 'getAddOnPricing']);
		// API endpoints for Bell Pricing
		Route::get('/api/bell-pricing', [BellPricingController::class, 'getPricing'])->name('api.bell-pricing');
		Route::get('/api/bell-pricing/device', [BellPricingController::class, 'getDevicePricing'])->name('api.bell-pricing.device');
		// API endpoints for Bell Device Compatibility
		Route::get('/bell-devices/compatible', [BellDeviceController::class, 'compatible']);
	
	});
	
});
Route::get('/test-alpine', fn() => view('test-alpine'))->name('test.alpine');
// Ensure auth routes are included
require base_path('routes/auth.php');