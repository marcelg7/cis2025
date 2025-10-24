<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SettingsHelper; // CHANGED

class SettingsController extends Controller
{
    public function edit()
    {
        $logPruneDays = SettingsHelper::get('log_prune_days', 90);
        $supervisorEmail = SettingsHelper::get('cellular_supervisor_email', 'supervisor@hay.net');
        $connectionFee = SettingsHelper::get('default_connection_fee', 80);
        $showContractCostBreakdown = SettingsHelper::enabled('show_contract_cost_breakdown');

        return view('admin.settings', compact(
            'logPruneDays',
            'supervisorEmail',
            'connectionFee',
            'showContractCostBreakdown'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'log_prune_days' => 'required|integer|min:30',
            'cellular_supervisor_email' => 'required|email',
            'default_connection_fee' => 'required|numeric|min:0|max:500',
        ]);

        SettingsHelper::set('log_prune_days', $request->log_prune_days);
        SettingsHelper::set('cellular_supervisor_email', $request->cellular_supervisor_email);
        SettingsHelper::set('default_connection_fee', $request->default_connection_fee);
        SettingsHelper::set('show_contract_cost_breakdown', $request->boolean('show_contract_cost_breakdown') ? 'true' : 'false');

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully!');
    }
}