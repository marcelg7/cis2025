@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Contract Templates</h1>
            <a href="{{ route('contract-templates.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Template
            </a>
        </div>
        <p class="mt-2 text-sm text-gray-600">Manage your personal templates and team templates. Templates help you quickly create contracts with pre-configured settings.</p>
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

    <!-- My Templates Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="h-5 w-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                My Templates ({{ $personalTemplates->count() }})
            </h2>
            <p class="mt-1 text-sm text-gray-500">Your personal contract templates</p>
        </div>

        @if($personalTemplates->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No personal templates</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first template</p>
                <div class="mt-6">
                    <a href="{{ route('contract-templates.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Create Template
                    </a>
                </div>
            </div>
        @else
            <div class="divide-y divide-gray-200">
                @foreach($personalTemplates as $template)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <h3 class="text-sm font-medium text-gray-900">{{ $template->name }}</h3>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        Used {{ $template->use_count }}x
                                    </span>
                                </div>
                                @if($template->description)
                                    <p class="mt-1 text-sm text-gray-500">{{ $template->description }}</p>
                                @endif
                                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                    @if($template->bellDevice)
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $template->bellDevice->name }}
                                        </span>
                                    @endif
                                    @if($template->ratePlan)
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            {{ $template->ratePlan->name }}
                                        </span>
                                    @endif
                                    @if($template->activityType)
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            {{ $template->activityType->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4 flex items-center space-x-2">
                                <a href="{{ route('contract-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                    Edit
                                </a>
                                <form action="{{ route('contract-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Team Templates Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="h-5 w-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Team Templates ({{ $teamTemplates->count() }})
            </h2>
            <p class="mt-1 text-sm text-gray-500">Templates shared with the entire team</p>
        </div>

        @if($teamTemplates->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No team templates</h3>
                <p class="mt-1 text-sm text-gray-500">Admins can create team templates for everyone to use</p>
            </div>
        @else
            <div class="divide-y divide-gray-200">
                @foreach($teamTemplates as $template)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <h3 class="text-sm font-medium text-gray-900">{{ $template->name }}</h3>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Team
                                    </span>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        Used {{ $template->use_count }}x
                                    </span>
                                </div>
                                @if($template->description)
                                    <p class="mt-1 text-sm text-gray-500">{{ $template->description }}</p>
                                @endif
                                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                    @if($template->bellDevice)
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $template->bellDevice->name }}
                                        </span>
                                    @endif
                                    @if($template->ratePlan)
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            {{ $template->ratePlan->name }}
                                        </span>
                                    @endif
                                    @if($template->activityType)
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            {{ $template->activityType->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @can('update', $template)
                                <div class="ml-4 flex items-center space-x-2">
                                    <a href="{{ route('contract-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Edit
                                    </a>
                                    <form action="{{ route('contract-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this team template?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
