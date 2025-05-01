@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2"> <!-- Added px-2 -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">User Settings</h1>
            
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('users.settings.update') }}">
                @csrf
                @method('PATCH')
                
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Session Settings</h2>
                    
                    <div class="mb-4">
                        <label for="session_lifetime" class="block text-sm font-medium text-gray-700">Session Timeout (minutes)</label>
                        <select name="session_lifetime" id="session_lifetime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="30" {{ $user->session_lifetime == 30 ? 'selected' : '' }}>30 minutes</option>
                            <option value="60" {{ $user->session_lifetime == 60 ? 'selected' : '' }}>1 hour</option>
                            <option value="120" {{ $user->session_lifetime == 120 ? 'selected' : '' }}>2 hours</option>
                            <option value="240" {{ $user->session_lifetime == 240 ? 'selected' : '' }}>4 hours</option>
                            <option value="480" {{ $user->session_lifetime == 480 ? 'selected' : '' }}>8 hours</option>

                        </select>
                        <p class="mt-1 text-sm text-gray-500">How long until your session expires due to inactivity.</p>
                        @error('session_lifetime')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- You can add more settings here in the future -->
                </div>
                
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection