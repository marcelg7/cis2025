@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 page-container">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Terms of Service Management</h1>
        <a href="{{ route('terms-of-service.create') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Upload New Version
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    <!-- Active Version Card -->
    @if($activeVersion)
        <div class="bg-green-50 border-2 border-green-500 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-green-900 mb-2">
                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Currently Active Version
                    </h3>
                    <p class="text-sm text-green-800"><strong>Version:</strong> {{ $activeVersion->version }}</p>
                    <p class="text-sm text-green-800"><strong>Filename:</strong> {{ $activeVersion->filename }}</p>
                    <p class="text-sm text-green-800"><strong>Uploaded:</strong> {{ $activeVersion->created_at->format('F d, Y g:i A') }}</p>
                    <p class="text-sm text-green-800"><strong>Uploaded by:</strong> {{ $activeVersion->uploader->name }}</p>
                    @if($activeVersion->notes)
                        <p class="text-sm text-green-800 mt-2"><strong>Notes:</strong> {{ $activeVersion->notes }}</p>
                    @endif
                </div>
                <a href="{{ route('terms-of-service.download', $activeVersion->id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-green-700 text-sm font-medium rounded-md text-green-700 bg-white hover:bg-green-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <p class="text-yellow-800">
                <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                No active Terms of Service version. Please upload and activate one.
            </p>
        </div>
    @endif

    <!-- Version History -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Version History</h2>
        </div>

        @if($versions->isEmpty())
            <div class="px-6 py-8 text-center text-gray-500">
                No versions uploaded yet.
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filename</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($versions as $version)
                        <tr class="{{ $version->is_active ? 'bg-green-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $version->version }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $version->filename }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $version->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $version->uploader->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($version->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('terms-of-service.download', $version->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">Download</a>
                                
                                @if(!$version->is_active)
                                    <form action="{{ route('terms-of-service.activate', $version->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900">
                                            Activate
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('terms-of-service.destroy', $version->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this version?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @if($version->notes)
                            <tr class="{{ $version->is_active ? 'bg-green-50' : '' }}">
                                <td colspan="6" class="px-6 py-2 text-sm text-gray-600">
                                    <strong>Notes:</strong> {{ $version->notes }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection