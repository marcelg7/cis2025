<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\Admin::class . ':admin');
    }

    public function index()
    {
        $testContractsCount = DB::table('contracts')->where('is_test', 1)->count();
        $testSubscribersCount = DB::table('subscribers')->where('is_test', 1)->count();
        $testCustomersCount = DB::table('customers')->where('is_test', 1)->count();
        
        return view('admin.index', compact(
            'testContractsCount',
            'testSubscribersCount',
            'testCustomersCount',
        ));
    }

    public function settings()
    {
        $logPruneDays = Setting::get('log_prune_days', 90);
        $supervisorEmail = Setting::get('cellular_supervisor_email', 'supervisor@hay.net');
        $showDevInfo = Setting::get('show_development_info', 'false');
        $connectionFee = Setting::get('default_connection_fee', 80); // NEW
        
        return view('admin.settings', compact(
            'logPruneDays', 
            'supervisorEmail', 
            'showDevInfo',
            'connectionFee' // NEW
        ));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'log_prune_days' => 'required|integer|min:30',
            'cellular_supervisor_email' => 'required|email',
            'default_connection_fee' => 'required|numeric|min:0|max:500', // NEW
        ]);

        Setting::set('log_prune_days', $request->log_prune_days);
        Setting::set('cellular_supervisor_email', $request->cellular_supervisor_email);
        Setting::set('show_development_info', $request->has('show_development_info') ? 'true' : 'false');
        Setting::set('default_connection_fee', $request->default_connection_fee); // NEW

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully!');
    }

    public function clearTestData(Request $request)
    {
        $dryRun = $request->input('dry_run', false);
        $reset = $request->input('reset', false);
        
        $command = ['command' => 'db:clear-test-data'];
        
        if ($dryRun) {
            $command['--dry-run'] = true;
        }
        
        if ($reset) {
            $command['--reset'] = true;
        }
        
        $exitCode = Artisan::call($command['command'], array_filter($command));
        
        if ($exitCode === 0) {
            return redirect()->route('admin.index')->with('success', 'Test data operation completed successfully.');
        }
        
        return redirect()->route('admin.index')->with('error', 'Failed to clear test data.');
    }

    public function seedTestData(Request $request)
    {
        $exitCode = Artisan::call('db:seed');
        
        if ($exitCode === 0) {
            return redirect()->route('admin.index')->with('success', 'Test data seeded successfully.');
        }
        
        return redirect()->route('admin.index')->with('error', 'Failed to seed test data.');
    }
}