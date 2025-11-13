@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Notifications</h1>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
            <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
        </div>
    @else
        <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-200">
            @foreach($notifications as $notification)
                <div class="p-4 hover:bg-gray-50 transition-colors {{ $notification->read_at ? 'opacity-75' : 'bg-blue-50' }}">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            @if($notification->data['type'] === 'contract_pending_signature')
                                <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </div>
                            @elseif($notification->data['type'] === 'ftp_upload_failed')
                                <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @elseif($notification->data['type'] === 'contract_renewal')
                                <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $notification->data['title'] }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $notification->data['message'] }}
                            </p>
                            @php
                                // Check if contract still exists (for contract-related notifications)
                                $contractExists = true;
                                if (isset($notification->data['contract_id'])) {
                                    $contractExists = \App\Models\Contract::find($notification->data['contract_id']) !== null;
                                }
                            @endphp
                            @if(!$contractExists)
                                <p class="text-xs text-red-600 mt-1">
                                    ⚠️ This contract has been deleted
                                </p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            @if($notification->read_at === null)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800">
                                        Mark as read
                                    </button>
                                </form>
                            @endif
                            @if(isset($notification->data['action_url']) && $contractExists)
                                <a href="{{ route('notifications.read', $notification->id) }}"
                                   class="text-xs text-indigo-600 hover:text-indigo-800">
                                    {{ $notification->data['action_text'] ?? 'View' }}
                                </a>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 bg-white p-4 rounded-lg shadow-sm">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
