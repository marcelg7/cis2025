@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container" x-data="{ refreshing: false }">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->display_name }}</h1>
                    <div class="mt-1 text-sm text-gray-600 flex items-center">
                        <span>Customer Number: {{ $customer->ivue_customer_number }}</span>
                        <form method="POST" action="{{ route('customers.fetch') }}" class="inline-flex ml-2" x-ref="refreshForm">
                            @csrf
                            <input type="hidden" name="customer_number" value="{{ $customer->ivue_customer_number }}">
                            <button
                                type="submit"
                                @click.prevent="refreshing = true; $refs.refreshForm.submit();"
                                :disabled="refreshing"
                                class="text-gray-400 hover:text-indigo-600 focus:outline-none disabled:opacity-50"
                                title="Refresh customer data from NISC"
                            >
                                <svg
                                    :class="refreshing ? 'animate-spin' : ''"
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="flex space-x-4">
                    @php
                        // Check if there are any IVUE accounts without mobility accounts
                        $hasAvailableIvueAccounts = $customer->ivueAccounts->filter(function($ivueAccount) {
                            return $ivueAccount->mobilityAccount === null;
                        })->isNotEmpty();
                        // Check if there are any mobility accounts (for Add Subscriber button)
                        $hasMobilityAccounts = $customer->ivueAccounts->filter(function($ivueAccount) {
                            return $ivueAccount->mobilityAccount !== null;
                        })->isNotEmpty();
                    @endphp
                    @if ($hasAvailableIvueAccounts)
                        <x-primary-link href="{{ route('customers.add-mobility', $customer->id) }}">
                            Add Mobility Account
                        </x-primary-link>
                    @else
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider opacity-50 cursor-not-allowed"
                            title="No available IVUE accounts without mobility accounts"
                            disabled
                        >
                            Add Mobility Account
                        </button>
                    @endif
                    @if ($hasMobilityAccounts)
                        <x-primary-link href="{{ route('customers.add-subscriber', $customer->id) }}">
                            Add Subscriber
                        </x-primary-link>
                    @else
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider opacity-50 cursor-not-allowed"
                            title="No mobility accounts available to add a subscriber to"
                            disabled
                        >
                            Add Subscriber
                        </button>
                    @endif
                </div>
            </div>
            <div class="border-t border-gray-200">
                <div class="px-4 py-4 sm:px-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-medium text-gray-900">Contact Information</h3>
                            <a href="{{ route('customers.edit', $customer->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                Edit Contract Email
                            </a>
                        </div>
                        <p class="mt-1 text-sm text-gray-600"><strong>Email (IVUE):</strong> {{ $customer->email ?? 'N/A' }}</p>
                        @if($customer->contract_email)
                            <p class="text-sm text-gray-600">
                                <strong>Contract Signing Email:</strong>
                                <span class="text-green-700 font-medium">{{ $customer->contract_email }}</span>
                                <span class="text-xs text-gray-500 ml-1">(Used for contracts)</span>
                            </p>
                        @else
                            <p class="text-sm text-gray-500 italic">
                                No contract email set - using IVUE email for contracts
                            </p>
                        @endif
                        <p class="text-sm text-gray-600"><strong>Address:</strong> {{ $customer->address }}, {{ $customer->city }}, {{ $customer->state }} {{ $customer->zip_code }}</p>

                        @if($customer->contact_methods && count($customer->contact_methods) > 0)
                            <div class="mt-3">
                                <h4 class="text-sm font-semibold text-gray-700">Contact Methods:</h4>
                                <ul class="mt-1 space-y-1">
                                    @foreach($customer->contact_methods as $method)
                                        @php
                                            $contactInfo = $method['contactInfo'] ?? '';
                                            $contactMethod = $method['contactMethod'] ?? '';
                                            $formatted = \App\Helpers\PhoneHelper::formatDisplay($contactInfo);
                                        @endphp
                                        <li class="text-sm text-gray-600">
                                            <strong>{{ $contactMethod }}:</strong> {{ $formatted }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($customer->additional_contacts && count($customer->additional_contacts) > 0)
                            <div class="mt-3">
                                <h4 class="text-sm font-semibold text-gray-700">Additional Contacts:</h4>
                                <ul class="mt-1 space-y-2">
                                    @foreach($customer->additional_contacts as $contact)
                                        <li class="text-sm text-gray-600">
                                            <strong>{{ $contact['contactName'] ?? 'N/A' }}</strong>
                                            @if(isset($contact['contactType']) && $contact['contactType'])
                                                <span class="text-gray-500">({{ $contact['contactType'] }})</span>
                                            @endif
                                            @if(isset($contact['contactMethods']) && is_array($contact['contactMethods']) && count($contact['contactMethods']) > 0)
                                                <ul class="ml-4 mt-1 space-y-1">
                                                    @foreach($contact['contactMethods'] as $method)
                                                        @php
                                                            $contactInfo = $method['contactInfo'] ?? '';
                                                            $contactMethod = $method['contactMethod'] ?? '';
                                                            $formatted = \App\Helpers\PhoneHelper::formatDisplay($contactInfo);
                                                        @endphp
                                                        <li class="text-xs text-gray-500">
                                                            {{ $contactMethod }}: {{ $formatted }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <h2 class="mt-6 text-xl font-semibold text-gray-900">IVUE Accounts</h2>
        <div class="mt-4 space-y-6">
            @foreach ($customer->ivueAccounts as $ivue)
                <div x-data="{ expanded: true }" class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <button @click="expanded = !expanded" class="focus:outline-none">
                                <x-icon-chevron-down x-show="expanded" class="w-5 h-5 text-gray-600" />
                                <x-icon-chevron-right x-show="!expanded" class="w-5 h-5 text-gray-600" />
                            </button>
                            <h3 class="text-lg font-medium text-gray-900">{{ $ivue->ivue_account }}</h3>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $ivue->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($ivue->status) }}</span>
                    </div>
                    <div x-show="expanded" x-transition class="border-t border-gray-200 px-4 py-4 sm:px-6">
                        @if ($ivue->mobilityAccount)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-md font-medium text-gray-900">Mobility: {{ $ivue->mobilityAccount->mobility_account }}</h4>
                                    <a href="{{ route('customers.edit-mobility', [$customer->id, $ivue->mobilityAccount->id]) }}" class="text-gray-400 hover:text-indigo-600 focus:outline-none" title="Edit mobility account">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full {{ $ivue->mobilityAccount->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($ivue->mobilityAccount->status) }}</span>
                            </div>
                            @if ($ivue->mobilityAccount->subscribers->isNotEmpty())
                                <div class="mt-4 space-y-4">
                                    @foreach ($ivue->mobilityAccount->subscribers as $subscriber)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <h5 class="text-md font-medium text-gray-900">{{ $subscriber->mobile_number }}</h5>
                                                <span class="px-2 py-1 text-xs rounded-full {{ $subscriber->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($subscriber->status) }}</span>
                                            </div>
                                            @if ($subscriber->first_name || $subscriber->last_name)
                                                <p class="text-sm text-gray-500">{{ $subscriber->first_name }} {{ $subscriber->last_name }}</p>
                                            @endif
                                            <h3 class="mt-3 text-lg font-medium text-gray-900">Contracts</h3>
                                            @if ($subscriber->contracts->isEmpty())
                                                <p class="text-gray-500">No contracts yet</p>
                                            @else
                                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    @foreach ($subscriber->contracts as $contract)
                                                        <x-contract-card :contract="$contract" :canDelete="in_array($contract->status, $deletableStatuses)" />
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="mt-3">
                                                <x-primary-link href="{{ route('contracts.create', $subscriber->id) }}">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    Create Contract
                                                </x-primary-link>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-gray-500">No subscribers yet</p>
                            @endif
                        @else
                            <p class="mt-2 text-sm text-gray-500">No Mobility Account</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            <x-secondary-link href="{{ route('customers.index') }}">
                Back
            </x-secondary-link>
        </div>
    </div>
@endsection