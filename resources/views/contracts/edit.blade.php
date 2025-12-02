@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Contract for {{ $contract->subscriber->mobile_number }}</h1>
        <div class="text-sm text-gray-600">
            <span class="font-semibold">Status:</span>
            <span class="px-2 py-1 rounded {{ $contract->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : ($contract->status === 'signed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                {{ ucfirst($contract->status) }}
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('contracts.update', $contract->id) }}" class="mt-6 space-y-8" id="contract-form">
        @csrf
        @method('PUT')
        
        <!-- Hidden fields to store Bell pricing data -->
        <input type="hidden" name="bell_device_id" id="hidden_bell_device_id" value="{{ old('bell_device_id', $contract->bell_device_id) }}">
        <input type="hidden" name="bell_pricing_type" id="hidden_bell_pricing_type" value="{{ old('bell_pricing_type', $contract->bell_pricing_type) }}">
        <input type="hidden" name="bell_tier" id="hidden_bell_tier" value="{{ old('bell_tier', $contract->bell_tier) }}">
        <input type="hidden" name="bell_retail_price" id="hidden_bell_retail_price" value="{{ old('bell_retail_price', $contract->bell_retail_price) }}">
        <input type="hidden" name="bell_monthly_device_cost" id="hidden_bell_monthly_device_cost" value="{{ old('bell_monthly_device_cost', $contract->bell_monthly_device_cost) }}">
        <input type="hidden" name="bell_plan_cost" id="hidden_bell_plan_cost" value="{{ old('bell_plan_cost', $contract->bell_plan_cost) }}">
        <input type="hidden" name="bell_dro_amount" id="hidden_bell_dro_amount" value="{{ old('bell_dro_amount', $contract->bell_dro_amount) }}">
        <input type="hidden" name="bell_plan_plus_device" id="hidden_bell_plan_plus_device" value="{{ old('bell_plan_plus_device', $contract->bell_plan_plus_device) }}">
        
        <div>
            <h2 class="text-lg font-medium text-gray-900">Contract Details</h2>
            <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <div class="mt-1 flex gap-2">
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <button type="button" onclick="document.getElementById('start_date').value = new Date().toISOString().split('T')[0]" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">Today</button>
                    </div>
                    @error('start_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <div class="mt-1 flex gap-2">
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $contract->end_date->format('Y-m-d')) }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <button type="button" onclick="document.getElementById('end_date').value = new Date().toISOString().split('T')[0]" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">Today</button>
                    </div>
                    @error('end_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="activity_type_id" class="block text-sm font-medium text-gray-700">Activity Type</label>
                    <select name="activity_type_id" id="activity_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @foreach ($activityTypes as $activityType)
                            <option value="{{ $activityType->id }}" {{ old('activity_type_id', $contract->activity_type_id) == $activityType->id ? 'selected' : '' }}>{{ $activityType->name }}</option>
                        @endforeach
                    </select>
                    @error('activity_type_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="contract_date" class="block text-sm font-medium text-gray-700">Contract Date</label>
                    <div class="mt-1 flex gap-2">
                        <input type="date" name="contract_date" id="contract_date" value="{{ old('contract_date', $contract->contract_date->format('Y-m-d')) }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <button type="button" onclick="document.getElementById('contract_date').value = new Date().toISOString().split('T')[0]" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">Today</button>
                    </div>
                    @error('contract_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="location_id" class="block text-sm font-medium text-gray-700">Location</label>
                    <select name="location_id" id="location_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="">Select a location</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id', $contract->location_id) == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @if($contract->subscriber && $contract->subscriber->mobilityAccount && $contract->subscriber->mobilityAccount->ivueAccount && $contract->subscriber->mobilityAccount->ivueAccount->customer)
                    @php
                        $customer = $contract->subscriber->mobilityAccount->ivueAccount->customer;
                        $phoneNumbers = collect($customer->contact_methods ?? [])
                            ->filter(function($method) {
                                return stripos($method['contactMethod'], 'phone') !== false ||
                                       stripos($method['contactMethod'], 'cellular') !== false ||
                                       stripos($method['contactMethod'], 'mobile') !== false;
                            })
                            ->map(function($method) {
                                $info = $method['contactInfo'] ?? '';
                                return \App\Helpers\PhoneHelper::formatDisplay($info);
                            })
                            ->values();
                    @endphp
                    @if($phoneNumbers->count() > 0)
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700">Customer Contact Number</label>
                            <select name="customer_phone" id="customer_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @foreach($phoneNumbers as $phone)
                                    <option value="{{ $phone }}" {{ old('customer_phone', $contract->customer_phone) == $phone ? 'selected' : '' }}>{{ $phone }}</option>
                                @endforeach
                            </select>
                            @error('customer_phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                @endif
            </div>
        </div>
        
        <!-- Cellular Plans Section -->
        @include('contracts.partials._cellular_pricing_fields')
        
        <div id="device-details-section">
            <h2 class="text-lg font-medium text-gray-900">Device Details</h2>
            
            <!-- Show current Bell device if exists -->
            @if($contract->bell_device_id)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4 mb-4" id="current-bell-device-display">
                    <h3 class="text-sm font-medium text-blue-900 mb-2">Current Bell Device</h3>
                    <div class="text-sm text-blue-800">
                        <p><strong>Device:</strong> {{ $contract->bellDevice->name ?? 'Unknown' }}</p>
						<p><strong>Pricing Type:</strong> {{ $contract->bell_pricing_type === 'dro' ? 'DRO' : ucfirst($contract->bell_pricing_type ?? 'N/A') }}</p>
                        <p><strong>Tier:</strong> {{ $contract->bell_tier ?? 'N/A' }}</p>
                        <p><strong>Retail Price:</strong> ${{ number_format($contract->bell_retail_price ?? 0, 2) }}</p>
                        <p><strong>Monthly Device Cost:</strong> ${{ number_format($contract->bell_monthly_device_cost ?? 0, 2) }}</p>
                        @if($contract->bell_dro_amount)
                            <p><strong>DRO Amount:</strong> ${{ number_format($contract->bell_dro_amount, 2) }}</p>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Bell Device Selection - Will be hidden for BYOD -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-4 mb-6" id="bell-device-selection-section">
                <h3 class="text-md font-medium text-gray-900 mb-4">Update Bell Device Selection</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Device Selection -->
                    <div>
                        <label for="bell_device_id" class="block text-sm font-medium text-gray-700">Bell Device</label>
                        <select name="bell_device_select"
                                id="bell_device_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Select a device</option>
                            @foreach($bellDevices as $device)
                                <option value="{{ $device->id }}"
                                        data-name="{{ $device->name }}"
                                        data-has-smartpay="{{ $device->has_smartpay ? 'true' : 'false' }}"
                                        data-has-dro="{{ $device->has_dro ? 'true' : 'false' }}"
                                        data-available-tiers="{{ isset($deviceTiers[$device->id]) ? implode(',', $deviceTiers[$device->id]) : '' }}"
                                        {{ old('bell_device_id', $contract->bell_device_id) == $device->id ? 'selected' : '' }}>
                                    {{ $device->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Pricing Type -->
                    <div>
                        <label for="pricing_type" class="block text-sm font-medium text-gray-700">Pricing Type</label>
                        <select name="pricing_type_select"
                                id="pricing_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="smartpay" {{ old('bell_pricing_type', $contract->bell_pricing_type) === 'smartpay' ? 'selected' : '' }}>SmartPay</option>
                            <option value="dro" {{ old('bell_pricing_type', $contract->bell_pricing_type) === 'dro' ? 'selected' : '' }}>DRO (Device Return Option)</option>
                        </select>
                        @error('bell_pricing_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Tier Selection -->
                    <div>
                        <label for="tier" class="block text-sm font-medium text-gray-700">
                            Tier
                            <span class="text-xs text-gray-500" id="tier-auto-label" style="display: none;">(Auto-set from plan)</span>
                        </label>
                        <select name="tier_select"
                                id="tier"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($tiers as $tier)
                                <option value="{{ $tier }}" {{ old('bell_tier', $contract->bell_tier) === $tier ? 'selected' : '' }}>{{ $tier }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
				<!-- Pricing Details Display -->
				<div id="pricing-details" class="mt-4 p-4 bg-white border border-gray-300 rounded-md hidden">
					<h4 class="text-sm font-medium text-gray-900 mb-2">Pricing Details</h4>
					<div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
						<div>
							<span class="text-gray-600">Retail Price:</span>
							<span class="font-medium text-gray-900" id="display-retail-price">-</span>
						</div>
						<div>
							<span class="text-gray-600">Agreement Credit:</span>
							<span class="font-medium text-green-600" id="display-agreement-credit">-</span>
						</div>
						<div>
							<span class="text-gray-600">Monthly Device:</span>
							<span class="font-medium text-gray-900" id="display-monthly-device">-</span>
						</div>
						<div>
							<span class="text-gray-600">Plan + Device:</span>
							<span class="font-medium text-gray-900" id="display-plan-device">-</span>
						</div>
						<div id="dro-amount-display" class="hidden">
							<span class="text-gray-600">DRO Amount:</span>
							<span class="font-medium text-orange-600" id="display-dro-amount">-</span>
						</div>
					</div>
				</div>
                <button type="button"
                        id="load-pricing-btn"
                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    Load Pricing
                </button>
                
                <button type="button"
                        id="apply-pricing-btn"
                        class="mt-4 ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 hidden">
                    Apply to Contract
                </button>
            </div>
            
            <!-- BYOD Notice - Only shown when BYOD plan is selected -->
            <div id="byod-notice" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4 mb-4 hidden">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">BYOD Plan Selected</h3>
                        <p class="mt-2 text-sm text-blue-700">
                            Device details are not required for Bring Your Own Device (BYOD) plans. The customer is using their own device.
                        </p>
                    </div>
                </div>
            </div>
            
			<div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4" id="agreement-credit-section">
				<div>
					<label for="agreement_credit_amount" class="block text-sm font-medium text-gray-700">Agreement Credit Amount ($)</label>
					<input type="number" name="agreement_credit_amount" id="agreement_credit_amount" step="0.01" value="{{ old('agreement_credit_amount', $contract->agreement_credit_amount) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input" required>
					@error('agreement_credit_amount')
						<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
					@enderror
				</div>
				<div>
					<label for="imei" class="block text-sm font-medium text-gray-700">IMEI Number</label>
					<input type="text" name="imei" id="imei" value="{{ old('imei', $contract->imei) }}" maxlength="20" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter 15-digit IMEI">
					@error('imei')
						<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
					@enderror
				</div>
			</div>
        </div>
        
        <div>
            <h2 class="text-lg font-medium text-gray-900">Hay Financing</h2>
            <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                <div>
                    <label for="required_upfront_payment" class="block text-sm font-medium text-gray-700">Required Up-front Payment ($)</label>
                    <input type="number" name="required_upfront_payment" id="required_upfront_payment" step="0.01" value="{{ old('required_upfront_payment', $contract->required_upfront_payment) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input" required>
                    @error('required_upfront_payment')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="optional_down_payment" class="block text-sm font-medium text-gray-700">Optional Up-front Payment ($)</label>
                    <input type="number" name="optional_down_payment" id="optional_down_payment" step="0.01" value="{{ old('optional_down_payment', $contract->optional_down_payment) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
                    @error('optional_down_payment')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="deferred_payment_amount" class="block text-sm font-medium text-gray-700">Deferred Payment Amount ($)</label>
                    <input type="number" name="deferred_payment_amount" id="deferred_payment_amount" step="0.01" value="{{ old('deferred_payment_amount', $contract->deferred_payment_amount) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
                    @error('deferred_payment_amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <div>
            <h2 class="text-lg font-medium text-gray-900">Plan Section</h2>
            <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                <div>
                    <label for="commitment_period_id" class="block text-sm font-medium text-gray-700">Commitment Period</label>
                    <select name="commitment_period_id" id="commitment_period_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @foreach ($commitmentPeriods as $period)
                            <option value="{{ $period->id }}" {{ old('commitment_period_id', $contract->commitment_period_id) == $period->id ? 'selected' : '' }}>{{ $period->name }}</option>
                        @endforeach
                    </select>
                    @error('commitment_period_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="first_bill_date" class="block text-sm font-medium text-gray-700">First Bill Date</label>
                    <div class="mt-1 flex gap-2">
                        <input type="date" name="first_bill_date" id="first_bill_date" value="{{ old('first_bill_date', $contract->first_bill_date->format('Y-m-d')) }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <button type="button" onclick="document.getElementById('first_bill_date').value = new Date().toISOString().split('T')[0]" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">Today</button>
                    </div>
                    @error('first_bill_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-900">
                    <strong>Note:</strong> Plan costs are automatically included when you select and apply cellular plans above.
                </p>
            </div>
        </div>
        
		<div>
			<h2 class="text-lg font-medium text-gray-900">Plan Add-ons</h2>
			<div id="add-ons" class="mt-4 space-y-4">
				@foreach($contract->addOns as $index => $addOn)
					<div class="add-on grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
						<div class="sm:col-span-2">
							<label class="block text-sm font-medium text-gray-700">Name</label>
							<input type="text"
								   name="add_ons[{{ $index }}][name]"
								   value="{{ old("add_ons.{$index}.name", $addOn->name) }}"
								   class="addon-name-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
								   list="addon-suggestions"
								   autocomplete="off">
							<input type="hidden"
								   name="add_ons[{{ $index }}][code]"
								   value="{{ old("add_ons.{$index}.code", $addOn->code) }}"
								   class="addon-code-input">
							@error("add_ons.{$index}.name")
								<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
							@enderror
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700">Cost ($)</label>
							<input type="number"
								   name="add_ons[{{ $index }}][cost]"
								   step="0.01"
								   value="{{ old("add_ons.{$index}.cost", $addOn->cost) }}"
								   class="addon-cost-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
							@error("add_ons.{$index}.cost")
								<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
							@enderror
						</div>
						<div>
							<button type="button" onclick="removeAddOn(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
								Remove
							</button>
						</div>
					</div>
				@endforeach
			</div>
			<button type="button" onclick="addAddOn()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
				+ Add Add-on
			</button>
			
			<datalist id="addon-suggestions">
				@foreach($planAddOns as $addon)
					<option value="{{ $addon->add_on_name }}" data-code="{{ $addon->soc_code }}" data-cost="{{ $addon->monthly_rate }}">
				@endforeach
			</datalist>
		</div>       
        
        <div>
            <h2 class="text-lg font-medium text-gray-900">One Time Fees</h2>
            <div id="one-time-fees" class="mt-4 space-y-4">
                @foreach($contract->oneTimeFees as $index => $fee)
                    <div class="one-time-fee grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="one_time_fees[{{ $index }}][name]" value="{{ old("one_time_fees.{$index}.name", $fee->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error("one_time_fees.{$index}.name")
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
                            <input type="number" name="one_time_fees[{{ $index }}][cost]" step="0.01" value="{{ old("one_time_fees.{$index}.cost", $fee->cost) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
                            @error("one_time_fees.{$index}.cost")
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <button type="button" onclick="removeFee(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Remove
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addFee()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                + Add Fee
            </button>
        </div>
        
        <!-- Dynamic Total Calculator -->
		<!-- Dynamic Total Calculator -->
		<div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-4">
			<h2 class="text-lg font-medium text-gray-900 mb-4">Estimated Monthly Cost</h2>
			<div class="text-2xl font-bold text-indigo-600" id="total-cost">$0.00</div>
			<p class="text-sm text-gray-500 mt-2">This is a real-time estimate of your monthly service cost (device payment + plan + add-ons). One-time fees and upfront payments are not included in this monthly estimate.</p>
		</div>
        
        <div class="flex justify-end">
            <a href="{{ route('contracts.view', $contract->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Contract
            </button>
        </div>
    </form>
</div>

<script>
const DEFAULT_CONNECTION_FEE = {{ $defaultConnectionFee ?? 80 }};

let addOnCount = {{ $contract->addOns->count() }};
let feeCount = {{ $contract->oneTimeFees->count() }};
let currentPricingData = null;

const addonData = @json($planAddOns->map(function($addon) {
    return [
        'name' => $addon->add_on_name,
        'code' => $addon->soc_code,
        'cost' => $addon->monthly_rate
    ];
}));

function addAddOn() {
    const div = document.createElement('div');
    div.className = 'add-on grid grid-cols-1 sm:grid-cols-3 gap-4 items-end';
    div.innerHTML = `
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text"
                   name="add_ons[${addOnCount}][name]"
                   class="addon-name-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                   list="addon-suggestions"
                   autocomplete="off">
            <input type="hidden"
                   name="add_ons[${addOnCount}][code]"
                   value="ADDON"
                   class="addon-code-input">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
            <input type="number"
                   name="add_ons[${addOnCount}][cost]"
                   step="0.01"
                   class="addon-cost-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
        </div>
        <div>
            <button type="button" onclick="removeAddOn(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Remove
            </button>
        </div>
    `;
    document.getElementById('add-ons').appendChild(div);
    
    const nameInput = div.querySelector('.addon-name-input');
    attachAddonAutocomplete(nameInput, div);
    
    addOnCount++;
    if (typeof calculateTotal === 'function') calculateTotal();
}

function removeAddOn(button) {
    button.closest('.add-on').remove();
    if (typeof calculateTotal === 'function') calculateTotal();
}

function attachAddonAutocomplete(nameInput, container) {
    nameInput.addEventListener('input', function() {
        const value = this.value.toLowerCase();
        const match = addonData.find(addon => addon.name.toLowerCase() === value);
      
        if (match) {
            container.querySelector('.addon-code-input').value = match.code;
            container.querySelector('.addon-cost-input').value = match.cost;
            if (typeof calculateTotal === 'function') calculateTotal();
        }
    });
}

function addFee() {
    const div = document.createElement('div');
    div.className = 'one-time-fee grid grid-cols-1 sm:grid-cols-3 gap-4 items-end';
    div.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="one_time_fees[${feeCount}][name]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
            <input type="number" name="one_time_fees[${feeCount}][cost]" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
        </div>
        <div>
            <button type="button" onclick="removeFee(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Remove
            </button>
        </div>
    `;
    document.getElementById('one-time-fees').appendChild(div);
    feeCount++;
    if (typeof calculateTotal === 'function') calculateTotal();
}

function removeFee(button) {
    button.parentElement.parentElement.remove();
    if (typeof calculateTotal === 'function') calculateTotal();
}

document.getElementById('start_date').addEventListener('change', function() {
    const startDateInput = this.value;
    if (startDateInput) {
        const startDate = new Date(startDateInput);
        const endDate = new Date(startDate);
        endDate.setFullYear(startDate.getFullYear() + 2);
        if (endDate.getDate() !== startDate.getDate()) {
            endDate.setDate(startDate.getDate() + 1);
        }
        const formattedEndDate = endDate.toISOString().split('T')[0];
        document.getElementById('end_date').value = formattedEndDate;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const deviceSelect = document.getElementById('bell_device_id');
    const pricingTypeSelect = document.getElementById('pricing_type');
    const loadPricingBtn = document.getElementById('load-pricing-btn');
    const applyPricingBtn = document.getElementById('apply-pricing-btn');
    const pricingDetails = document.getElementById('pricing-details');
    
    function checkLoadPricingButton() {
        const deviceId = deviceSelect.value;
        const tier = document.getElementById('selected_tier') ? document.getElementById('selected_tier').value : '';
        
        if (deviceId && tier) {
            loadPricingBtn.disabled = false;
            loadPricingBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            loadPricingBtn.disabled = true;
            loadPricingBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
    
    deviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const hasSmartpay = selectedOption.getAttribute('data-has-smartpay') === 'true';
        const hasDro = selectedOption.getAttribute('data-has-dro') === 'true';
        
        Array.from(pricingTypeSelect.options).forEach(option => {
            if (option.value === 'smartpay') {
                option.disabled = !hasSmartpay;
            } else if (option.value === 'dro') {
                option.disabled = !hasDro;
            }
        });
        
        if (hasSmartpay) {
            pricingTypeSelect.value = 'smartpay';
        } else if (hasDro) {
            pricingTypeSelect.value = 'dro';
        }
        
        pricingDetails.classList.add('hidden');
        applyPricingBtn.classList.add('hidden');
        currentPricingData = null;
        
        checkLoadPricingButton();
    });
    
    const selectedTierField = document.getElementById('selected_tier');
    if (selectedTierField) {
        const tierObserver = new MutationObserver(checkLoadPricingButton);
        tierObserver.observe(selectedTierField, {
            attributes: true,
            attributeFilter: ['value']
        });
        
        selectedTierField.addEventListener('change', checkLoadPricingButton);
    }
    
    checkLoadPricingButton();
    
    loadPricingBtn.addEventListener('click', function() {
        const deviceId = deviceSelect.value;
        const pricingType = pricingTypeSelect.value;
        const tier = document.getElementById('selected_tier').value;
        
        if (!deviceId) {
            alert('Please select a device first');
            return;
        }
        if (!tier) {
            alert('Please select a rate plan first to determine the tier.');
            return;
        }
        
        loadPricingBtn.disabled = true;
        loadPricingBtn.textContent = 'Loading...';
        
        fetch(`/api/bell-pricing/device?device_id=${deviceId}&tier=${tier}&pricing_type=${pricingType}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Pricing not found for this device and tier: ' + data.error);
                    return;
                }
                
                currentPricingData = {
                    device_id: deviceId,
                    device_name: deviceSelect.options[deviceSelect.selectedIndex].getAttribute('data-name'),
                    pricing_type: pricingType,
                    tier: tier,
                    pricing: data.pricing
                };
								
				document.getElementById('display-retail-price').textContent = '$' + parseFloat(data.pricing.retail_price).toFixed(2);
				document.getElementById('display-agreement-credit').textContent = data.pricing.agreement_credit ? '$' + parseFloat(data.pricing.agreement_credit).toFixed(2) : '$0.00';
				document.getElementById('display-monthly-device').textContent = '$' + parseFloat(data.pricing.monthly_device_cost_pre_tax).toFixed(2);
				document.getElementById('display-plan-device').textContent = '$' + parseFloat(data.pricing.plan_plus_device_pre_tax).toFixed(2);
                
                if (pricingType === 'dro') {
                    document.getElementById('dro-amount-display').classList.remove('hidden');
                    document.getElementById('display-dro-amount').textContent = '$' + parseFloat(data.pricing.dro_amount).toFixed(2);
                } else {
                    document.getElementById('dro-amount-display').classList.add('hidden');
                }
                
                pricingDetails.classList.remove('hidden');
                applyPricingBtn.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load pricing: ' + error.message);
            })
            .finally(() => {
                loadPricingBtn.disabled = false;
                loadPricingBtn.textContent = 'Load Pricing';
                checkLoadPricingButton();
            });
    });
    
    applyPricingBtn.addEventListener('click', function() {
        if (!currentPricingData) {
            alert('Please load pricing first');
            return;
        }
        
        const pricing = currentPricingData.pricing;
        
        document.getElementById('hidden_bell_device_id').value = currentPricingData.device_id;
        document.getElementById('hidden_bell_pricing_type').value = currentPricingData.pricing_type;
        document.getElementById('hidden_bell_tier').value = currentPricingData.tier;
        document.getElementById('hidden_bell_retail_price').value = pricing.retail_price;
        document.getElementById('hidden_bell_monthly_device_cost').value = pricing.monthly_device_cost_pre_tax;
        document.getElementById('hidden_bell_plan_cost').value = pricing.plan_cost;
        document.getElementById('hidden_bell_dro_amount').value = pricing.dro_amount || 0;
        document.getElementById('hidden_bell_plan_plus_device').value = pricing.plan_plus_device_pre_tax;
        
        const deferredInput = document.getElementById('deferred_payment_amount');
        if (currentPricingData.pricing_type === 'dro' && pricing.dro_amount) {
            deferredInput.value = parseFloat(pricing.dro_amount).toFixed(2);
        } else {
            deferredInput.value = '';
        }
        
        if (pricing.agreement_credit) {
            document.getElementById('agreement_credit_amount').value = pricing.agreement_credit;
        }

        applyPricingBtn.textContent = '✓ Applied';
        applyPricingBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
        applyPricingBtn.classList.add('bg-gray-400');
        applyPricingBtn.disabled = true;
        
        setTimeout(() => {
            applyPricingBtn.textContent = 'Apply to Contract';
            applyPricingBtn.classList.remove('bg-gray-400');
            applyPricingBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            applyPricingBtn.disabled = false;
        }, 2000);
        
        calculateTotal();
    });
    
    document.querySelectorAll('.addon-name-input').forEach(input => {
        const container = input.closest('.add-on');
        if (container) {
            attachAddonAutocomplete(input, container);
        }
    });
    
    function calculateTotal() {
        const pricingType = document.getElementById('hidden_bell_pricing_type').value;
        let devicePrice = (pricingType !== 'byod') ? (parseFloat(document.getElementById('hidden_bell_retail_price').value) || 0) : 0;
        let agreementCredit = parseFloat(document.getElementById('agreement_credit_amount').value) || 0;
        let requiredUpfront = parseFloat(document.getElementById('required_upfront_payment').value) || 0;
        let optionalDown = parseFloat(document.getElementById('optional_down_payment').value) || 0;
        let deferred = parseFloat(document.getElementById('deferred_payment_amount').value) || 0;

        let ratePlanPrice = parseFloat(document.getElementById('rate_plan_price').value) || 0;
        let mobileInternetPrice = parseFloat(document.getElementById('mobile_internet_price').value) || 0;
        let planPrice = ratePlanPrice + mobileInternetPrice;

        let addOnTotal = 0;
        document.querySelectorAll('[name^="add_ons["][name$="[cost]"]').forEach(input => {
            addOnTotal += parseFloat(input.value) || 0;
        });

        // Calculate monthly device payment correctly
        // Formula: (Device Price - Agreement Credit - Required Upfront - Optional Down - Deferred) / 24
        let deviceAmount = devicePrice - agreementCredit;
        let totalFinancedAmount = deviceAmount - requiredUpfront - optionalDown;
        let remainingBalance = totalFinancedAmount - deferred;
        let monthlyDevicePayment = remainingBalance / 24;

        // Monthly total = monthly device payment + plan + add-ons (NOT including one-time fees)
        let total = monthlyDevicePayment + planPrice + addOnTotal;

        document.getElementById('total-cost').textContent = '$' + total.toFixed(2);
    }
    
    document.querySelectorAll('.total-input').forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
    
    const addOnsContainer = document.getElementById('add-ons');
    const feesContainer = document.getElementById('one-time-fees');
    if (addOnsContainer) addOnsContainer.addEventListener('input', calculateTotal);
    if (feesContainer) feesContainer.addEventListener('input', calculateTotal);
    
    const cellularPriceFields = ['rate_plan_price', 'mobile_internet_price'];
    cellularPriceFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            const observer = new MutationObserver(calculateTotal);
            observer.observe(field, { attributes: true, attributeFilter: ['value'] });
        }
    });
    
    calculateTotal();
    window.calculateTotal = calculateTotal;
});

// Tier synchronization and device filtering - wrapped in DOMContentLoaded with delay
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const tierSelect = document.getElementById('tier');
        const selectedTierField = document.getElementById('selected_tier');
        const tierAutoLabel = document.getElementById('tier-auto-label');
        const deviceSelect = document.getElementById('bell_device_id');

        function filterDevicesByTier(selectedTier) {
            const allOptions = Array.from(deviceSelect.options);

            console.log('Filtering devices for tier:', selectedTier);

            allOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = '';
                    option.disabled = false;
                    return;
                }

                const availableTiers = option.getAttribute('data-available-tiers');

                if (!selectedTier || !availableTiers) {
                    option.style.display = '';
                    option.disabled = false;
                } else {
                    const tiersArray = availableTiers.split(',');
                    // Always show devices if they match the selected tier OR if they have Basic tier pricing
                    if (tiersArray.includes(selectedTier) || tiersArray.includes('Basic')) {
                        option.style.display = '';
                        option.disabled = false;
                    } else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                }
            });

            const currentDeviceOption = deviceSelect.options[deviceSelect.selectedIndex];
            if (currentDeviceOption && currentDeviceOption.disabled) {
                deviceSelect.value = '';
                const pricingDetails = document.getElementById('pricing-details');
                const applyPricingBtn = document.getElementById('apply-pricing-btn');
                pricingDetails.classList.add('hidden');
                applyPricingBtn.classList.add('hidden');
                currentPricingData = null;
            }
        }

        function syncTierWithPlan() {
            const planTier = selectedTierField.value;
            
            if (planTier) {
                console.log('Syncing tier to:', planTier);
                tierSelect.value = planTier;
                tierSelect.disabled = true;
                tierSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
                if (tierAutoLabel) tierAutoLabel.style.display = 'inline';
                
                filterDevicesByTier(planTier);
            } else {
                console.log('No plan tier set, enabling tier selection');
                tierSelect.disabled = false;
                tierSelect.classList.remove('bg-gray-100', 'cursor-not-allowed');
                if (tierAutoLabel) tierAutoLabel.style.display = 'none';
                
                filterDevicesByTier(null);
            }
        }

        const tierObserver = new MutationObserver(function(mutations) {
            syncTierWithPlan();
        });

        if (selectedTierField) {
            tierObserver.observe(selectedTierField, {
                attributes: true,
                attributeFilter: ['value']
            });
            
            selectedTierField.addEventListener('change', syncTierWithPlan);
            selectedTierField.addEventListener('input', syncTierWithPlan);
        }

        if (tierSelect) {
            tierSelect.addEventListener('change', function() {
                if (!tierSelect.disabled) {
                    filterDevicesByTier(this.value);
                }
            });
        }

        syncTierWithPlan();
        
    }, 150);
});

