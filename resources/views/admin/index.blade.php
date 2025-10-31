@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Test Data Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-4">Test Data Overview</h3>
                    <p class="text-gray-700">Test Contracts: <span class="font-semibold">{{ $testContractsCount }}</span></p>
                    <p class="text-gray-700">Test Subscribers: <span class="font-semibold">{{ $testSubscribersCount }}</span></p>
                    <p class="text-gray-700">Test Customers: <span class="font-semibold">{{ $testCustomersCount }}</span></p>

                    <!-- Clear Test Data Form -->
                    <h4 class="mt-6 text-lg font-medium">Clear Test Data</h4>
                    <form method="POST" action="{{ route('admin.clear-test-data') }}" class="mt-2">
                        @csrf
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="dry_run" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Dry Run</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="reset" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Reset and Reseed</span>
                            </label>
                            <x-danger-button type="submit">Clear Test Data</x-danger-button>
                        </div>
                    </form>

                    <!-- Seed Test Data Form -->
                    <h4 class="mt-6 text-lg font-medium">Seed Test Data</h4>
                    <form method="POST" action="{{ route('admin.seed-test-data') }}" class="mt-2">
                        @csrf
                        <x-primary-button type="submit">Seed Test Data</x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Customer Data Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-4">Customer Data Management</h3>
                    <p class="text-gray-700 mb-4">Total Customers in Database: <span class="font-semibold">{{ $totalCustomersCount }}</span></p>

                    <!-- Refetch All Customers Form -->
                    <h4 class="mt-6 text-lg font-medium">Re-fetch All Customers from NISC</h4>
                    <p class="text-sm text-gray-600 mt-2 mb-4">
                        This will update all customer records from the NISC billing system API.
                        This is useful after code changes to postal code formatting or other customer data processing.
                        <br>
                        <span class="font-semibold text-amber-600">Warning:</span> This may take several minutes depending on the number of customers.
                    </p>
                    <form method="POST" action="{{ route('admin.refetch-all-customers') }}"
                          onsubmit="return confirm('Are you sure you want to re-fetch all {{ $totalCustomersCount }} customers? This may take several minutes.');"
                          x-data="{ submitting: false }">
                        @csrf
                        <x-primary-button type="submit"
                                         @click="submitting = true"
                                         :disabled="submitting"
                                         class="disabled:opacity-50">
                            <span x-show="!submitting">Re-fetch All Customers</span>
                            <span x-show="submitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Messages -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
        </div>
    </div>
@endsection