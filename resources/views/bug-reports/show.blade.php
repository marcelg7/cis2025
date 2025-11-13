@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('bug-reports.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Feedback
            </a>
            @can('delete', $bugReport)
                <form action="{{ route('bug-reports.destroy', $bugReport) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this bug report?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </button>
                </form>
            @endcan
        </div>
        <h1 class="text-2xl font-semibold text-gray-900">{{ $bugReport->title }}</h1>
        <div class="mt-1 flex items-center space-x-2">
            <p class="text-sm text-gray-600">Feedback #{{ $bugReport->id }}</p>
            <span class="text-gray-400">•</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                {{ \App\Models\BugReport::FEEDBACK_TYPES[$bugReport->feedback_type ?? 'bug'] }}
            </span>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (Left Column) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Bug Details -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Description</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $bugReport->description }}</p>
                </div>

                @if($bugReport->url)
                    <div class="px-6 pb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Page URL</h3>
                        <a href="{{ $bugReport->url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm break-all">
                            {{ $bugReport->url }}
                            <svg class="inline h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                @endif

                @if($bugReport->browser_info)
                    <div class="px-6 pb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Browser Information</h3>
                        <p class="text-sm text-gray-600 font-mono bg-gray-50 p-2 rounded">{{ $bugReport->browser_info }}</p>
                    </div>
                @endif

                @if($bugReport->screenshot)
                    <div class="px-6 pb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Screenshot</h3>
                        <div class="mt-2">
                            <a href="{{ Storage::url($bugReport->screenshot) }}" target="_blank">
                                <img src="{{ Storage::url($bugReport->screenshot) }}" alt="Bug screenshot" class="rounded-lg border border-gray-200 max-w-full h-auto hover:opacity-90 transition-opacity">
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Comments Section -->
            @can('update', $bugReport)
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Comments</h2>
                    </div>

                    <!-- Existing Comments -->
                    <div class="p-6 space-y-4">
                        @forelse($bugReport->comments as $comment)
                            <div class="border-l-4 border-indigo-200 bg-gray-50 p-4 rounded">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $comment->created_at->format('M d, Y \a\t g:i A') }}</span>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-700">
                                    {!! $comment->comment !!}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 italic text-sm">No comments yet. Add one below.</p>
                        @endforelse
                    </div>

                    <!-- Add New Comment Form -->
                    <div class="p-6 border-t border-gray-200 bg-gray-50">
                        <form action="{{ route('bug-reports.comments.store', $bugReport) }}" method="POST">
                            @csrf
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Add Comment</label>
                            <textarea
                                id="comment"
                                name="comment"
                                rows="4"
                                class="tinymce w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                placeholder="Add notes about investigation, fixes, updates, etc."
                            ></textarea>
                            @error('comment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                                    Add Comment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar (Right Column) -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Feedback Information</h3>

                <div class="space-y-4">
                    <!-- Status -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($bugReport->status === 'open') bg-red-100 text-red-800
                            @elseif($bugReport->status === 'in_progress') bg-blue-100 text-blue-800
                            @elseif($bugReport->status === 'resolved') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $bugReport->statusInfo['label'] }}
                        </span>
                    </div>

                    <!-- Severity -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Severity</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($bugReport->severity === 'critical') bg-red-100 text-red-800
                            @elseif($bugReport->severity === 'high') bg-orange-100 text-orange-800
                            @elseif($bugReport->severity === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $bugReport->severityInfo['label'] }}
                        </span>
                    </div>

                    <!-- Category -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Category</p>
                        <p class="text-sm text-gray-900">{{ \App\Models\BugReport::CATEGORIES[$bugReport->category] ?? 'Other' }}</p>
                    </div>

                    <!-- Reporter -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Reported By</p>
                        <p class="text-sm text-gray-900">{{ $bugReport->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $bugReport->user->email }}</p>
                    </div>

                    <!-- Assigned To -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Assigned To</p>
                        @if($bugReport->assignedTo)
                            <p class="text-sm text-gray-900">{{ $bugReport->assignedTo->name }}</p>
                        @else
                            <p class="text-sm text-gray-500 italic">Unassigned</p>
                        @endif
                    </div>

                    <!-- Dates -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Reported</p>
                        <p class="text-sm text-gray-900">{{ $bugReport->created_at->format('M d, Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $bugReport->created_at->format('h:i A') }}</p>
                    </div>

                    @if($bugReport->resolved_at)
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Resolved</p>
                            <p class="text-sm text-gray-900">{{ $bugReport->resolved_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $bugReport->resolved_at->format('h:i A') }}</p>
                        </div>
                    @endif
                </div>

                @if($bugReport->slack_thread_ts && $bugReport->slack_channel_id)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="https://slack.com/app_redirect?channel={{ $bugReport->slack_channel_id }}&message_ts={{ $bugReport->slack_thread_ts }}"
                           target="_blank"
                           class="inline-flex items-center justify-center w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>
                            </svg>
                            View in Slack
                            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Update Form (Admin Only) -->
            @can('update', $bugReport)
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Update Feedback</h3>
                    </div>
                    <form action="{{ route('bug-reports.update', $bugReport) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PATCH')

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="open" {{ $bugReport->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $bugReport->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $bugReport->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $bugReport->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <!-- Severity -->
                        <div>
                            <label for="severity" class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                            <select id="severity" name="severity" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="low" {{ $bugReport->severity === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $bugReport->severity === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $bugReport->severity === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ $bugReport->severity === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>

                        <!-- Assigned To -->
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                            <select id="assigned_to" name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Unassigned</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}" {{ $bugReport->assigned_to == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                            Update Feedback
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