// BYOD Plan Handler
const bellDeviceSection = document.querySelector('.bg-gray-50.border.border-gray-200.rounded-lg.p-6.mt-4.mb-6');
const cellularPlanSelect = document.getElementById('cellular_plan_selector');

function handlePlanChange(event) {
    const selectedOpt = event.target.options[event.target.selectedIndex];
    const planType = selectedOpt ? selectedOpt.getAttribute('data-plan-type') : null;
  
    console.log('handlePlanChange called, planType:', planType);
    
    const bellDeviceSection = document.getElementById('bell-device-selection-section');
    const byodNotice = document.getElementById('byod-notice');
    const agreementCreditSection = document.getElementById('agreement-credit-section');
    const currentBellDeviceDisplay = document.getElementById('current-bell-device-display');
    const deviceSelect = document.getElementById('bell_device_id');
    
    if (planType === 'byod') {
        console.log('Activating BYOD mode');
        
        bellDeviceSection.classList.add('hidden');
        byodNotice.classList.remove('hidden');
        
        if (currentBellDeviceDisplay) {
            currentBellDeviceDisplay.classList.add('hidden');
        }
        
        if (agreementCreditSection) {
            agreementCreditSection.classList.add('hidden');
        }
        document.getElementById('agreement_credit_amount').value = 0;
      
        document.getElementById('hidden_bell_device_id').value = '';
        document.getElementById('hidden_bell_pricing_type').value = 'byod';
        document.getElementById('hidden_bell_retail_price').value = 0;
        document.getElementById('hidden_bell_monthly_device_cost').value = 0;
        document.getElementById('hidden_bell_plan_cost').value = 0;
        document.getElementById('hidden_bell_dro_amount').value = 0;
        document.getElementById('hidden_bell_plan_plus_device').value = 0;
        
        deviceSelect.value = '';
    } else {
        console.log('Activating SmartPay/DRO mode');
        
        bellDeviceSection.classList.remove('hidden');
        byodNotice.classList.add('hidden');
        
        if (currentBellDeviceDisplay) {
            currentBellDeviceDisplay.classList.remove('hidden');
        }
        
        if (agreementCreditSection) {
            agreementCreditSection.classList.remove('hidden');
        }
    }
  
    calculateTotal();
}

