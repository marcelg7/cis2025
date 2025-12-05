@extends('layouts.app')

@section('content')
    <div class="py-8 bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Welcome Header -->
            <div class="mb-8 bg-white rounded-xl shadow-sm p-6">
                <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="mt-2 text-gray-600">Here's what's happening with your contracts today.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Drafts Today Card -->
                <div class="relative group h-full">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-blue-400 rounded-xl opacity-0 group-hover:opacity-100 transition duration-300 blur"></div>
                    <div class="relative bg-white rounded-xl shadow-lg p-6 transition-all duration-300 hover:shadow-xl h-full flex flex-col min-h-[160px]">
                        <div class="flex items-start justify-between flex-1">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Drafts Today</p>
                                <div class="mt-3 flex items-baseline">
                                    <p class="text-4xl font-bold text-gray-900">{{ $stats['drafts_today'] }}</p>
                                    <p class="ml-2 text-sm text-gray-500">of {{ $stats['contracts_today'] }}</p>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Signatures Card -->
                <div class="relative group h-full">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-yellow-600 to-yellow-400 rounded-xl opacity-0 group-hover:opacity-100 transition duration-300 blur"></div>
                    <div class="relative bg-white rounded-xl shadow-lg p-6 transition-all duration-300 hover:shadow-xl h-full flex flex-col min-h-[160px]">
                        <div class="flex items-start justify-between flex-1">
                            <div class="flex-1 flex flex-col">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Pending Signatures</p>
                                <div class="mt-3">
                                    <p class="text-4xl font-bold text-gray-900">{{ $stats['pending_signatures'] }}</p>
                                </div>
                                <div class="mt-auto pt-2">
                                    @if($stats['pending_signatures'] > 0)
                                        <p class="text-xs text-yellow-600 font-medium">Needs attention</p>
                                    @else
                                        <p class="text-xs text-gray-400">&nbsp;</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-yellow-100 rounded-lg">
                                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ready to Finalize Card -->
                <div class="relative group h-full">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-green-600 to-green-400 rounded-xl opacity-0 group-hover:opacity-100 transition duration-300 blur"></div>
                    <div class="relative bg-white rounded-xl shadow-lg p-6 transition-all duration-300 hover:shadow-xl h-full flex flex-col min-h-[160px]">
                        <div class="flex items-start justify-between flex-1">
                            <div class="flex-1 flex flex-col">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Ready to Finalize</p>
                                <div class="mt-3">
                                    <p class="text-4xl font-bold text-gray-900">{{ $stats['ready_to_finalize'] }}</p>
                                </div>
                                <div class="mt-auto pt-2">
                                    @if($stats['ready_to_finalize'] > 0)
                                        <p class="text-xs text-green-600 font-medium">Ready to complete</p>
                                    @else
                                        <p class="text-xs text-gray-400">&nbsp;</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Week Card -->
                <div class="relative group h-full">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-600 to-indigo-400 rounded-xl opacity-0 group-hover:opacity-100 transition duration-300 blur"></div>
                    <div class="relative bg-white rounded-xl shadow-lg p-6 transition-all duration-300 hover:shadow-xl h-full flex flex-col min-h-[160px]">
                        <div class="flex items-start justify-between flex-1">
                            <div class="flex-1 flex flex-col">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">This Week</p>
                                <div class="mt-3">
                                    <p class="text-4xl font-bold text-gray-900">{{ $stats['contracts_this_week'] }}</p>
                                </div>
                                <div class="mt-auto pt-2">
                                    <p class="text-sm text-gray-500">{{ $stats['contracts_this_month'] }} this month</p>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-indigo-100 rounded-lg">
                                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-8">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('customers.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-semibold rounded-lg shadow-md hover:from-indigo-700 hover:to-indigo-600 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Contract
                    </a>
                    <a href="{{ route('contracts.index') }}" class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-semibold rounded-lg shadow-md hover:bg-gray-50 transition-all duration-200 border border-gray-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        All Contracts
                    </a>
                </div>
            </div>

            <!-- Recent Contracts -->
            @if($recentContracts->count() > 0)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-xl font-bold text-gray-900">Your Recent Contracts</h3>
                    <p class="mt-1 text-sm text-gray-600">Last updated {{ now()->diffForHumans() }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Contract</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Last Updated</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentContracts as $contract)
                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <span class="text-indigo-600 font-bold text-sm">#{{ $contract->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-medium">{{ $contract->bellDevice->name ?? 'BYOD' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $contract->ratePlan->plan_name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full
                                        @if($contract->status === 'draft') bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800
                                        @elseif($contract->status === 'pending') bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800
                                        @elseif($contract->status === 'signed') bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800
                                        @elseif($contract->status === 'finalized') bg-gradient-to-r from-green-100 to-green-200 text-green-800
                                        @endif">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $contract->updated_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('contracts.view', $contract->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors duration-150">
                                        View
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-600">Showing {{ $recentContracts->count() }} of your most recent contracts</p>
                        <a href="{{ route('contracts.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold text-sm transition-colors">
                            View All Contracts
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">No recent contracts</h3>
                <p class="mt-2 text-sm text-gray-600">Get started by creating your first contract!</p>
                <div class="mt-6">
                    <a href="{{ route('customers.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-semibold rounded-lg shadow-md hover:from-indigo-700 hover:to-indigo-600 transition-all duration-200">
                        Create New Contract
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>
@endsection
