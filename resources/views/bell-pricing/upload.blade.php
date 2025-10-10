@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <a href="{{ route('bell-pricing.index') }}" class="text-indigo-600 hover:text-indigo-900">
            ← Back to Pricing
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Upload Bell Pricing</h1>
            <p class="mt-1 text-sm text-gray-500">Upload an Excel file containing Bell's SmartPay and DRO pricing</p>
        </div>

        @if(session('success'))
            <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('bell-pricing.upload.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Effective Date -->
            <div>
                <label for="effective_date" class="block text-sm font-medium text-gray-700">
                    Effective Date <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       name="effective_date" 
                       id="effective_date" 
                       value="{{ old('effective_date', now()->format('Y-m-d')) }}"
                       required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('effective_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">The date when this pricing becomes effective</p>
            </div>

            <!-- File Upload -->
            <div>
                <label for="pricing_file" class="block text-sm font-medium text-gray-700">
                    Pricing File <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="pricing_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Upload a file</span>
                                <input id="pricing_file" 
                                       name="pricing_file" 
                                       type="file" 
                                       accept=".xlsx,.xls"
                                       required
                                       class="sr-only"
                                       onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'No file chosen'">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">Excel files only (.xlsx, .xls) up to 10MB</p>
                        <p id="file-name" class="text-sm text-gray-700 font-medium mt-2">No file chosen</p>
                    </div>
                </div>
                @error('pricing_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <h3 class="text-sm font-medium text-blue-800 mb-2">File Requirements:</h3>
                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                    <li>File must contain sheets named "SMART PAY" and "DRO - SMARTPAY"</li>
                    <li>Data should start at row 5 for SmartPay and row 6 for DRO</li>
                    <li>Device names must be in column A</li>
                    <li>Retail prices must be in column B</li>
                    <li>Tier information must be in column E (SmartPay) or column F (DRO)</li>
                    <li>All pricing columns should contain numeric values</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('bell-pricing.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Upload & Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection