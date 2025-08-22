@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2">
        <h1 class="text-2xl font-bold mb-4">Contracts</h1>
        @if (session('success'))
            <div class="bg-green-50 p-3 rounded-lg shadow-sm mb-6">
                {{ session('success') }}
            </div>
        @endif
        <!-- Filter Form -->
        <form method="GET" action="{{ route('contracts.index') }}" class="mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label for="customer" class="block text-sm font-medium text-gray-700">Customer</label>
                    <input type="text" name="customer" id="customer" value="{{ request('customer') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter customer name">
                </div>
                <div>
                    <label for="device" class="block text-sm font-medium text-gray-700">Device</label>
                    <input type="text" name="device" id="device" value="{{ request('device') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter device details">
                </div>
                <div>
                    <label for="plan" class="block text-sm font-medium text-gray-700">Plan</label>
                    <input type="text" name="plan" id="plan" value="{{ request('plan') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter plan name">
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>
            <div class="mt-4 flex space-x-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Filter</button>
                <a href="{{ route('contracts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Clear Filters</a>
            </div>
        </form>
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead>
                <tr>
                    <th class="py-2 px-4">Contract ID</th>
                    <th class="py-2 px-4">Customer</th>
                    <th class="py-2 px-4">Device</th>
                    <th class="py-2 px-4">Plan</th>
                    <th class="py-2 px-4">Start Date</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contracts as $contract)
                    <tr>
                        <td class="py-2 px-4">{{ $contract->id }}</td>
                        <td class="py-2 px-4">{{ $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name ?? 'N/A' }}</td>
                        <td class="py-2 px-4">
                            {{ implode(', ', array_filter([
                                $contract->manufacturer ? "Manufacturer: " . $contract->manufacturer : null,
                                $contract->model ? "Model: " . $contract->model : null,
                                $contract->version ? "Version: " . $contract->version : null,
                                $contract->device_storage ? "Storage: " . $contract->device_storage : null,
                                $contract->extra_info ? "Extra: " . $contract->extra_info : null,
                            ])) }}
                        </td>
                        <td class="py-2 px-4">{{ $contract->plan->name ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $contract->start_date }}</td>
                        <td class="py-2 px-4 flex space-x-2">
                            <a href="{{ route('contracts.view', $contract->id) }}" class="inline-flex items-center p-2 rounded-full text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            @if($contract->status == 'draft')
                                <a href="{{ route('contracts.edit', $contract->id) }}" class="inline-flex items-center p-2 rounded-full text-yellow-600 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            @endif
                            <a href="{{ route('contracts.download', $contract->id) }}" class="inline-flex items-center p-2 rounded-full text-green-600 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 {{ $contract->status != 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}" title="Download" {{ $contract->status != 'finalized' ? 'disabled' : '' }}>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </a>
                            <a href="{{ route('contracts.email', $contract->id) }}" class="inline-flex items-center p-2 rounded-full text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $contract->status != 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}" title="Email" {{ $contract->status != 'finalized' ? 'disabled' : '' }}>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $contracts->links() }}
    </div>
@endsection