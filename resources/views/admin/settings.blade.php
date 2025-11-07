@extends('layouts.app')

@section('content')
<div class="py-8">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 page-container">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Settings</h1>
        <p class="mt-2 text-sm text-gray-600">Manage system configuration and preferences</p>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="ml-3 text-sm text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('admin.settings') }}" class="space-y-6">
        @csrf

        <!-- System Settings Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">System Settings</h2>
            </div>
            <div class="p-6 space-y-6">
                <!-- Log Retention and Connection Fee Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Log Retention Period -->
                    <div>
                        <label for="log_prune_days" class="block text-sm font-medium text-gray-700 mb-2">
                            Log Retention Period
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="log_prune_days"
                                name="log_prune_days"
                                value="{{ old('log_prune_days', $logPruneDays) }}"
                                min="30"
                                required
                                class="block w-full px-3 py-2 pr-12 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('log_prune_days') border-red-500 @enderror"
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center pr-8 pointer-events-none">
                                <span class="text-gray-500 text-sm">days</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimum 30 days</p>
                        @error('log_prune_days')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Default Connection Fee -->
                    <div>
                        <label for="default_connection_fee" class="block text-sm font-medium text-gray-700 mb-2">
                            Default Connection Fee
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">$</span>
                            </div>
                            <input
                                type="number"
                                id="default_connection_fee"
                                name="default_connection_fee"
                                value="{{ old('default_connection_fee', $connectionFee) }}"
                                min="0"
                                max="500"
                                step="0.01"
                                required
                                class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('default_connection_fee') border-red-500 @enderror"
                            >
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Added to new contracts</p>
                        @error('default_connection_fee')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Supervisor Email -->
                <div>
                    <label for="cellular_supervisor_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Cellular Supervisor Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input
                            type="email"
                            id="cellular_supervisor_email"
                            name="cellular_supervisor_email"
                            value="{{ old('cellular_supervisor_email', $supervisorEmail) }}"
                            required
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('cellular_supervisor_email') border-red-500 @enderror"
                            placeholder="supervisor@example.com"
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Receives notifications for cellular account issues</p>
                    @error('cellular_supervisor_email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Contract Settings Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Contract Settings</h2>
            </div>
            <div class="p-6 space-y-6">
                <!-- Display Options -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Display Options</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    type="checkbox"
                                    id="show_contract_cost_breakdown"
                                    name="show_contract_cost_breakdown"
                                    value="1"
                                    {{ $showContractCostBreakdown ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                >
                            </div>
                            <div class="ml-3">
                                <label for="show_contract_cost_breakdown" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Show cost breakdown section
                                </label>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Display detailed breakdown with device cost, rate plan, add-ons, and estimated taxes
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deletion Settings -->
                <div class="pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Deletion Settings</h3>
                    <p class="text-xs text-gray-500 mb-3">Select which contract statuses CSRs can delete</p>
                    <div class="space-y-2">
                        @foreach(['draft' => 'Draft', 'pending' => 'Pending'] as $statusValue => $statusLabel)
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="status_{{ $statusValue }}"
                                    name="deletable_statuses[]"
                                    value="{{ $statusValue }}"
                                    {{ in_array($statusValue, $deletableStatuses) ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                >
                                <label for="status_{{ $statusValue }}" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                    {{ $statusLabel }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-md">
                        <p class="text-xs text-amber-800">
                            <strong>Note:</strong> Signed and finalized contracts cannot be deleted for compliance and audit purposes.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ url('/customers') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
            <button
                type="submit"
                class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Settings
            </button>
        </div>
    </form>

    <!-- Help Card -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-900">Need Help?</h3>
                <p class="mt-1 text-sm text-blue-700">
                    Changes take effect immediately. Contact IT support if you need assistance.
                </p>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
