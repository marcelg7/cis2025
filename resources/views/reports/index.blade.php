@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h1 class="text-3xl font-bold text-gray-900">Reports</h1>
                <p class="mt-2 text-sm text-gray-600">Generate and export comprehensive business reports</p>
            </div>
        </div>

        <!-- Reports Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Contract Summary Report -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h2 class="ml-3 text-xl font-semibold text-gray-900">Contract Summary</h2>
                </div>
                <p class="text-gray-600 mb-4">Monthly overview of all contracts including activity types, locations, and revenue breakdown.</p>
                <a href="{{ route('reports.contract-summary') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    View Report
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Device Sales Report -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <h2 class="ml-3 text-xl font-semibold text-gray-900">Device Sales</h2>
                </div>
                <p class="text-gray-600 mb-4">Detailed breakdown of device sales by model, pricing type, and sales representative.</p>
                <a href="{{ route('reports.device-sales') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                    View Report
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Plan Adoption Report -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h2 class="ml-3 text-xl font-semibold text-gray-900">Plan Adoption</h2>
                </div>
                <p class="text-gray-600 mb-4">Track rate plan and mobile internet plan adoption trends over time.</p>
                <a href="{{ route('reports.plan-adoption') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                    View Report
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- CSR Usage Report (Admin Only) -->
        @can('admin-access')
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="ml-3 text-xl font-semibold text-gray-900">CSR Usage</h2>
                </div>
                <p class="text-gray-600 mb-4">Track CSR login activity, session duration, and system usage statistics.</p>
                <a href="{{ route('reports.usage') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors">
                    View Report
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
        @endcan
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-900">Export Options</h3>
                    <p class="mt-1 text-sm text-blue-700">All reports can be exported to Excel or PDF format for easy sharing and analysis.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
