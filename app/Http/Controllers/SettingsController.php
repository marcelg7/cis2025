<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function edit()
    {
        $logPruneDays = Setting::where('key', 'log_prune_days')->first()->value ?? 365;
        $supervisorEmail = Setting::where('key', 'cellular_supervisor_email')->first()->value ?? 'supervisor@example.com';
        return view('admin.settings', compact('logPruneDays', 'supervisorEmail'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'log_prune_days' => 'required|integer|min:30',
            'cellular_supervisor_email' => 'required|email',
        ]);

        Setting::updateOrCreate(['key' => 'log_prune_days'], ['value' => $request->log_prune_days]);
        Setting::updateOrCreate(['key' => 'cellular_supervisor_email'], ['value' => $request->cellular_supervisor_email]);

        return redirect()->back()->with('success', 'Settings updated.');
    }
}