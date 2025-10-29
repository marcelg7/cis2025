@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container"> <!-- Added px-2 -->
        <h1 class="text-2xl font-semibold text-gray-900">Edit Commitment Period: {{ $commitmentPeriod->name }}</h1>
        <form method="POST" action="{{ route('commitment-periods.update', $commitmentPeriod->id) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $commitmentPeriod->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="cancellation_policy" class="block text-sm font-medium text-gray-700">Cancellation Policy (optional)</label>
                <textarea name="cancellation_policy" id="cancellation_policy" rows="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('cancellation_policy', $commitmentPeriod->cancellation_policy) }}</textarea>
                <p class="mt-2 text-sm text-gray-500">Available variables: {balance}, {monthly_reduction}, {start_date}, {end_date}, {buyout_cost}, {device_return_option}</p>
                @error('cancellation_policy')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="is_active" id="is_active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="1" {{ old('is_active', $commitmentPeriod->is_active) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $commitmentPeriod->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('is_active')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end">
                <a href="{{ route('commitment-periods.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Commitment Period
                </button>
            </div>
        </form>
    </div>
@endsection