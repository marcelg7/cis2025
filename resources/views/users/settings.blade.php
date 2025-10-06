@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2">
        <h1 class="text-2xl font-semibold text-gray-900">User Settings</h1>
        <form method="POST" action="{{ route('users.settings.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('PATCH')
            <!-- Session Settings -->
            <div>
                <h2 class="text-lg font-medium text-gray-900">Session Settings</h2>
                <div class="mt-4">
                    <label for="session_lifetime" class="block text-sm font-medium text-gray-700">Session Lifetime (minutes)</label>
                    <select name="session_lifetime" id="session_lifetime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="30" {{ old('session_lifetime', $user->session_lifetime ?? 120) == 30 ? 'selected' : '' }}>30</option>
                        <option value="60" {{ old('session_lifetime', $user->session_lifetime ?? 120) == 60 ? 'selected' : '' }}>60</option>
                        <option value="120" {{ old('session_lifetime', $user->session_lifetime ?? 120) == 120 ? 'selected' : '' }}>120</option>
                        <option value="240" {{ old('session_lifetime', $user->session_lifetime ?? 120) == 240 ? 'selected' : '' }}>240</option>
                        <option value="480" {{ old('session_lifetime', $user->session_lifetime ?? 120) == 480 ? 'selected' : '' }}>480</option>
                    </select>
                    @error('session_lifetime')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <!-- Component Style Settings -->
            <div>
                <h2 class="text-lg font-medium text-gray-900">Component Style Settings</h2>
                <div class="mt-4">
                    @foreach([
                        'primary-button' => 'Primary Button',
                        'secondary-button' => 'Secondary Button',
                        'primary-link' => 'Primary Link',
                        'secondary-link' => 'Secondary Link',
                        'warning-button' => 'Warning Button',
                        'warning-link' => 'Warning Link',
                        'danger-button' => 'Danger Button',
                        'danger-button-submit' => 'Danger Button Submit',
                        'danger-link' => 'Danger Link',
                        'info-button' => 'Info Button',
                        'info-link' => 'Info Link',
                    ] as $key => $label)
                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-800">{{ $label }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
                                <!-- Background Color -->
                                <div>
                                    <label for="{{ $key }}_background" class="block text-sm font-medium text-gray-700">Background Color</label>
                                    <input type="color" name="component_styles[{{ $key }}][background]" id="{{ $key }}_background" value="{{ $user->component_styles[$key]['background'] ?? '#4f46e5' }}" class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm">
                                    @error("component_styles.{$key}.background")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Hover Color -->
                                <div>
                                    <label for="{{ $key }}_hover" class="block text-sm font-medium text-gray-700">Hover Color</label>
                                    <input type="color" name="component_styles[{{ $key }}][hover]" id="{{ $key }}_hover" value="{{ $user->component_styles[$key]['hover'] ?? '#4338ca' }}" class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm">
                                    @error("component_styles.{$key}.hover")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Text Color -->
                                <div>
                                    <label for="{{ $key }}_text" class="block text-sm font-medium text-gray-700">Text Color</label>
                                    <input type="color" name="component_styles[{{ $key }}][text]" id="{{ $key }}_text" value="{{ $user->component_styles[$key]['text'] ?? '#ffffff' }}" class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm">
                                    @error("component_styles.{$key}.text")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Text Size -->
                                <div>
                                    <label for="{{ $key }}_text_size" class="block text-sm font-medium text-gray-700">Text Size</label>
                                    <select name="component_styles[{{ $key }}][text_size]" id="{{ $key }}_text_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="xs" {{ ($user->component_styles[$key]['text_size'] ?? 'xs') === 'xs' ? 'selected' : '' }}>Extra Small</option>
                                        <option value="sm" {{ ($user->component_styles[$key]['text_size'] ?? 'xs') === 'sm' ? 'selected' : '' }}>Small</option>
                                        <option value="base" {{ ($user->component_styles[$key]['text_size'] ?? 'xs') === 'base' ? 'selected' : '' }}>Medium</option>
                                        <option value="lg" {{ ($user->component_styles[$key]['text_size'] ?? 'xs') === 'lg' ? 'selected' : '' }}>Large</option>
                                        <option value="xl" {{ ($user->component_styles[$key]['text_size'] ?? 'xs') === 'xl' ? 'selected' : '' }}>Extra Large</option>
                                    </select>
                                    @error("component_styles.{$key}.text_size")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end">
                <x-primary-button type="submit">
                    Save Settings
                </x-primary-button>
                <a href="{{ route('customers.index') }}" class="ml-4 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back
                </a>
                <x-danger-button type="submit" name="action" value="reset" onclick="return confirm('Are you sure you want to reset all settings to default?')">
                    Reset to Defaults
                </x-danger-button>
            </div>
        </form>
    </div>
@endsection