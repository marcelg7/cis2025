@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2"> <!-- Added px-2 -->
        <h1 class="text-2xl font-semibold text-gray-900">Edit Plan: {{ $plan->name }}</h1>
        <form method="POST" action="{{ route('plans.update', $plan->id) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="service_level" class="block text-sm font-medium text-gray-700">Service Level</label>
                <select name="service_level" id="service_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="consumer" {{ old('service_level', $plan->service_level) === 'consumer' ? 'selected' : '' }}>Consumer</option>
                    <option value="business" {{ old('service_level', $plan->service_level) === 'business' ? 'selected' : '' }}>Business</option>
                </select>
                @error('service_level')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="plan_type" class="block text-sm font-medium text-gray-700">Plan Type</label>
                <select name="plan_type" id="plan_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="byod" {{ old('plan_type', $plan->plan_type) === 'byod' ? 'selected' : '' }}>BYOD</option>
                    <option value="smartpay" {{ old('plan_type', $plan->plan_type) === 'smartpay' ? 'selected' : '' }}>SmartPay</option>
                </select>
                @error('plan_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Plan Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                <input type="number" name="price" id="price" step="0.01" value="{{ old('price', $plan->price) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('price')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="details" class="block text-sm font-medium text-gray-700">Plan Details (optional)</label>
                <textarea name="details" id="details" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('details', $plan->details) }}</textarea>
                @error('details')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="is_active" id="is_active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="1" {{ old('is_active', $plan->is_active) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $plan->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('is_active')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end">
                <a href="{{ route('plans.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Plan
                </button>
            </div>
        </form>
    </div>
@endsection