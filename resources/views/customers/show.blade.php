@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2"> <!-- Added px-2 -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->display_name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Customer Number: {{ $customer->ivue_customer_number }}</p>
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
                        <h3 class="text-lg font-medium text-gray-900">Contact Information</h3>
                        <p class="mt-1 text-sm text-gray-600"><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600"><strong>Address:</strong> {{ $customer->address }}, {{ $customer->city }}, {{ $customer->state }} {{ $customer->zip_code }}</p>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="mt-6 text-xl font-semibold text-gray-900">IVUE Accounts</h2>
        <div class="mt-4 space-y-6">
            @foreach ($customer->ivueAccounts as $ivue)
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">{{ $ivue->ivue_account }} <span class="ml-2 px-2 py-1 text-xs rounded-full {{ $ivue->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $ivue->status }}</span></h3>
                    </div>
                    
                    @if ($ivue->mobilityAccount)
                        <div class="border-t border-gray-200 px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <h4 class="text-md font-medium text-gray-900">Mobility: {{ $ivue->mobilityAccount->mobility_account }}</h4>
                                <span class="px-2 py-1 text-xs rounded-full {{ $ivue->mobilityAccount->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $ivue->mobilityAccount->status }}</span>
                            </div>
                            
                            @if ($ivue->mobilityAccount->subscribers->isNotEmpty())
                                <div class="mt-4 space-y-4">
                                    @foreach ($ivue->mobilityAccount->subscribers as $subscriber)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <h5 class="text-md font-medium text-gray-900">{{ $subscriber->mobile_number }}</h5>
                                                <span class="px-2 py-1 text-xs rounded-full {{ $subscriber->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $subscriber->status }}</span>
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
                                                        <x-contract-card :contract="$contract" />
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
                        </div>
                    @endif
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