if (cellularPlanSelect) {
    cellularPlanSelect.addEventListener('change', handlePlanChange);
}

function checkForPreAppliedBYOD() {
    const appliedRatePlanDiv = document.getElementById('applied_rate_plan');
    
    console.log('Checking for pre-applied BYOD plan');
    
    if (!appliedRatePlanDiv) {
        console.log('applied_rate_plan div not found');
        return;
    }
    
    const computedDisplay = window.getComputedStyle(appliedRatePlanDiv).display;
    console.log('applied_rate_plan display:', computedDisplay);
    
    if (computedDisplay !== 'none') {
        const appliedPlanType = appliedRatePlanDiv.getAttribute('data-plan-type');
        console.log('Pre-applied plan type:', appliedPlanType);
        
        const bellDeviceSection = document.getElementById('bell-device-selection-section');
        const byodNotice = document.getElementById('byod-notice');
        const agreementCreditSection = document.getElementById('agreement-credit-section');
        const currentBellDeviceDisplay = document.getElementById('current-bell-device-display');
        
        if (appliedPlanType === 'byod') {
            console.log('Detected BYOD plan, activating BYOD mode');
            
            bellDeviceSection.classList.add('hidden');
            byodNotice.classList.remove('hidden');
            
            if (currentBellDeviceDisplay) {
                currentBellDeviceDisplay.classList.add('hidden');
            }
            
            if (agreementCreditSection) {
                agreementCreditSection.classList.add('hidden');
            }
            
            document.getElementById('hidden_bell_device_id').value = '';
            document.getElementById('hidden_bell_pricing_type').value = 'byod';
            document.getElementById('hidden_bell_retail_price').value = 0;
            document.getElementById('hidden_bell_monthly_device_cost').value = 0;
            document.getElementById('hidden_bell_dro_amount').value = 0;
            document.getElementById('hidden_bell_plan_plus_device').value = 0;
            document.getElementById('agreement_credit_amount').value = 0;
            
            const planCostField = document.getElementById('hidden_bell_plan_cost');
            const ratePlanPriceField = document.getElementById('rate_plan_price');
            if (planCostField && ratePlanPriceField && ratePlanPriceField.value) {
                planCostField.value = ratePlanPriceField.value;
            }
            
            calculateTotal();
        } else {
            console.log('Applied plan is not BYOD, ensuring device section is visible');
            
            bellDeviceSection.classList.remove('hidden');
            byodNotice.classList.add('hidden');
            
            if (currentBellDeviceDisplay) {
                currentBellDeviceDisplay.classList.remove('hidden');
            }
            
            if (agreementCreditSection) {
                agreementCreditSection.classList.remove('hidden');
            }
        }
    } else {
        console.log('applied_rate_plan div is not visible');
    }
}

