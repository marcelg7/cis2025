@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Notification Preferences</h1>
        <p class="mt-1 text-sm text-gray-600">Choose which notifications you want to receive</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('users.notification-preferences.update') }}">
        @csrf
        @method('PATCH')

        <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-200">
            @foreach($preferences as $type => $preference)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0 mr-4">
                            <div class="flex items-center">
                                @if($type === 'contract_pending_signature')
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </div>
                                @elseif($type === 'ftp_upload_failed')
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @elseif($type === 'contract_renewal')
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-base font-medium text-gray-900">{{ $preference['label'] }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        @if($type === 'contract_pending_signature')
                                            Get notified when contracts haven't been signed for more than 24 hours
                                        @elseif($type === 'ftp_upload_failed')
                                            Get notified when contract uploads to the Vault fail
                                        @elseif($type === 'contract_renewal')
                                            Get notified about contracts approaching their end date (30/60/90 days before)
                                        @elseif($type === 'device_pricing_uploaded')
                                            Get notified when new device pricing is uploaded to the system
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0" x-data="{ enabled: {{ $preference['enabled'] ? 'true' : 'false' }} }">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       name="notifications[{{ $type }}]"
                                       class="sr-only"
                                       x-model="enabled">
                                <div class="relative w-11 h-6 rounded-full transition-colors"
                                     :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'">
                                    <div class="absolute top-[2px] left-[2px] bg-white border border-gray-300 rounded-full h-5 w-5 transition-transform duration-200"
                                         :class="enabled ? 'translate-x-5' : 'translate-x-0'"></div>
                                </div>
                                <span class="ml-3 text-sm font-medium"
                                      :class="enabled ? 'text-indigo-600' : 'text-gray-500'"
                                      x-text="enabled ? 'Enabled' : 'Disabled'">
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center justify-between">
            <a href="{{ route('users.settings.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Settings
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save Preferences
            </button>
        </div>
    </form>
</div>
@endsection
