@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6">
    <!-- Header with Back Button -->
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('cellular-pricing.mobile-internet') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Mobile Internet Plans
        </a>
        
        <a href="{{ route('cellular-pricing.mobile-internet.edit', $mobileInternetPlan) }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Plan
        </a>
    </div>

    <!-- Plan Header -->
    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-8">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-3">
                        @if($mobileInternetPlan->category)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $mobileInternetPlan->category }}
                            </span>
                        @endif
                        @if($mobileInternetPlan->is_current)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Current
                            </span>
                        @endif
                        @if($mobileInternetPlan->description)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Custom Description
                            </span>
                        @endif
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $mobileInternetPlan->plan_name }}</h1>
                    <p class="mt-2 text-lg text-gray-600">SOC Code: {{ $mobileInternetPlan->soc_code }}</p>
                </div>
                
                <div class="text-right">
                    <div class="text-4xl font-bold text-gray-900">${{ number_format($mobileInternetPlan->monthly_rate, 2) }}</div>
                    <p class="text-sm text-gray-500 mt-1">per month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Description (if set) -->
    @if($mobileInternetPlan->description)
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Custom Plan Description</h2>
                <p class="text-sm text-gray-500 mt-1">This description will appear on contracts for this plan</p>
            </div>
            <div class="px-6 py-4 prose prose-sm max-w-none">
                {!! $mobileInternetPlan->description !!}
            </div>
        </div>
    @endif

    <!-- Plan Details -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Plan Details</h2>
        </div>
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">SOC Code</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $mobileInternetPlan->soc_code }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Monthly Rate</dt>
                    <dd class="mt-1 text-sm text-gray-900">${{ number_format($mobileInternetPlan->monthly_rate, 2) }}</dd>
                </div>

                @if($mobileInternetPlan->category)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $mobileInternetPlan->category }}</dd>
                    </div>
                @endif

                @if($mobileInternetPlan->promo_group)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Promo Group</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $mobileInternetPlan->promo_group }}</dd>
                    </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Effective Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $mobileInternetPlan->effective_date->format('F j, Y') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($mobileInternetPlan->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection