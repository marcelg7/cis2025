@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
    <div class="mb-6">
        <a href="{{ route('contract-templates.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Templates
        </a>
        <h1 class="mt-2 text-2xl font-semibold text-gray-900">Create Template</h1>
        <p class="mt-1 text-sm text-gray-600">Save a contract configuration as a template for quick reuse</p>
    </div>

    <form method="POST" action="{{ route('contract-templates.store') }}" class="space-y-6">
        @csrf

        <!-- Template Name & Description -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Template Details</h2>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required placeholder="e.g., iPhone 15 + Ultra Plan">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                    <textarea name="description" id="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Brief description of when to use this template">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(auth()->user()->hasRole('admin'))
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_team_template" id="is_team_template" value="1" {{ old('is_team_template') ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_team_template" class="font-medium text-gray-700">Team Template</label>
                            <p class="text-gray-500">Make this template available to all users (admin only)</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Contract Configuration -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Contract Configuration</h2>
            <p class="text-sm text-gray-600 mb-4">Select the default values for contracts created with this template</p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="activity_type_id" class="block text-sm font-medium text-gray-700">Activity Type</label>
                    <select name="activity_type_id" id="activity_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">-- Not Set --</option>
                        @foreach(\App\Models\ActivityType::orderBy('name')->get() as $activityType)
                            <option value="{{ $activityType->id }}" {{ old('activity_type_id') == $activityType->id ? 'selected' : '' }}>
                                {{ $activityType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="location_id" class="block text-sm font-medium text-gray-700">Location</label>
                    <select name="location_id" id="location_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">-- Not Set --</option>
                        @foreach(\App\Models\Location::orderBy('name')->get() as $location)
                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="bell_device_id" class="block text-sm font-medium text-gray-700">Device</label>
                    <select name="bell_device_id" id="bell_device_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">-- Not Set --</option>
                        @foreach(\App\Models\BellDevice::orderBy('name')->get() as $device)
                            <option value="{{ $device->id }}" {{ old('bell_device_id') == $device->id ? 'selected' : '' }}>
                                {{ $device->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="rate_plan_id" class="block text-sm font-medium text-gray-700">Rate Plan</label>
                    <select name="rate_plan_id" id="rate_plan_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">-- Not Set --</option>
                        @foreach(\App\Models\RatePlan::orderBy('name')->get() as $plan)
                            <option value="{{ $plan->id }}" {{ old('rate_plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="commitment_period_id" class="block text-sm font-medium text-gray-700">Commitment Period</label>
                    <select name="commitment_period_id" id="commitment_period_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">-- Not Set --</option>
                        @foreach(\App\Models\CommitmentPeriod::orderBy('length_months')->get() as $period)
                            <option value="{{ $period->id }}" {{ old('commitment_period_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('contract-templates.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                Create Template
            </button>
        </div>
    </form>
</div>
@endsection
