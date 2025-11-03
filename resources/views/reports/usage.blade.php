@extends('layouts.app')

@section('content')
<div class="py-12">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Page Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900">CSR Usage Report</h1>
            <p class="mt-2 text-sm text-gray-600">Track login activity, session duration, and user engagement</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <form method="GET" action="{{ route('reports.usage') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date"
                       name="start_date"
                       id="start_date"
                       value="{{ $startDate }}"
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date"
                       name="end_date"
                       id="end_date"
                       value="{{ $endDate }}"
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Filter by User</label>
                <select name="user_id"
                        id="user_id"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Currently Active Sessions -->
    @if($activeSessions->count() > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    Currently Active Sessions ({{ $activeSessions->count() }})
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($activeSessions as $session)
                        <div class="border border-green-200 bg-green-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-900">{{ $session->user->name }}</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-2 h-2 bg-green-600 rounded-full mr-1 animate-pulse"></span>
                                    Active
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Logged in: {{ $session->login_at->format('M j, Y g:i A') }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Duration: {{ $session->login_at->diffForHumans(null, true) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- User Statistics -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Usage Statistics by User</h2>
            <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</p>
        </div>

        @if($userStats->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Sessions
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Avg Session
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Login
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($userStats as $stat)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $stat['user']->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $stat['user']->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $stat['total_sessions'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                    {{ $stat['total_duration_formatted'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $stat['avg_duration_formatted'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($stat['last_login'])
                                        <div>{{ $stat['last_login']->format('M j, Y') }}</div>
                                        <div class="text-xs">{{ $stat['last_login']->format('g:i A') }}</div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center text-gray-500">
                No usage data found for the selected date range.
            </div>
        @endif
    </div>

    <!-- Daily Activity Chart -->
    @if($dailyActivity->count() > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Daily Activity</h2>
            <div class="space-y-3">
                @php
                    $maxLogins = $dailyActivity->max('login_count');
                @endphp
                @foreach($dailyActivity as $day)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">
                                {{ \Carbon\Carbon::parse($day->date)->format('M j, Y') }}
                            </span>
                            <span class="text-sm text-gray-600">
                                {{ $day->login_count }} logins
                                @if($day->total_duration)
                                    <span class="text-gray-400">•</span>
                                    {{ floor($day->total_duration / 3600) }}h {{ floor(($day->total_duration % 3600) / 60) }}m total
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full"
                                 style="width: {{ ($day->login_count / $maxLogins) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Sessions -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Sessions</h2>
        </div>

        @if($recentSessions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Login Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Logout Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Duration
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                IP Address
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentSessions as $session)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $session->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $session->login_at->format('M j, g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($session->logout_at)
                                        {{ $session->logout_at->format('M j, g:i A') }}
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $session->formatted_duration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $session->ip_address }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                {{ $recentSessions->appends(request()->query())->links() }}
            </div>
        @else
            <div class="p-12 text-center text-gray-500">
                No sessions found for the selected criteria.
            </div>
        @endif
    </div>
</div>
</div>
@endsection
