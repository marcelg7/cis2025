@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-semibold text-gray-900">Contract #{{ $contract->id }}</h1>
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-5 sm:p-6">
                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Contract Details</h2>
                        <div class="mt-4 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
                            <p><strong>Start Date:</strong> {{ $contract->start_date->format('Y-m-d') }}</p>
                            <p><strong>End Date:</strong> {{ $contract->end_date->format('Y-m-d') }}</p>
                            <p><strong>Activity Type:</strong> {{ $contract->activityType->name }}</p>
                            <p><strong>Contract Date:</strong> {{ $contract->contract_date->format('Y-m-d') }}</p>
                            <p><strong>Location:</strong> {{ ucfirst($contract->location) }}</p>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Device Details</h2>
                        <div class="mt-4 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
                            <p><strong>Device:</strong> {{ $contract->device ? $contract->device->manufacturer . ' ' . $contract->device->model : 'None' }}</p>
                            <p><strong>SIM #:</strong> {{ $contract->sim_number ?? 'N/A' }}</p>
                            <p><strong>IMEI #:</strong> {{ $contract->imei_number ?? 'N/A' }}</p>
                            <p><strong>Amount Paid:</strong> ${{ number_format($contract->amount_paid_for_device, 2) }}</p>
                            <p><strong>Agreement Credit:</strong> ${{ number_format($contract->agreement_credit_amount, 2) }}</p>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Plan Section</h2>
                        <div class="mt-4 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
                            <p><strong>Plan:</strong> {{ $contract->plan->name }}</p>
                            <p><strong>Commitment Period:</strong> {{ $contract->commitmentPeriod->name }}</p>
                            <p><strong>First Bill Date:</strong> {{ $contract->first_bill_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Plan Add-ons</h2>
                        @if ($contract->addOns->isEmpty())
                            <p class="text-gray-500">No add-ons</p>
                        @else
                            <ul class="list-disc list-inside mt-2">
                                @foreach ($contract->addOns as $addOn)
                                    <li>{{ $addOn->name }} ({{ $addOn->code }}): ${{ number_format($addOn->cost, 2) }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">One Time Fees</h2>
                        @if ($contract->oneTimeFees->isEmpty())
                            <p class="text-gray-500">No fees</p>
                        @else
                            <ul class="list-disc list-inside mt-2">
                                @foreach ($contract->oneTimeFees as $fee)
                                    <li>{{ $fee->name }}: ${{ number_format($fee->cost, 2) }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Cancellation Policy</h2>
                        <p class="text-gray-600">{{ $contract->commitmentPeriod->cancellation_policy ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('customers.show', $contract->subscriber->mobilityAccount->ivueAccount->customer_id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Customer
            </a>
        </div>
    </div>
@endsection