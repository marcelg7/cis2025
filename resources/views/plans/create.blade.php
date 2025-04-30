@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-semibold text-gray-900">Add New Plan</h1>
        <form method="POST" action="{{ route('plans.store') }}" class="mt-6 space-y-6">
            @csrf
            <div>
                <label for="service_level" class="block text-sm font-medium text-gray-700">Service Level</label>
                <select name="service_level" id="service_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="consumer" {{ old('service_level') === 'consumer' ? 'selected' : '' }}>Consumer</option>
                    <option value="business" {{ old('service_level') === 'business' ? 'selected' : '' }}>Business</option>
                </select>
                @error('service_level')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="plan_type" class="block text-sm font-medium text-gray-700">Plan Type</label>
                <select name="plan_type" id="plan_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="byod" {{ old('plan_type') === 'byod' ? 'selected' : '' }}>BYOD</option>
                    <option value="smartpay" {{ old('plan_type') === 'smartpay' ? 'selected' : '' }}>SmartPay</option>
                </select>
                @error('plan_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Plan Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                <input type="number" name="price" id="price" step="0.01" value="{{ old('price') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('price')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="details" class="block text-sm font-medium text-gray-700">Plan Details (optional)</label>
                <textarea name="details" id="details" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('details') }}</textarea>
                @error('details')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="is_active" id="is_active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', 0) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('is_active')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify