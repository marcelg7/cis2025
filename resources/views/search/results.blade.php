@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-semibold text-gray-900">Search Results for "{{ $query }}"</h1>
        <div class="mt-6 space-y-8">
            <!-- Customers -->
            <div>
                <h2 class="text-lg font-medium text-gray-900">Customers</h2>
                @if ($results['customers']->isEmpty())
                    <p class="text-gray-500">No customers found.</p>
                @else
                    <ul class="mt-2 divide-y divide-gray-200 bg-white shadow rounded-lg">
                        @foreach ($results['customers'] as $customer)
                            <li class="px-6 py-4">
                                <a href="{{ route('customers.show', $customer->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $customer->display_name }} ({{ $customer->ivue_customer_number }})
                                </a>
                                <p class="text-sm text-gray-600">{{ $customer->email ?? 'No email' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Devices -->
            <div>
                <h2 class="text-lg font-medium text-gray-900">Devices</h2>
                @if ($results['devices']->isEmpty())
                    <p class="text-gray-500">No devices found.</p>
                @else
                    <ul class="mt-2 divide-y divide-gray-200 bg-white shadow rounded-lg">
                        @foreach ($results['devices'] as $device)
                            <li class="px-6 py-4">
                                <a href="{{ route('devices.edit', $device->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $device->manufacturer }} {{ $device->model }}
                                </a>
                                <p class="text-sm text-gray-600">SRP: ${{ number_format($device->srp, 2) }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Plans -->
            <div>
                <h2 class="text-lg font-medium text-gray-900">Plans</h2>
                @if ($results['plans']->isEmpty())
                    <p class="text-gray-500">No plans found.</p>
                @else
                    <ul class="mt-2 divide-y divide-gray-200 bg-white shadow rounded-lg">
                        @foreach ($results['plans'] as $plan)
                            <li class="px-6 py-4">
                                <a href="{{ route('plans.edit', $plan->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $plan->name }}
                                </a>
                                <p class="text-sm text-gray-600">${{ number_format($plan->price, 2) }} - {{ $plan->details ?? 'No details' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Activity Types -->
            <div>
                <h2 class="text-lg font-medium text-gray-900">Activity Types</h2>
                @if ($results['activity_types']->isEmpty())
                    <p class="text-gray-500">No activity types found.</p>
                @else
                    <ul class="mt-2 divide-y divide-gray-200 bg-white shadow rounded-lg">
                        @foreach ($results['activity_types'] as $activityType)
                            <li class="px-6 py-4">
                                <a href="{{ route('activity-types.edit', $activityType->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $activityType->name }}
                                </a>
                                <p class="text-sm text-gray-600">{{ $activityType->is_active ? 'Active' : 'Inactive' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Commitment Periods -->
            <div>
                <h2 class="text-lg font-medium text-gray-900">Commitment Periods</h2>
                @if ($results['commitment_periods']->isEmpty())
                    <p class="text-gray-500">No commitment periods found.</p>
                @else
                    <ul class="mt-2 divide-y divide-gray-200 bg-white shadow rounded-lg">
                        @foreach ($results['commitment_periods'] as $period)
                            <li class="px-6 py-4">
                                <a href="{{ route('commitment-periods.edit', $period->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $period->name }}
                                </a>
                                <p class="text-sm text-gray-600">{{ $period->is_active ? 'Active' : 'Inactive' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Contracts -->
            <div>
                <h2 class="text-lg font-medium text-gray-900">Contracts</h2>
                @if ($results['contracts']->isEmpty())
                    <p class="text-gray-500">No contracts found.</p>
                @else
                    <ul class="mt-2 divide-y divide-gray-200 bg-white shadow rounded-lg">
                        @foreach ($results['contracts'] as $contract)
                            <li class="px-6 py-4">
                                <a href="{{ route('contracts.view', $contract->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Contract #{{ $contract->id }} ({{ $contract->contract_date->format('Y-m-d') }})
                                </a>
                                <p class="text-sm text-gray-600">Subscriber: {{ $contract->subscriber->mobile_number }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Customers
            </a>
        </div>
    </div>
@endsection