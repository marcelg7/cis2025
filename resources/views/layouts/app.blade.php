<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mobile Contracts') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" style="background-image: url('/images/background.jpg'); background-attachment: fixed; background-size: cover;">
    <div class="min-h-screen bg-gray-100 bg-opacity-90">
	<nav class="bg-white border-b border-gray-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between h-16">
				<div class="flex">
					<!-- Logo -->
					<div class="shrink-0 flex items-center">
						<a href="{{ route('customers.index') }}" class="flex items-center">
							<img src="/images/hayLogo.png" alt="Hay Logo" class="h-8 w-auto mr-2">
							<span class="text-lg font-bold">Hay CIS</span>
						</a>
					</div>
					<!-- Navigation Links -->
					<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
						<a href="{{ route('customers.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ Route::is('customers.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
							Customers
						</a>
						
						<!-- External Links -->
						<a href="https://hay.net" target="_blank" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
							Hay Website
							<svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
							</svg>
						</a>
						
					</div>
				</div>

				<!-- Mobile Navigation Button -->
				<div class="flex items-center sm:hidden">
					<button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" aria-label="Main menu" aria-expanded="false">
						<!-- Hamburger Icon -->
						<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
						</svg>
					</button>
				</div>
				
				
				<div class="hidden sm:flex sm:items-center sm:ms-6">
					@auth
						<!-- Search Form -->
						<form action="{{ route('search') }}" method="GET" class="flex items-center mr-4">
							<input type="text" name="query" placeholder="Search..." value="{{ old('query') }}" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
							<button type="submit" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
								<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
								</svg>
							</button>
						</form>
						
					@else
						<a href="{{ route('login') }}" class="text-sm font-medium text-gray-900 hover:text-gray-700">Login</a>
					@endauth
					
@auth
					@if (auth()->user()->isAdmin())
						<!-- Settings Dropdown -->
						<div class="relative" x-data="{ open: false }" @click.away="open = false">
							<button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
								<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
								</svg>
							</button>
							
							<div
								x-show="open"
								x-transition:enter="transition ease-out duration-200"
								x-transition:enter-start="transform opacity-0 scale-95"
								x-transition:enter-end="transform opacity-100 scale-100"
								x-transition:leave="transition ease-in duration-75"
								x-transition:leave-start="transform opacity-100 scale-100"
								x-transition:leave-end="transform opacity-0 scale-95"
								class="absolute z-50 mt-3 w-48 rounded-md shadow-lg origin-top-right right-0"
								style="display: none;"
							>
								<div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
									<a href="{{ route('devices.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
										Devices
									</a>
									<a href="{{ route('plans.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
										Plans
									</a>
									<a href="{{ route('activity-types.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
										Activity Types
									</a>
									<a href="{{ route('commitment-periods.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
										Commitment Periods
									</a>
									<a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
										Users
									</a>
								</div>
							</div>
						</div>
					@endif
					<div class="hidden sm:flex sm:items-center sm:ms-6">
						<div class="relative">
							<button class="flex items-center text-sm font-medium text-gray-900 hover:text-gray-700 focus:outline-none" type="button" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
								{{ auth()->user()->name }}
								<svg class="ml-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
								</svg>
							</button>
							<div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden" id="user-menu">
								<a href="{{ route('password.change') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
									Change Password
								</a>
								<a href="{{ route('users.settings.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
									Settings
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
	
	<!-- Mobile Navigation Menu -->
	<div id="mobile-menu" class="hidden sm:hidden">
		<div class="pt-2 pb-3 space-y-1">
			<a href="{{ route('customers.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ Route::is('customers.*') ? 'border-indigo-400 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium">
				Customers
			</a>
			
			<!-- External Links -->
			<a href="https://hay.net" target="_blank" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 text-base font-medium flex items-center">
				Hay Website
				<svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
				</svg>
			</a>
			
			@auth
				@if (auth()->user()->isAdmin())
					<!-- Admin Links -->
					<div class="settings-dropdown">
						<button class="w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 text-base font-medium flex items-center justify-between settings-toggle">
							<div class="flex items-center">
								<svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
								</svg>
								Settings
							</div>
							<svg class="w-4 h-4 settings-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
							</svg>
						</button>
						<div class="pl-4 pb-2 hidden settings-menu">
							<a href="{{ route('devices.index') }}" class="block py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50">
								Devices
							</a>
							<a href="{{ route('plans.index') }}" class="block py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50">
								Plans
							</a>
							<a href="{{ route('activity-types.index') }}" class="block py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50">
								Activity Types
							</a>
							<a href="{{ route('commitment-periods.index') }}" class="block py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50">
								Commitment Periods
							</a>
							<a href="{{ route('users.index') }}" class="block py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50">
								Users
							</a>
						</div>
					</div>
				@endif
			@endauth
		</div>
		
		<!-- Mobile Search -->
		<div class="pt-2 pb-3 border-t border-gray-200">
			<form action="{{ route('search') }}" method="GET" class="px-4">
				<div class="flex rounded-md shadow-sm">
					<input type="text" name="query" placeholder="Search..." class="block w-full px-3 py-2 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
					<button type="submit" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
						</svg>
					</button>
				</div>
			</form>
		</div>
		
		<!-- Mobile User Menu -->
		@auth
			<div class="pt-4 pb-3 border-t border-gray-200">
				<div class="flex items-center px-4">
					<div>
						<div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
						<div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
					</div>
				</div>
				<div class="mt-3 space-y-1">
					<a href="{{ route('password.change') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
						Change Password
					</a>
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
							Sign out
						</button>
					</form>
				</div>
			</div>
		@else
			<div class="py-3 border-t border-gray-200">
				<a href="{{ route('login') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
					Login
				</a>
			</div>
		@endauth
	</div>

        <!-- Page Content -->
		<main class="px-2 sm:px-0"> <!-- Added px-2 for small screens, removed on larger screens -->

            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-auto max-w-7xl sm:px-6 lg:px-8 mt-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-auto max-w-7xl sm:px-6 lg:px-8 mt-4">
                    {{ session('error') }}
                </div>
            @endif
			@if (session('info'))
				<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mx-auto max-w-7xl sm:px-6 lg:px-8 mt-4">
					{{ session('info') }}
				</div>
			@endif			
            @yield('content')
        </main>
    </div>
</body>
</html>