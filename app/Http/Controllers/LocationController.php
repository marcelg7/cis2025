<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of locations
     */
    public function index()
    {
        $locations = Location::withCount('users')->orderBy('name')->get();
        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location
     */
    public function create()
    {
        return view('locations.create');
    }

    /**
     * Store a newly created location
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:locations,code',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        Location::create($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Location created successfully!');
    }

    /**
     * Show the form for editing a location
     */
    public function edit(Location $location)
    {
        return view('locations.edit', compact('location'));
    }

    /**
     * Update the specified location
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:locations,code,' . $location->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Location updated successfully!');
    }

    /**
     * Remove the specified location
     */
    public function destroy(Location $location)
    {
        // Check if location has users assigned
        if ($location->users()->count() > 0) {
            return redirect()->route('locations.index')
                ->with('error', 'Cannot delete location with assigned users.');
        }

        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Location deleted successfully!');
    }
}
