@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">User Preferences</h1>
            <p class="text-indigo-100 text-sm mt-1">Customize your experience</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mx-6 mt-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('users.settings.update') }}" class="p-6 space-y-8">
            @csrf
            @method('PATCH')

            <!-- Session Settings -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Session Settings
                </h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label for="session_lifetime" class="block text-sm font-medium text-gray-700 mb-2">
                        Session Timeout
                    </label>
                    <select name="session_lifetime" id="session_lifetime" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="30" @selected(old('session_lifetime', $user->session_lifetime ?? 120) == 30)>30 minutes</option>
                        <option value="60" @selected(old('session_lifetime', $user->session_lifetime ?? 120) == 60)>1 hour</option>
                        <option value="120" @selected(old('session_lifetime', $user->session_lifetime ?? 120) == 120)>2 hours</option>
                        <option value="240" @selected(old('session_lifetime', $user->session_lifetime ?? 120) == 240)>4 hours</option>
                        <option value="480" @selected(old('session_lifetime', $user->session_lifetime ?? 120) == 480)>8 hours</option>
                    </select>
                    <p class="mt-2 text-xs text-gray-500">How long before you're automatically logged out due to inactivity</p>
                </div>
            </div>

            <!-- Theme Settings -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Color Theme
                </h2>
                <div class="space-y-3">
                    @php
                        $themes = [
                            'default' => [
                                'name' => 'Default (Indigo)',
                                'description' => 'Standard blue-indigo color scheme',
                                'preview' => ['#4f46e5', '#ffffff', '#6366f1']
                            ],
                            'dark' => [
                                'name' => 'Dark Mode',
                                'description' => 'Easy on the eyes in low light',
                                'preview' => ['#1f2937', '#f3f4f6', '#4b5563']
                            ],
                            'high-contrast' => [
                                'name' => 'High Contrast',
                                'description' => 'WCAG AAA compliant, maximum readability',
                                'preview' => ['#000000', '#ffffff', '#ffff00']
                            ],
                            'warm' => [
                                'name' => 'Warm',
                                'description' => 'Amber and orange tones',
                                'preview' => ['#f59e0b', '#ffffff', '#fbbf24']
                            ],
                            'cool' => [
                                'name' => 'Cool',
                                'description' => 'Blue and teal tones',
                                'preview' => ['#0891b2', '#ffffff', '#06b6d4']
                            ],
                            'deuteranopia' => [
                                'name' => 'Deuteranopia Friendly',
                                'description' => 'Optimized for red-green colorblindness',
                                'preview' => ['#0369a1', '#fef3c7', '#f59e0b']
                            ],
                            'protanopia' => [
                                'name' => 'Protanopia Friendly',
                                'description' => 'Alternative for red-green colorblindness',
                                'preview' => ['#7c3aed', '#fef3c7', '#f59e0b']
                            ],
                        ];
                        $currentTheme = old('theme', $user->theme ?? 'default');
                    @endphp

                    @foreach($themes as $themeKey => $themeData)
                        <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors @if($currentTheme === $themeKey) border-indigo-600 bg-indigo-50 @else border-gray-200 @endif">
                            <input type="radio" 
                                   name="theme" 
                                   value="{{ $themeKey }}" 
                                   @checked($currentTheme === $themeKey)
                                   class="mt-1 h-5 w-5 text-indigo-600 border-gray-300 focus:ring-indigo-500 flex-shrink-0">
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-semibold text-gray-900">{{ $themeData['name'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">{{ $themeData['description'] }}</p>
                                <div class="flex space-x-2">
                                    @foreach($themeData['preview'] as $color)
                                        <div class="h-6 flex-1 rounded shadow-sm" style="background-color: {{ $color }}; border: 1px solid rgba(0,0,0,0.1);"></div>
                                    @endforeach
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Developer Options -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    Developer Options
                </h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" 
                               name="show_development_info" 
                               value="1"
                               @checked(old('show_development_info', $user->show_development_info ?? false))
                               class="mt-0.5 h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 flex-shrink-0">
                        <div class="ml-3">
                            <span class="font-medium text-gray-700 block">Show Development Information</span>
                            <p class="text-sm text-gray-500 mt-1">
                                Display detailed calculation breakdowns and technical information on contract views
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('customers.index') }}" 
                   class="text-sm text-gray-600 hover:text-gray-800 transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Home
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Preferences
                </button>
            </div>
        </form>
    </div>

    <!-- Help Card -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <svg class="h-5 w-5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">About Accessibility Themes</h3>
                <p class="mt-1 text-sm text-blue-700">
                    Colorblind-friendly themes have been carefully designed to ensure all users can distinguish between different interface elements.
                    High contrast mode meets WCAG AAA standards for maximum readability.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection