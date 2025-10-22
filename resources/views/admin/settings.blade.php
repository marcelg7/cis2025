@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Admin Settings</h1>
            <p class="text-blue-100 text-sm mt-1">Manage system configuration and preferences</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mx-6 mt-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('admin.settings') }}" class="p-6 space-y-6">
            @csrf

            <!-- Log Prune Days -->
            <div>
                <label for="log_prune_days" class="block text-sm font-semibold text-gray-700 mb-2">
                    Log Retention Period
                </label>
                <div class="relative">
                    <input 
                        type="number" 
                        id="log_prune_days"
                        name="log_prune_days" 
                        value="{{ old('log_prune_days', $logPruneDays) }}" 
                        min="30" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('log_prune_days') border-red-500 @enderror"
                        placeholder="90"
                    >
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 text-sm">days</span>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Logs older than this will be automatically deleted (minimum 30 days)
                </p>
                @error('log_prune_days')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Supervisor Email -->
            <div>
                <label for="cellular_supervisor_email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Cellular Supervisor Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input 
                        type="email" 
                        id="cellular_supervisor_email"
                        name="cellular_supervisor_email" 
                        value="{{ old('cellular_supervisor_email', $supervisorEmail) }}" 
                        required
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('cellular_supervisor_email') border-red-500 @enderror"
                        placeholder="supervisor@example.com"
                    >
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Notifications for cellular account issues will be sent to this address
                </p>
                @error('cellular_supervisor_email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

			<!-- Default Connection Fee -->
			<div>
				<label for="default_connection_fee" class="block text-sm font-semibold text-gray-700 mb-2">
					Default Connection Fee
				</label>
				<div class="relative">
					<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
						<span class="text-gray-500 text-sm">$</span>
					</div>
					<input 
						type="number" 
						id="default_connection_fee"
						name="default_connection_fee" 
						value="{{ old('default_connection_fee', $connectionFee) }}" 
						min="0"
						max="500"
						step="0.01"
						required
						class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('default_connection_fee') border-red-500 @enderror"
						placeholder="80.00"
					>
				</div>
				<p class="mt-2 text-sm text-gray-500">
					<svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
						<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
					</svg>
					This fee will be automatically added to new contracts
				</p>
				@error('default_connection_fee')
					<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
				@enderror
			</div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ url('/customers') }}" class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                    ← Back to Home Page
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Additional Info Card -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Need Help?</h3>
                <p class="mt-1 text-sm text-blue-700">
                    Changes to these settings take effect immediately. Contact IT support if you need assistance.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection