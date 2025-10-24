@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Report a Bug</h1>
        <p class="mt-1 text-sm text-gray-600">Help us improve by reporting issues you encounter</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-red-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-medium mb-1">There were some errors with your submission:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('bug-reports.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg border border-gray-200 shadow-sm">
        @csrf

        <div class="p-6 space-y-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                    placeholder="Brief description of the issue"
                    required
                >
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    class="no-rich-text w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                    placeholder="Please describe the issue in detail. Include steps to reproduce if possible."
                    required
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Include what you expected to happen and what actually happened</p>
            </div>

            <!-- Severity and Category Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Severity -->
                <div>
                    <label for="severity" class="block text-sm font-medium text-gray-700 mb-1">
                        Severity <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="severity"
                        name="severity"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('severity') border-red-500 @enderror"
                        required
                    >
                        <option value="low" {{ old('severity') == 'low' ? 'selected' : '' }}>Low - Minor inconvenience</option>
                        <option value="medium" {{ old('severity', 'medium') == 'medium' ? 'selected' : '' }}>Medium - Noticeable issue</option>
                        <option value="high" {{ old('severity') == 'high' ? 'selected' : '' }}>High - Major problem</option>
                        <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>Critical - System broken</option>
                    </select>
                    @error('severity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                        Category
                    </label>
                    <select
                        id="category"
                        name="category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('category') border-red-500 @enderror"
                    >
                        <option value="ui" {{ old('category') == 'ui' ? 'selected' : '' }}>User Interface</option>
                        <option value="functionality" {{ old('category') == 'functionality' ? 'selected' : '' }}>Functionality</option>
                        <option value="performance" {{ old('category') == 'performance' ? 'selected' : '' }}>Performance</option>
                        <option value="data" {{ old('category') == 'data' ? 'selected' : '' }}>Data Issue</option>
                        <option value="security" {{ old('category') == 'security' ? 'selected' : '' }}>Security</option>
                        <option value="other" {{ old('category', 'other') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- URL -->
            <div>
                <label for="url" class="block text-sm font-medium text-gray-700 mb-1">
                    Page URL
                </label>
                <input
                    type="url"
                    id="url"
                    name="url"
                    value="{{ old('url', request()->headers->get('referer')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('url') border-red-500 @enderror"
                    placeholder="https://example.com/page-where-issue-occurred"
                >
                @error('url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">URL where you encountered the issue (optional)</p>
            </div>

            <!-- Screenshot Upload -->
            <div x-data="{ fileName: '' }">
                <label for="screenshot" class="block text-sm font-medium text-gray-700 mb-1">
                    Screenshot
                </label>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">
                        <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Choose File
                        <input
                            type="file"
                            id="screenshot"
                            name="screenshot"
                            accept="image/*"
                            class="hidden"
                            @change="fileName = $event.target.files[0]?.name || ''"
                        >
                    </label>
                    <span class="text-sm text-gray-600" x-text="fileName || 'No file chosen'"></span>
                </div>
                @error('screenshot')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Upload a screenshot of the issue (max 5MB, optional)</p>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 text-sm text-blue-700">
                        <p><strong>What happens after you submit?</strong></p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            <li>Your report will be sent to our development team</li>
                            <li>You'll be able to track your report's status</li>
                            <li>We may contact you if we need more information</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex items-center justify-end space-x-3">
            <a href="{{ url()->previous() }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                Submit Bug Report
            </button>
        </div>
    </form>

    <!-- Recent Submissions (if any) -->
    <div class="mt-8 bg-gray-50 rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-2">Tips for a Good Bug Report</h2>
        <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-start">
                <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span><strong>Be specific:</strong> Include exact error messages or unexpected behavior</span>
            </li>
            <li class="flex items-start">
                <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span><strong>Steps to reproduce:</strong> Help us recreate the issue by listing what you did</span>
            </li>
            <li class="flex items-start">
                <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span><strong>Screenshots:</strong> A picture is worth a thousand words</span>
            </li>
            <li class="flex items-start">
                <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span><strong>Expected vs actual:</strong> Tell us what should happen and what actually happened</span>
            </li>
        </ul>
    </div>
</div>
@endsection
