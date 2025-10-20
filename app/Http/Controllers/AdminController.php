<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\Admin::class . ':admin'); // Explicit class with parameter
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

	public function update(Request $request)
	{
		$request->validate([
			'log_prune_days' => 'required|integer|min:30',
		]);

		Setting::updateOrCreate(['key' => 'log_prune_days'], ['value' => $request->log_prune_days]);
		// ... other settings
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