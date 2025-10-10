@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3>Test Data Overview</h3>
                    <p>Test Contracts: {{ $testContractsCount }}</p>
                    <p>Test Subscribers: {{ $testSubscribersCount }}</p>
                    <p>Test Customers: {{ $testCustomersCount }}</p>

                    <!-- Clear Test Data Form -->
                    <h4 class="mt-4">Clear Test Data</h4>
                    <form method="POST" action="{{ route('admin.clear-test-data') }}">
                        @csrf
                        <div class="flex space-x-2">
                            <label>
                                <input type="checkbox" name="dry_run" value="1"> Dry Run
                            </label>
                            <label>
                                <input type="checkbox" name="reset" value="1"> Reset and Reseed
                            </label>
                            <button type="submit" class="bg-red-500 text-white p-2 rounded">Clear Test Data</button>
                        </div>
                    </form>
                    <!-- Seed Test Data Form -->
                    <h4 class="mt-4">Seed Test Data</h4>
                    <form method="POST" action="{{ route('admin.seed-test-data') }}">
                        @csrf
                        <button type="submit" class="bg-green-500 text-white p-2 rounded">Seed Test Data</button>
                    </form>
                    @if (session('success'))
                        <p class="text-green-500 mt-2">{{ session('success') }}</p>
                    @endif
                    @if (session('error'))
                        <p class="text-red-500 mt-2">{{ session('error') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection