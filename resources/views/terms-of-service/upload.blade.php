@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <a href="{{ route('terms-of-service.index') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Terms of Service
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Upload New Terms of Service</h2>
        </div>

        <form action="{{ route('terms-of-service.store') }}" method="POST" enctype="multipart/form-data" class="px-6 py-6">
            @csrf

            <div class="space-y-6">
                <!-- PDF File Upload -->
                <div>
                    <label for="pdf" class="block text-sm font-medium text-gray-700 mb-2">
                        Terms of Service PDF <span class="text-red-500">*</span>
                    </label>
                    <input type="file" 
                           name="pdf" 
                           id="pdf" 
                           accept=".pdf"
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100"
                           required>
                    @error('pdf')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Maximum file size: 10MB</p>
                </div>

                <!-- Version -->
                <div>
                    <label for="version" class="block text-sm font-medium text-gray-700 mb-2">
                        Version Number
                    </label>
                    <input type="text" 
                           name="version" 
                           id="version" 
                           placeholder="e.g., v2.1, 2024-01, March 2024"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('version')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Leave blank to auto-generate (v1, v2, v3, etc.)</p>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="3"
                              placeholder="e.g., Updated privacy policy section, Added arbitration clause"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Describe what changed in this version</p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Important Information</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>The uploaded PDF will NOT be automatically activated</li>
                                    <li>After upload, you must click "Activate" to make it the active version</li>
                                    <li>Once activated, this version will be merged with all new contracts</li>
                                    <li>Previous versions will be kept for historical reference</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('terms-of-service.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload Terms of Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection