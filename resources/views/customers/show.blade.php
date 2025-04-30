@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->display_name }} ({{ $customer->ivue_customer_number }})</h1>
        <div class="mt-4">
            <p class="text-gray-600"><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
            <p class="text-gray-600"><strong>Address:</strong> {{ $customer->address }}, {{ $customer->city }}, {{ $customer->state }} {{ $customer->zip_code }}</p>
        </div>

        <h2 class="mt-6 text-xl font-semibold text-gray-900">IVUE Accounts</h2>
        <ul class="mt-4 space-y-4">
            @foreach ($customer->ivueAccounts as $ivue)
                <li class="border-l-4 border-gray-300 pl-4">
                    {{ $ivue->ivue_account }} ({{ $ivue->status }})
                    @if ($ivue->mobilityAccount)
                        <div class="ml-4">
                            <strong>Mobility:</strong> {{ $ivue->mobilityAccount->mobility_account }} ({{ $ivue->mobilityAccount->status }})
                            <ul class="mt-2 space-y-2">
                                @foreach ($ivue->mobilityAccount->subscribers as $subscriber)
                                    <li class="border-l-2 border-gray-200 pl-4">
                                        {{ $subscriber->mobile_number }} ({{ $subscriber->first_name }} {{ $subscriber->last_name }}) - {{ $subscriber->status }}
                                        <h3 class="mt-2 text-lg font-medium text-gray-900">Contracts</h3>
                                        <ul class="mt-2 space-y-1">
                                            @if ($subscriber->contracts->isEmpty())
                                                <li class="text-gray-500">No contracts yet</li>
                                            @else
                                                @foreach ($subscriber->contracts as $contract)
                                                    <li>
                                                        Contract #{{ $contract->id }} ({{ $contract->contract_date->format('Y-m-d') }})
                                                        <a href="{{ route('contracts.download', $contract->id) }}" class="text-indigo-600 hover:text-indigo-900">Download PDF</a> |
                                                        <a href="{{ route('contracts.view', $contract->id) }}" class="text-indigo-600 hover:text-indigo-900">View HTML</a> |
                                                        <form action="{{ route('contracts.email', $contract->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">Email</button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            @endif
                                            <li>
                                                <a href="{{ route('contracts.create', $subscriber->id) }}" class="text-indigo-600 hover:text-indigo-900">Create Contract for {{ $subscriber->mobile_number }}</a>
                                            </li>
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-6 flex space-x-4">
            <a href="{{ route('customers.add-mobility', $customer->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Mobility Account
            </a>
            <a href="{{ route('customers.add-subscriber', $customer->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Subscriber
            </a>
            <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back
            </a>
        </div>
    </div>
@endsection