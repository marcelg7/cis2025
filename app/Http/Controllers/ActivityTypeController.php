<?php
namespace App\Http\Controllers;

use App\Models\ActivityType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityTypeController extends Controller {
    public function index(): View {
        $activityTypes = ActivityType::all();
        return view('activity-types.index', compact('activityTypes'));
    }

    public function create(): View {
        return view('activity-types.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100|unique:activity_types,name',
            'is_active' => 'required|boolean',
        ]);

        ActivityType::create([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('activity-types.index')->with('success', 'Activity Type added successfully.');
    }

    public function edit($id): View {
        $activityType = ActivityType::findOrFail($id);
        return view('activity-types.edit', compact('activityType'));
    }

    public function update(Request $request, $id) {
        $activityType = ActivityType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:activity_types,name,' . $id,
            'is_active' => 'required|boolean',
        ]);

        $activityType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('activity-types.index')->with('success', 'Activity Type updated successfully.');
    }

    public function destroy($id) {
        $activityType = ActivityType::findOrFail($id);
        $activityType->delete();
        return redirect()->route('activity-types.index')->with('success', 'Activity Type deleted successfully.');
    }
}
