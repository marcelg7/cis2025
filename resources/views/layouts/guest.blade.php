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
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/app2.css', 'resources/js/app.js'])

        <!-- Default theme colors for guest pages -->
        <style>
            :root {
                --color-primary: #4F46E5; /* Indigo-600 */
                --color-primary-hover: #4338CA; /* Indigo-700 */
                --color-secondary: #10B981; /* Green-500 */
                --color-secondary-hover: #059669; /* Green-600 */
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <img src="{{ asset('images/hayLogo.png') }}" alt="Hay Communications" class="w-32 h-auto">
                </a>
            </div>
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <footer class="mt-8 py-4 text-center text-sm text-gray-500">
                <div class="max-w-7xl mx-auto px-4">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>
            </footer>
        </div>
    </body>
</html>