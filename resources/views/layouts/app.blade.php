<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Theme CSS Variables -->
    <style>
        {!! $themeCss ?? '' !!}
        
        /* Apply theme colors to common elements */
        body {
            background-color: var(--color-background);
            color: var(--color-text);
        }
        
        .bg-primary {
            background-color: var(--color-primary) !important;
        }
        
        .bg-primary:hover {
            background-color: var(--color-primary-hover) !important;
        }
        
        .text-primary {
            color: var(--color-primary) !important;
        }
        
        .border-primary {
            border-color: var(--color-primary) !important;
        }
        
        .bg-surface {
            background-color: var(--color-surface);
        }
        
        /* Buttons with theme colors */
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--color-primary-hover);
        }
        
        .btn-secondary {
            background-color: var(--color-secondary);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: var(--color-secondary-hover);
        }
        
        /* Links */
        a.text-indigo-600, a.text-blue-600 {
            color: var(--color-primary) !important;
        }
        
        a.text-indigo-600:hover, a.text-blue-600:hover {
            color: var(--color-primary-hover) !important;
        }
        
        /* Indigo/Blue button overrides */
        .bg-indigo-600 {
            background-color: var(--color-primary) !important;
        }
        
        .bg-indigo-700, .hover\:bg-indigo-700:hover {
            background-color: var(--color-primary-hover) !important;
        }
        
        .bg-blue-600 {
            background-color: var(--color-primary) !important;
        }
        
        .bg-blue-700, .hover\:bg-blue-700:hover {
            background-color: var(--color-primary-hover) !important;
        }
        
        /* Focus rings */
        .focus\:ring-indigo-500:focus {
            --tw-ring-color: var(--color-primary) !important;
        }
        
        .focus\:border-indigo-500:focus {
            border-color: var(--color-primary) !important;
        }
        
        /* Text colors */
        .text-indigo-600 {
            color: var(--color-primary) !important;
        }
        
        /* Border colors */
        .border-indigo-600 {
            border-color: var(--color-primary) !important;
        }
        
        /* Background light */
        .bg-indigo-50 {
            background-color: var(--color-primary-light) !important;
        }
        
        /* Success/Warning/Danger stay consistent or use theme */
        .bg-green-600 {
            background-color: var(--color-success) !important;
        }
        
        .bg-yellow-600, .bg-orange-600 {
            background-color: var(--color-warning) !important;
        }
        
        .bg-red-600 {
            background-color: var(--color-danger) !important;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/css/app2.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
    <style>
        /* Ensure Pricing Dropdown width is respected */
        .pricing-dropdown {
            min-width: 230px !important;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-white">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Main Navigation -->
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('customers.index') }}" class="flex items-center">
                                <img src="/images/hayLogo.png" alt="Hay Logo" class="h-8 w-auto mr-2">
                                <span class="text-lg font-bold">Hay CIS</span>
                            </a>
                        </div>
                        
                        <!-- Desktop Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                                Customers
                            </x-nav-link>
                            <x-nav-link :href="route('contracts.index')" :active="request()->routeIs('contracts.*')">
                                Contracts
                            </x-nav-link>
                        
                            <!-- Pricing Dropdown -->
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open"
                                        class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out h-16 {{ request()->routeIs('bell-pricing.*') || request()->routeIs('cellular-pricing.*') ? 'border-indigo-400 text-gray-900 focus:border-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300' }}">
                                    Pricing
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 z-50 mt-0 w-80 rounded-md shadow-lg pricing-dropdown"
                                     style="display: none;">
                                    <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                        <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Bell Device Pricing</div>
                                        <a href="{{ route('bell-pricing.index') }}"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap {{ request()->routeIs('bell-pricing.index') ? 'bg-gray-50' : '' }}">
                                            Browse Devices
                                        </a>
                                        @can('upload-device-pricing')
                                            <a href="{{ route('bell-pricing.upload') }}"
                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap {{ request()->routeIs('bell-pricing.upload') ? 'bg-gray-50' : '' }}">
                                                Upload Device Pricing
                                            </a>
                                        @endcan
                                        <a href="{{ route('bell-pricing.compare') }}"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap {{ request()->routeIs('bell-pricing.compare') ? 'bg-gray-50' : '' }}">
                                            Compare Devices
                                        </a>
                                    
                                        <div class="border-t border-gray-200 my-1"></div>
                                    
                                        <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Cellular Plans</div>
                                        <a href="{{ route('cellular-pricing.rate-plans') }}"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap {{ request()->routeIs('cellular-pricing.rate-plans*') ? 'bg-gray-50' : '' }}">
                                            Rate Plans
                                        </a>
                                        <a href="{{ route('cellular-pricing.mobile-internet') }}"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap {{ request()->routeIs('cellular-pricing.mobile-internet') ? 'bg-gray-50' : '' }}">
                                            Mobile Internet
                                        </a>
                                        <a href="{{ route('cellular-pricing.add-ons') }}"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap {{ request()->routeIs('cellular-pricing.add-ons') ? 'bg-gray-50' : '' }}">
                                            Plan Add-Ons
                                        </a>
                                        @can('upload-plan-pricing')
                                            <a href="{{ route('cellular-pricing.upload') }}"
                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap {{ request()->routeIs('cellular-pricing.upload') ? 'bg-gray-50' : '' }}">
                                                Upload Plan Pricing
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        
                            <x-nav-link href="https://hay.net" target="_blank" :active="false">
                                Hay Website
                                <x-icon-open />
                            </x-nav-link>
                        </div>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="flex items-center sm:hidden">
                        <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" aria-label="Main menu" aria-expanded="false">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Desktop Right Side -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <!-- Search Form -->
                        <form action="{{ route('search') }}" method="GET" class="flex items-center mr-4">
                            <input type="text" name="query" placeholder="Search..." value="{{ old('query') }}" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <button type="submit" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </form>
                        
                        @auth
                            <!-- Settings/Gear Dropdown -->
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute z-50 mt-3 w-48 rounded-md shadow-lg origin-top-right right-0"
                                     style="display: none;">
                                    <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                        @hasrole('admin')
                                            <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Administration</div>
                                            <a href="{{ route('admin.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Test Data Management
                                            </a>
                                            <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Settings
                                            </a>
                                            <a href="{{ route('admin.backups.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Database Backups
                                            </a>
                                            <a href="{{ route('roles.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Manage Roles
                                            </a>
                                            <a href="{{ route('permissions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Manage Permissions
                                            </a>
                                            <div class="border-t border-gray-200 my-1"></div>
                                            
                                            <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Configuration</div>
                                            <a href="{{ route('activity-types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Activity Types
                                            </a>
                                            <a href="{{ route('commitment-periods.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Commitment Periods
                                            </a>
                                            <a href="{{ route('terms-of-service.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Terms of Service
                                            </a>
                                            <div class="border-t border-gray-200 my-1"></div>
                                        @endhasrole
                                        
                                        <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Users & Logs</div>
                                        @can('manage-users')
                                            <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Users
                                            </a>
                                        @endcan
                                        <a href="{{ route('logs.my') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            My Activity Logs
                                        </a>
                                        @can('view_all_logs')
                                            <a href="{{ route('logs.all') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                All Activity Logs
                                            </a>
                                        @endcan
                                        <div class="border-t border-gray-200 my-1"></div>
                                        <a href="{{ route('changelog') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Changelog
                                        </a>
                                        <a href="{{ route('readme') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Documentation
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Menu Dropdown -->
                            <div class="relative ml-3" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-900 hover:text-gray-700 focus:outline-none" type="button">
                                    {{ auth()->user()->name }}
                                    <svg class="ml-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10"
                                     style="display: none;">
                                    <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1">
                                        <a href="{{ route('users.settings.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            User Settings
                                        </a>
                                        <a href="{{ route('analytics') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Analytics
                                        </a>
                                        <a href="{{ route('password.custom_change') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Change Password
                                        </a>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </nav>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <!-- Main Navigation -->
                    <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                        Customers
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('contracts.index')" :active="request()->routeIs('contracts.*')">
                        Contracts
                    </x-responsive-nav-link>
                
                    <!-- Bell Device Pricing -->
                    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Bell Device Pricing</div>
                    <x-responsive-nav-link :href="route('bell-pricing.index')" :active="request()->routeIs('bell-pricing.index')">
                        Browse Devices
                    </x-responsive-nav-link>
                    @can('upload-device-pricing')
                        <x-responsive-nav-link :href="route('bell-pricing.upload')" :active="request()->routeIs('bell-pricing.upload')">
                            Upload Device Pricing
                        </x-responsive-nav-link>
                    @endcan
                    <x-responsive-nav-link :href="route('bell-pricing.compare')" :active="request()->routeIs('bell-pricing.compare')">
                        Compare Devices
                    </x-responsive-nav-link>
                
                    <!-- Cellular Plans -->
                    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase mt-2">Cellular Plans</div>
                    <x-responsive-nav-link :href="route('cellular-pricing.rate-plans')" :active="request()->routeIs('cellular-pricing.rate-plans*')">
                        Rate Plans
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('cellular-pricing.mobile-internet')" :active="request()->routeIs('cellular-pricing.mobile-internet')">
                        Mobile Internet
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('cellular-pricing.add-ons')" :active="request()->routeIs('cellular-pricing.add-ons')">
                        Plan Add-Ons
                    </x-responsive-nav-link>
                    @can('upload-plan-pricing')
                        <x-responsive-nav-link :href="route('cellular-pricing.upload')" :active="request()->routeIs('cellular-pricing.upload')">
                            Upload Plan Pricing
                        </x-responsive-nav-link>
                    @endcan
                
                    <x-responsive-nav-link href="https://hay.net" target="_blank" :active="false" class="flex items-center">
                        Hay Website
                        <x-icon-open />
                    </x-responsive-nav-link>
                </div>
                
                <!-- Search Form -->
                <div class="pt-2 pb-3 border-t border-gray-200">
                    <form action="{{ route('search') }}" method="GET" class="px-4">
                        <div class="flex rounded-md shadow-sm">
                            <input type="text" name="query" placeholder="Search..." value="{{ old('query') }}" class="block w-full px-3 py-2 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <button type="submit" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
                
                @auth
                    <div class="pt-4 pb-3 border-t border-gray-200">
                        <!-- User Info -->
                        <div class="flex items-center px-4">
                            <div>
                                <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                                <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                        
                        <div class="mt-3 space-y-1">
                            @hasrole('admin')
                                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Administration</div>
                                <x-responsive-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">
                                    Test Data Management
                                </x-responsive-nav-link>
                                <x-responsive-nav-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')">
                                    Settings
                                </x-responsive-nav-link>
                                <x-responsive-nav-link :href="route('admin.backups.index')" :active="request()->routeIs('admin.backups.*')">
                                    Database Backups
                                </x-responsive-nav-link>
                                <x-responsive-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">
                                    Manage Roles
                                </x-responsive-nav-link>
                                <x-responsive-nav-link :href="route('permissions.index')" :active="request()->routeIs('permissions.*')">
                                    Manage Permissions
                                </x-responsive-nav-link>
                                <div class="border-t border-gray-200 my-1 mx-4"></div>
                                
                                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Configuration</div>
                                <x-responsive-nav-link :href="route('activity-types.index')" :active="request()->routeIs('activity-types.*')">
                                    Activity Types
                                </x-responsive-nav-link>
                                <x-responsive-nav-link :href="route('commitment-periods.index')" :active="request()->routeIs('commitment-periods.*')">
                                    Commitment Periods
                                </x-responsive-nav-link>
                                <x-responsive-nav-link :href="route('terms-of-service.index')" :active="request()->routeIs('terms-of-service.*')">
                                    Terms of Service
                                </x-responsive-nav-link>
                                <div class="border-t border-gray-200 my-1 mx-4"></div>
                            @endhasrole
                            
                            <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Users & Logs</div>
                            @can('manage-users')
                                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                                    Users
                                </x-responsive-nav-link>
                            @endcan
                            <x-responsive-nav-link :href="route('logs.my')" :active="request()->routeIs('logs.my')">
                                My Activity Logs
                            </x-responsive-nav-link>
                            @can('view_all_logs')
                                <x-responsive-nav-link :href="route('logs.all')" :active="request()->routeIs('logs.all')">
                                    All Activity Logs
                                </x-responsive-nav-link>
                            @endcan
                            <div class="border-t border-gray-200 my-1 mx-4"></div>
                            
                            <x-responsive-nav-link :href="route('changelog')" :active="request()->routeIs('changelog')">
                                Changelog
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('readme')" :active="request()->routeIs('readme')">
                                Documentation
                            </x-responsive-nav-link>

                            <div class="border-t border-gray-200 my-1 mx-4"></div>
                            
                            <x-responsive-nav-link :href="route('users.settings.edit')" :active="request()->routeIs('users.settings.edit')">
                                User Settings
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('analytics')" :active="request()->routeIs('analytics')">
                                Analytics
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('password.custom_change')" :active="request()->routeIs('password.custom_change')">
                                Change Password
                            </x-responsive-nav-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-responsive-nav-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-responsive-nav-link>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="py-3 border-t border-gray-200">
                        <x-responsive-nav-link href="{{ route('login') }}" :active="request()->routeIs('login')">
                            Login
                        </x-responsive-nav-link>
                    </div>
                @endauth
            </div>
        </nav>
        
        <main class="px-2 sm:px-0">
            @yield('content')
        </main>

        <!-- Footer with Version -->
        <footer class="mt-8 py-4 text-center text-sm text-gray-500 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                <span class="mx-2">|</span>
                Version: {{ app_version() }}
            </div>
        </footer>
    </div>
</body>
</html>