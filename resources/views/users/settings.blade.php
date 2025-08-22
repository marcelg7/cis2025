@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2">
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
                
                <!-- Session Settings -->
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
                </div>
                
                <!-- Component Style Settings -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Component Style Settings</h2>
                    <div x-data="{ showStyles: false }" x-init="console.log('x-data initialized', $data)">
                        <div class="mb-4">
                            <button type="button" x-on:click="showStyles = !showStyles" aria-expanded="false" aria-controls="styleContent" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-200 focus:outline-none">
                                <span x-text="showStyles ? 'Hide Style Settings' : 'Show Style Settings'"></span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" :class="showStyles ? 'rotate-180' : ''">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                        <div x-show="showStyles" id="styleContent" class="p-4 bg-gray-50">

                            @foreach([
                                'primary-button' => ['label' => 'Primary Button', 'default_background' => '#4f46e5', 'default_hover' => '#4338ca', 'default_text' => '#ffffff', 'default_text_size' => 'xs'],
                                'secondary-button' => ['label' => 'Secondary Button', 'default_background' => '#ffffff', 'default_hover' => '#f9fafb', 'default_text' => '#374151', 'default_text_size' => 'xs'],
                                'primary-link' => ['label' => 'Primary Link', 'default_background' => '#4f46e5', 'default_hover' => '#4338ca', 'default_text' => '#ffffff', 'default_text_size' => 'xs'],
                                'secondary-link' => ['label' => 'Secondary Link', 'default_background' => '#ffffff', 'default_hover' => '#f9fafb', 'default_text' => '#374151', 'default_text_size' => 'xs'],
                                'warning-button' => ['label' => 'Warning Button', 'default_background' => '#fef3c7', 'default_hover' => '#fde68a', 'default_text' => '#b45309', 'default_text_size' => 'xs'],
                                'warning-link' => ['label' => 'Warning Link', 'default_background' => '#fef3c7', 'default_hover' => '#fde68a', 'default_text' => '#b45309', 'default_text_size' => 'xs'],
                                'danger-button' => ['label' => 'Danger Button', 'default_background' => '#fee2e2', 'default_hover' => '#dc2626', 'default_text' => '#b91c1c', 'default_text_size' => 'xs'],
                                'danger-button-submit' => ['label' => 'Danger Button Submit', 'default_background' => '#fee2e2', 'default_hover' => '#dc2626', 'default_text' => '#b91c1c', 'default_text_size' => 'xs'],
                                'danger-link' => ['label' => 'Danger Link', 'default_background' => '#fee2e2', 'default_hover' => '#991b1b', 'default_text' => '#b91c1c', 'default_text_size' => 'xs'],
                                'info-button' => ['label' => 'Info Button', 'default_background' => '#dbeafe', 'default_hover' => '#bfdbfe', 'default_text' => '#1e40af', 'default_text_size' => 'xs'],
                                'info-link' => ['label' => 'Info Link', 'default_background' => '#dbeafe', 'default_hover' => '#bfdbfe', 'default_text' => '#1e40af', 'default_text_size' => 'xs'],
                            ] as $key => $config)
                                <div class="mb-6">
                                    <h3 class="text-md font-medium text-gray-800 mb-2">{{ $config['label'] }}</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Background Color -->
                                        <div>
                                            <label for="{{ $key }}_background" class="block text-sm font-medium text-gray-700">Background Color</label>
                                            <input type="color" name="component_styles[{{ $key }}][background]" id="{{ $key }}_background" value="{{ isset($user->component_styles[$key]['background']) ? $user->component_styles[$key]['background'] : $config['default_background'] }}" class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm">
                                            @error("component_styles.{$key}.background")
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <!-- Hover Color -->
                                        <div>
                                            <label for="{{ $key }}_hover" class="block text-sm font-medium text-gray-700">Hover Color</label>
                                            <input type="color" name="component_styles[{{ $key }}][hover]" id="{{ $key }}_hover" value="{{ isset($user->component_styles[$key]['hover']) ? $user->component_styles[$key]['hover'] : $config['default_hover'] }}" class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm">
                                            @error("component_styles.{$key}.hover")
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <!-- Text Color -->
                                        <div>
                                            <label for="{{ $key }}_text" class="block text-sm font-medium text-gray-700">Text Color</label>
                                            <input type="color" name="component_styles[{{ $key }}][text]" id="{{ $key }}_text" value="{{ isset($user->component_styles[$key]['text']) ? $user->component_styles[$key]['text'] : $config['default_text'] }}" class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm">
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
                </div>
                
                <div class="flex space-x-4">
                    <x-primary-button type="submit" name="action" value="save">
                        Save Settings
                    </x-primary-button>
                    <x-secondary-button type="button" onclick="window.history.back()">
                        Cancel
                    </x-secondary-button>
                    <x-danger-button type="submit" name="action" value="reset" onclick="return confirm('Are you sure you want to reset all styles to default?')">
                        Reset to Defaults
                    </x-danger-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection