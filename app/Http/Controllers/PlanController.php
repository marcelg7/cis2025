<?php
namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller {
    public function index(): View {
        $plans = Plan::all();
        return view('plans.index', compact('plans'));
    }

    public function create(): View {
        return view('plans.create');
    }

    public function store(Request $request) {
        $request->validate([
            'service_level' => 'required|in:consumer,business',
            'plan_type' => 'required|in:byod,smartpay',
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'details' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        Plan::create([
            'service_level' => $request->service_level,
            'plan_type' => $request->plan_type,
            'name' => $request->name,
            'price' => $request->price,
            'details' => $request->details,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('plans.index')->with('success', 'Plan added successfully.');
    }

    public function edit($id): View {
        $plan = Plan::findOrFail($id);
        return view('plans.edit', compact('plan'));
    }

    public function update(Request $request, $id) {
        $plan = Plan::findOrFail($id);

        $request->validate([
            'service_level' => 'required|in:consumer,business',
            'plan_type' => 'required|in:byod,smartpay',
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'details' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $plan->update([
            'service_level' => $request->service_level,
            'plan_type' => $request->plan_type,
            'name' => $request->name,
            'price' => $request->price,
            'details' => $request->details,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy($id) {
        $plan = Plan::findOrFail($id);
        $plan->delete();
        return redirect()->route('plans.index')->with('success', 'Plan deleted successfully.');
    }
}
