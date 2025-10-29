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
use App\Http\Controllers\ReadmeController;
use App\Http\Controllers\CheatSheetController;
use App\Http\Controllers\TermsOfServiceController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BugReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CalculatorController;

/*
|--------------------------------------------------------------------------
| Root Route
|--------------------------------------------------------------------------
*/

// Redirect root to customers page
Route::get('/', function () {
    return redirect()->route('customers.index');
});

/*
|--------------------------------------------------------------------------
| Guest Routes (Unauthenticated)
|--------------------------------------------------------------------------
*/

// Password Reset Routes (must be outside auth middleware)
// Rate limited to prevent brute force attacks
Route::middleware(['guest', 'throttle:100,1'])->group(function () {
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (ALL authenticated users)
|--------------------------------------------------------------------------
*/

// Apply rate limiting to authenticated routes (200 requests per minute)
Route::middleware(['auth', 'throttle:200,1'])->group(function () {  // CHANGED FROM 'admin' to 'auth'
    
    /*
    |--------------------------------------------------------------------------
    | Customer Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/fetch', [CustomerController::class, 'fetch'])->name('fetch');
        Route::get('/search', [CustomerController::class, 'search'])->name('search');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/add-mobility', [CustomerController::class, 'addMobilityForm'])->name('add-mobility');
        Route::post('/{customer}/add-mobility', [CustomerController::class, 'storeMobility'])->name('store-mobility');
        Route::get('/{customer}/add-subscriber', [CustomerController::class, 'addSubscriberForm'])->name('add-subscriber');
        Route::post('/{customer}/add-subscriber', [CustomerController::class, 'storeSubscriber'])->name('store-subscriber');
    });

    /*
    |--------------------------------------------------------------------------
    | Contract Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('contracts')->name('contracts.')->group(function () {
        // List & View
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/{contract}/view', [ContractController::class, 'view'])->name('view');
        
        // Create & Update
        Route::get('/{subscriber}/create', [ContractController::class, 'create'])->name('create');
        Route::post('/{subscriber}', [ContractController::class, 'store'])->name('store');
        Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('/{contract}', [ContractController::class, 'update'])->name('update');
        
        // Signing & Finalization
        Route::get('/{contract}/sign', [ContractController::class, 'sign'])->name('sign');
        Route::post('/{contract}/sign', [ContractController::class, 'storeSignature'])->name('storeSignature');
        // SECURITY: Rate limit sensitive operations - balanced for workflow usability
        Route::post('/{contract}/finalize', [ContractController::class, 'finalize'])->name('finalize')->middleware('throttle:100,1');
        Route::post('/{contract}/revision', [ContractController::class, 'createRevision'])->name('revision');

        // Export & Communication
        Route::get('/{contract}/download', [ContractController::class, 'download'])->name('download');
        Route::post('/{contract}/email', [ContractController::class, 'email'])->name('email')->middleware('throttle:50,1');
        Route::post('/{contract}/ftp', [ContractController::class, 'ftp'])->name('ftp')->middleware('throttle:50,1');
        
        // Financing Form
        Route::prefix('{contract}/financing')->name('financing.')->group(function () {
            Route::get('/', [ContractController::class, 'financingForm'])->name('index');
            Route::get('/sign', [ContractController::class, 'signFinancing'])->name('sign');
            Route::post('/signature', [ContractController::class, 'storeFinancingSignature'])->name('signature');
            Route::post('/finalize', [ContractController::class, 'finalizeFinancing'])->name('finalize');
            Route::get('/download', [ContractController::class, 'downloadFinancing'])->name('download');
            Route::get('/csr-initial', [ContractController::class, 'signCsrFinancing'])->name('csr-initial');
            Route::post('/csr-initial', [ContractController::class, 'storeCsrFinancingInitials'])->name('store-csr-initial');
        });
        
        // DRO Form
        Route::prefix('{contract}/dro')->name('dro.')->group(function () {
            Route::get('/', [ContractController::class, 'droForm'])->name('index');
            Route::get('/sign', [ContractController::class, 'signDro'])->name('sign');
            Route::post('/signature', [ContractController::class, 'storeDroSignature'])->name('signature');
            Route::post('/finalize', [ContractController::class, 'finalizeDro'])->name('finalize');
            Route::get('/download', [ContractController::class, 'downloadDro'])->name('download');
            Route::get('/csr-initial', [ContractController::class, 'signCsrDro'])->name('csr-initial');
            Route::post('/csr-initial', [ContractController::class, 'storeCsrDroInitials'])->name('store-csr-initial');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Pricing Management
    |--------------------------------------------------------------------------
    */
    
    // Bell Device Pricing
    Route::prefix('bell-pricing')->name('bell-pricing.')->group(function () {
        Route::get('/', [BellPricingController::class, 'index'])->name('index');
        Route::get('/upload', [BellPricingController::class, 'uploadForm'])->name('upload')->middleware('permission:upload-device-pricing');
        Route::post('/upload', [BellPricingController::class, 'upload'])->name('upload.store')->middleware('permission:upload-device-pricing');
        Route::get('/compare', [BellPricingController::class, 'compare'])->name('compare');
        Route::get('/{id}', [BellPricingController::class, 'show'])->name('show');
        Route::get('/{id}/history', [BellPricingController::class, 'history'])->name('history');
    });
    
    // Cellular Pricing
    Route::prefix('cellular-pricing')->name('cellular-pricing.')->group(function () {
        // Upload
        Route::get('/upload', [CellularPricingController::class, 'upload'])->name('upload')->middleware('permission:upload-plan-pricing');
        Route::post('/import', [CellularPricingController::class, 'import'])->name('import')->middleware('permission:upload-plan-pricing');
        
        // Browse
        Route::get('/rate-plans', [CellularPricingController::class, 'ratePlans'])->name('rate-plans');
        Route::get('/rate-plans/{id}', [CellularPricingController::class, 'ratePlanShow'])->name('rate-plan-show');
        Route::get('/mobile-internet', [CellularPricingController::class, 'mobileInternet'])->name('mobile-internet');
        Route::get('/add-ons', [CellularPricingController::class, 'addOns'])->name('add-ons');
        Route::get('/compare', [CellularPricingController::class, 'compare'])->name('compare');
        
        // Edit Rate Plans
        Route::get('/rate-plans/{ratePlan}/edit', [RatePlanController::class, 'edit'])->name('rate-plans.edit');
        Route::put('/rate-plans/{ratePlan}', [RatePlanController::class, 'update'])->name('rate-plans.update');
        
        // Edit Mobile Internet Plans
        Route::get('/mobile-internet/{mobileInternetPlan}', [MobileInternetPlanController::class, 'show'])->name('mobile-internet.show');
        Route::get('/mobile-internet/{mobileInternetPlan}/edit', [MobileInternetPlanController::class, 'edit'])->name('mobile-internet.edit');
        Route::put('/mobile-internet/{mobileInternetPlan}', [MobileInternetPlanController::class, 'update'])->name('mobile-internet.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/my', [LogController::class, 'myLogs'])->name('my');
        Route::post('/request-review', [LogController::class, 'requestReview'])->name('request-review');
        Route::get('/all', [LogController::class, 'allLogs'])
            ->name('all')
            ->middleware('permission:view_all_logs');
    });

    /*
    |--------------------------------------------------------------------------
    | User Settings & Utilities
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', [UserSettingsController::class, 'edit'])->name('users.settings.edit');
    Route::patch('/settings', [UserSettingsController::class, 'update'])->name('users.settings.update');
    Route::get('/notification-preferences', [UserSettingsController::class, 'editNotificationPreferences'])->name('users.notification-preferences.edit');
    Route::patch('/notification-preferences', [UserSettingsController::class, 'updateNotificationPreferences'])->name('users.notification-preferences.update');
    Route::get('/change-password', fn() => view('auth.change-password'))->name('password.custom_change');
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    Route::get('/changelog', [ChangelogController::class, 'index'])->name('changelog');
    Route::get('/readme', [ReadmeController::class, 'index'])->name('readme');
    Route::get('/cheat-sheet', [CheatSheetController::class, 'index'])->name('cheat-sheet');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    /*
    |--------------------------------------------------------------------------
    | Report Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/contract-summary', [ReportController::class, 'contractSummary'])->name('contract-summary');
        Route::get('/device-sales', [ReportController::class, 'deviceSales'])->name('device-sales');
        Route::get('/plan-adoption', [ReportController::class, 'planAdoption'])->name('plan-adoption');
    });

    /*
    |--------------------------------------------------------------------------
    | Inventory Test Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/test', [InventoryController::class, 'index'])->name('test');
        Route::post('/test/equipment-by-id', [InventoryController::class, 'testEquipmentById'])->name('test-equipment-by-id');
        Route::post('/test/equipment-by-name', [InventoryController::class, 'testEquipmentByName'])->name('test-equipment-by-name');
        Route::post('/test/facility-equipment', [InventoryController::class, 'testFacilityEquipment'])->name('test-facility-equipment');
        Route::post('/test/custom-endpoint', [InventoryController::class, 'testCustomEndpoint'])->name('test-custom-endpoint');
    });

    /*
    |--------------------------------------------------------------------------
    | Calculator Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('calculator')->name('calculator.')->group(function () {
        Route::get('/', [CalculatorController::class, 'index'])->name('index');
        Route::post('/calculate', [CalculatorController::class, 'calculate'])->name('calculate');
        Route::post('/save', [CalculatorController::class, 'save'])->name('save');
        Route::get('/load/{id}', [CalculatorController::class, 'load'])->name('load');
        Route::delete('/delete/{id}', [CalculatorController::class, 'delete'])->name('delete');
        Route::post('/export-pdf', [CalculatorController::class, 'exportPdf'])->name('export-pdf');
        Route::post('/email', [CalculatorController::class, 'email'])->name('email');
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::get('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Bug Report Routes (User Submission)
    |--------------------------------------------------------------------------
    */
    Route::prefix('bug-reports')->name('bug-reports.')->group(function () {
        Route::get('/create', [BugReportController::class, 'create'])->name('create');
        Route::post('/', [BugReportController::class, 'store'])->name('store');
    });

    /*
    |--------------------------------------------------------------------------
    | Mobile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/mobile/devices', [MobileController::class, 'devices'])->name('mobile.devices');

    /*
    |--------------------------------------------------------------------------
    | API Routes (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->group(function () {
        // Cellular Pricing API
        Route::get('/cellular-pricing/rate-plan', [CellularPricingController::class, 'getPricing']);
        Route::get('/cellular-pricing/mobile-internet', [CellularPricingController::class, 'getMobileInternetPricing']);
        Route::get('/cellular-pricing/add-on', [CellularPricingController::class, 'getAddOnPricing']);
        
        // Bell Pricing API
        Route::get('/bell-pricing', [BellPricingController::class, 'getPricing'])->name('api.bell-pricing');
        Route::get('/bell-pricing/device', [BellPricingController::class, 'getDevicePricing'])->name('api.bell-pricing.device');
        
        // Bell Device Compatibility
        Route::get('/bell-devices/compatible', [BellDeviceController::class, 'compatible']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->group(function () {
        // Admin Dashboard & Data Management
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('index');
            Route::post('/clear-test-data', [AdminController::class, 'clearTestData'])->name('clear-test-data');
            Route::post('/seed-test-data', [AdminController::class, 'seedTestData'])->name('seed-test-data');
            Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
            Route::post('/settings', [SettingsController::class, 'update']);

            // Backup Management
            Route::prefix('backups')->name('backups.')->group(function () {
                Route::get('/', [BackupController::class, 'index'])->name('index');
                Route::post('/run', [BackupController::class, 'run'])->name('run');
                Route::get('/settings', [BackupController::class, 'settings'])->name('settings');
                Route::post('/settings', [BackupController::class, 'updateSettings'])->name('update-settings');
                Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
                Route::delete('/delete/{filename}', [BackupController::class, 'delete'])->name('delete');
            });
        });

        // Bug Report Management (Admin Only)
        Route::prefix('bug-reports')->name('bug-reports.')->group(function () {
            Route::get('/', [BugReportController::class, 'index'])->name('index');
            Route::get('/{bugReport}', [BugReportController::class, 'show'])->name('show');
            Route::patch('/{bugReport}', [BugReportController::class, 'update'])->name('update');
            Route::delete('/{bugReport}', [BugReportController::class, 'destroy'])->name('destroy');
        });

        // Resource Controllers
        Route::resource('activity-types', ActivityTypeController::class);
        Route::resource('commitment-periods', CommitmentPeriodController::class);
        Route::resource('users', UserController::class);
        Route::resource('locations', LocationController::class);
        // Roles and Permissions
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);		
        
        // Terms of Service Management
        Route::prefix('terms-of-service')->name('terms-of-service.')->group(function () {
            Route::get('/', [TermsOfServiceController::class, 'index'])->name('index');
            Route::get('/create', [TermsOfServiceController::class, 'create'])->name('create');
            Route::post('/', [TermsOfServiceController::class, 'store'])->name('store');
            Route::post('/{id}/activate', [TermsOfServiceController::class, 'activate'])->name('activate');
            Route::get('/{id}/download', [TermsOfServiceController::class, 'download'])->name('download');
            Route::delete('/{id}', [TermsOfServiceController::class, 'destroy'])->name('destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require base_path('routes/auth.php');