setTimeout(checkForPreAppliedBYOD, 0);
setTimeout(checkForPreAppliedBYOD, 50);
setTimeout(checkForPreAppliedBYOD, 100);
setTimeout(checkForPreAppliedBYOD, 200);
setTimeout(checkForPreAppliedBYOD, 500);

const applyPlanBtn = document.getElementById('apply_plan_btn');
if (applyPlanBtn) {
    const originalHandler = applyPlanBtn.onclick;
    
    applyPlanBtn.onclick = function(event) {
        if (originalHandler) {
            originalHandler.call(this, event);
        }
        
        setTimeout(function() {
            const bellDeviceSection = document.getElementById('bell-device-selection-section');
            const byodNotice = document.getElementById('byod-notice');
            const agreementCreditSection = document.getElementById('agreement-credit-section');
            const currentBellDeviceDisplay = document.getElementById('current-bell-device-display');
            
            if (window.currentSelectedPlan && 
                window.currentSelectedPlan.type === 'rate_plan' && 
                window.currentSelectedPlan.plan_type === 'byod') {
                
                console.log('Applied plan is BYOD, switching mode');
                
                bellDeviceSection.classList.add('hidden');
                byodNotice.classList.remove('hidden');
                
                if (currentBellDeviceDisplay) {
                    currentBellDeviceDisplay.classList.add('hidden');
                }
                
                if (agreementCreditSection) {
                    agreementCreditSection.classList.add('hidden');
                }
                
                document.getElementById('hidden_bell_device_id').value = '';
                document.getElementById('hidden_bell_pricing_type').value = 'byod';
                document.getElementById('hidden_bell_retail_price').value = 0;
                document.getElementById('hidden_bell_monthly_device_cost').value = 0;
                document.getElementById('hidden_bell_dro_amount').value = 0;
                document.getElementById('hidden_bell_plan_plus_device').value = 0;
                document.getElementById('agreement_credit_amount').value = 0;
                
                const planCostField = document.getElementById('hidden_bell_plan_cost');
                const ratePlanPriceField = document.getElementById('rate_plan_price');
                if (planCostField && ratePlanPriceField && ratePlanPriceField.value) {
                    planCostField.value = ratePlanPriceField.value;
                }
                
                calculateTotal();
            } else if (window.currentSelectedPlan && 
                       window.currentSelectedPlan.type === 'rate_plan') {
                console.log('Applied plan is not BYOD, showing device selection');
                
                bellDeviceSection.classList.remove('hidden');
                byodNotice.classList.add('hidden');
                
                if (currentBellDeviceDisplay) {
                    currentBellDeviceDisplay.classList.remove('hidden');
                }
                
                if (agreementCreditSection) {
                    agreementCreditSection.classList.remove('hidden');
                }
            }
        }, 100);
    };
}
</script>
@endsection