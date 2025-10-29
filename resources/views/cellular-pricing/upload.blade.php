@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6 page-container">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Cellular Price Plans Management</h1>
        <p class="mt-2 text-sm text-gray-600">Upload Excel files to import rate plans, mobile internet plans, and add-ons</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Upload Form -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Upload Pricing File</h2>
        </div>
        
        <form action="{{ route('cellular-pricing.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <!-- File Upload -->
                <div>
                    <label for="pricing_file" class="block text-sm font-medium text-gray-700">
                        Excel File
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="pricing_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="pricing_file" name="pricing_file" type="file" class="sr-only" accept=".xlsx,.xls" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">XLSX or XLS up to 10MB</p>
                        </div>
                    </div>
                    @error('pricing_file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Effective Date -->
                <div>
                    <label for="effective_date" class="block text-sm font-medium text-gray-700">
                        Effective Date
                    </label>
                    <input type="date" 
                           name="effective_date" 
                           id="effective_date" 
                           value="{{ old('effective_date', now()->toDateString()) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           required>
                    <p class="mt-1 text-sm text-gray-500">The date when these prices become effective</p>
                    @error('effective_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Replace Option -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="replace" 
                               name="replace" 
                               type="checkbox" 
                               value="1"
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="replace" class="font-medium text-gray-700">Replace existing data for this date</label>
                        <p class="text-gray-500">If checked, existing pricing for this effective date will be deleted before importing</p>
                    </div>
                </div>

                <!-- Expected File Format -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-blue-900 mb-2">Expected File Format</h3>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li><strong>Rate Plan Overview</strong> sheet with BYOD and SmartPay plans</li>
                        <li><strong>Mobile Internet</strong> sheet with mobile internet plans</li>
                        <li><strong>Plan Add-Ons</strong> sheet with available add-ons</li>
                    </ul>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('cellular-pricing.rate-plans') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Browse Current Pricing →
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Pricing
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Links -->
    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('cellular-pricing.rate-plans') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Rate Plans</h3>
            <p class="text-sm text-gray-600">Browse BYOD and SmartPay rate plans</p>
        </a>
        
        <a href="{{ route('cellular-pricing.mobile-internet') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Mobile Internet</h3>
            <p class="text-sm text-gray-600">View mobile internet plans</p>
        </a>
        
        <a href="{{ route('cellular-pricing.add-ons') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Plan Add-Ons</h3>
            <p class="text-sm text-gray-600">Browse available add-ons</p>
        </a>
    </div>
</div>

<script>
// File input preview
document.getElementById('pricing_file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    if (fileName) {
        const label = this.previousElementSibling.querySelector('span');
        label.textContent = fileName;
    }
});
</script>
@endsection