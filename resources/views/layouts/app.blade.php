<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mobile Contracts') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('customers.index') }}" class="text-lg font-bold">Mobile Contracts</a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <a href="{{ route('customers.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ Route::is('customers.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Customers
                            </a>
                            @auth
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('devices.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ Route::is('devices.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Devices
                                    </a>
                                    <a href="{{ route('plans.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ Route::is('plans.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Plans
                                    </a>
                                    <a href="{{ route('activity-types.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ Route::is('activity-types.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Activity Types
                                    </a>
                                    <a href="{{ route('commitment-periods.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ Route::is('commitment-periods.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Commitment Periods
                                    </a>
                                    <a href="{{ route('users.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ Route::is('users.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-900 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Users
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Right Side: Search and Auth -->
					<div class="hidden sm:flex sm:items-center sm:ms-6">
						@auth
							<!-- Search Form -->
							<form action="{{ route('search') }}" method="GET" class="flex items-center mr-4">
								<input type="text" name="query" placeholder="Search..." value="{{ old('query') }}" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
								<button type="submit" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
									Search
								</button>
							</form>
							<!-- User Dropdown -->
							<div class="relative">
								<button class="flex items-center text-sm font-medium text-gray-900 hover:text-gray-700 focus:outline-none" type="button" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
									{{ auth()->user()->name }}
									<svg class="ml-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
										<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
									</svg>
								</button>
								<div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden" id="user-menu">
									<form action="{{ route('logout') }}" method="POST">
										@csrf
										<button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
											Logout
										</button>
									</form>
								</div>
							</div>
						@else
							<a href="{{ route('login') }}" class="text-sm font-medium text-gray-900 hover:text-gray-700">Login</a>
						@endauth
					</div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
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
            @yield('content')
        </main>
    </div>
</body>
</html>