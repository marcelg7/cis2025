<?php
namespace App\Http\Controllers;

use App\Models\CommitmentPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommitmentPeriodController extends Controller {
    public function index(): View {
        $commitmentPeriods = CommitmentPeriod::all();
        return view('commitment-periods.index', compact('commitmentPeriods'));
    }

    public function create(): View {
        return view('commitment-periods.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100|unique:commitment_periods,name',
            'cancellation_policy' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        CommitmentPeriod::create([
            'name' => $request->name,
            'cancellation_policy' => $request->cancellation_policy,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('commitment-periods.index')->with('success', 'Commitment Period added successfully.');
    }

    public function edit($id): View {
        $commitmentPeriod = CommitmentPeriod::findOrFail($id);
        return view('commitment-periods.edit', compact('commitmentPeriod'));
    }

    public function update(Request $request, $id) {
        $commitmentPeriod = CommitmentPeriod::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:commitment_periods,name,' . $id,
            'cancellation_policy' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $commitmentPeriod->update([
            'name' => $request->name,
            'cancellation_policy' => $request->cancellation_policy,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('commitment-periods.index')->with('success', 'Commitment Period updated successfully.');
    }

    public function destroy($id) {
        $commitmentPeriod = CommitmentPeriod::findOrFail($id);
        $commitmentPeriod->delete();
        return redirect()->route('commitment-periods.index')->with('success', 'Commitment Period deleted successfully.');
    }
}
