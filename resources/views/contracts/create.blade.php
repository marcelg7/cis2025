@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2">
        <h1 class="text-2xl font-semibold text-gray-900">Create Contract for {{ $subscriber->mobile_number }}</h1>
        <form method="POST" action="{{ route('contracts.store', $subscriber->id) }}" class="mt-6 space-y-8" id="contract-form">
            @csrf
            
            <!-- Hidden fields to store Bell pricing data -->
            <input type="hidden" name="bell_device_id" id="hidden_bell_device_id">
            <input type="hidden" name="bell_pricing_type" id="hidden_bell_pricing_type">
            <input type="hidden" name="bell_tier" id="hidden_bell_tier">
            <input type="hidden" name="bell_retail_price" id="hidden_bell_retail_price">
            <input type="hidden" name="bell_monthly_device_cost" id="hidden_bell_monthly_device_cost">
            <input type="hidden" name="bell_plan_cost" id="hidden_bell_plan_cost">
            <input type="hidden" name="bell_dro_amount" id="hidden_bell_dro_amount">
            <input type="hidden" name="bell_plan_plus_device" id="hidden_bell_plan_plus_device">
            
            <div>
                <h2 class="text-lg font-medium text-gray-900">Contract Details</h2>
                <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('start_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', now()->addYears(2)->toDateString()) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('end_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="activity_type_id" class="block text-sm font-medium text-gray-700">Activity Type</label>
                        <select name="activity_type_id" id="activity_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            @foreach ($activityTypes as $activityType)
                                <option value="{{ $activityType->id }}" {{ old('activity_type_id', $activityTypes->firstWhere('name', 'New Postpaid Activation')->id ?? '') == $activityType->id ? 'selected' : '' }}>{{ $activityType->name }}</option>
                            @endforeach
                        </select>
                        @error('activity_type_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="contract_date" class="block text-sm font-medium text-gray-700">Contract Date</label>
                        <input type="date" name="contract_date" id="contract_date" value="{{ old('contract_date', now()->toDateString()) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('contract_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <select name="location" id="location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            <option value="zurich" {{ old('location') === 'zurich' ? 'selected' : '' }}>Zurich</option>
                            <option value="exeter" {{ old('location') === 'exeter' ? 'selected' : '' }}>Exeter</option>
                            <option value="grand_bend" {{ old('location') === 'grand_bend' ? 'selected' : '' }}>Grand Bend</option>
                        </select>
                        @error('location')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Cellular Plans Section -->
            @include('contracts.partials._cellular_pricing_fields')
            
            <div>
                <h2 class="text-lg font-medium text-gray-900">Device Details</h2>
                
                <!-- Bell Device Selection -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-4 mb-6">
                    <h3 class="text-md font-medium text-gray-900 mb-4">Bell Device Selection (Imported Pricing)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Device Selection -->
                        <div>
                            <label for="bell_device_id" class="block text-sm font-medium text-gray-700">Bell Device</label>
                            <select name="bell_device_select"
                                    id="bell_device_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select a device</option>
                                @foreach($bellDevices as $device)
                                    <option value="{{ $device['id'] }}"
                                            data-name="{{ $device['name'] }}"
                                            data-has-smartpay="{{ $device['has_smartpay'] ? 'true' : 'false' }}"
                                            data-has-dro="{{ $device['has_dro'] ? 'true' : 'false' }}">
                                        {{ $device['name'] }}
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
                                <option value="smartpay">SmartPay</option>
                                <option value="dro">DRO (Device Return Option)</option>
                            </select>
                        </div>
                    </div>
                    <!-- Pricing Details Display -->
                    <div id="pricing-details" class="mt-4 p-4 bg-white border border-gray-300 rounded-md hidden">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Pricing Details</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Retail Price:</span>
                                <span class="font-medium text-gray-900" id="display-retail-price">-</span>
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
                
                <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                    <div>
                        <label for="agreement_credit_amount" class="block text-sm font-medium text-gray-700">Agreement Credit Amount ($)</label>
                        <input type="number" name="agreement_credit_amount" id="agreement_credit_amount" step="0.01" value="{{ old('agreement_credit_amount', 0) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input" required>
                        @error('agreement_credit_amount')
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
                        <input type="number" name="required_upfront_payment" id="required_upfront_payment" step="0.01" value="{{ old('required_upfront_payment', 0) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input" required>
                        @error('required_upfront_payment')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="optional_down_payment" class="block text-sm font-medium text-gray-700">Optional Up-front Payment ($)</label>
                        <input type="number" name="optional_down_payment" id="optional_down_payment" step="0.01" value="{{ old('optional_down_payment') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
                        @error('optional_down_payment')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="deferred_payment_amount" class="block text-sm font-medium text-gray-700">Deferred Payment Amount ($)</label>
                        <input type="number" name="deferred_payment_amount" id="deferred_payment_amount" step="0.01" value="{{ old('deferred_payment_amount') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
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
                                <option value="{{ $period->id }}" {{ old('commitment_period_id', $commitmentPeriods->firstWhere('name', '2 Year Term Smart Pay')->id ?? '') == $period->id ? 'selected' : '' }}>{{ $period->name }}</option>
                            @endforeach
                        </select>
                        @error('commitment_period_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="first_bill_date" class="block text-sm font-medium text-gray-700">First Bill Date</label>
                        <input type="date" name="first_bill_date" id="first_bill_date" value="{{ old('first_bill_date', $defaultFirstBillDate->toDateString()) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
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
                    @if (old('add_ons'))
                        @foreach (old('add_ons') as $index => $addOn)
                            <div class="add-on grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" 
                                           name="add_ons[{{ $index }}][name]" 
                                           value="{{ old("add_ons.{$index}.name") }}" 
                                           class="addon-name-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           list="addon-suggestions"
                                           autocomplete="off">
                                    @error("add_ons.{$index}.name")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Code</label>
                                    <input type="text" 
                                           name="add_ons[{{ $index }}][code]" 
                                           value="{{ old("add_ons.{$index}.code") }}" 
                                           class="addon-code-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error("add_ons.{$index}.code")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
                                    <input type="number" 
                                           name="add_ons[{{ $index }}][cost]" 
                                           step="0.01" 
                                           value="{{ old("add_ons.{$index}.cost") }}" 
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
                    @endif
                </div>
                <button type="button" onclick="addAddOn()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    + Add Add-on
                </button>
                
                <!-- Hidden datalist for autocomplete -->
                <datalist id="addon-suggestions">
                    @foreach($planAddOns as $addon)
                        <option value="{{ $addon->add_on_name }}" data-code="{{ $addon->soc_code }}" data-cost="{{ $addon->monthly_rate }}">
                    @endforeach
                </datalist>
            </div>
            
            <div>
                <h2 class="text-lg font-medium text-gray-900">One Time Fees</h2>
                <div id="one-time-fees" class="mt-4 space-y-4">
                    @if (old('one_time_fees'))
                        @foreach (old('one_time_fees') as $index => $fee)
                            <div class="one-time-fee grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="one_time_fees[{{ $index }}][name]" value="{{ old("one_time_fees.{$index}.name") }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error("one_time_fees.{$index}.name")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
                                    <input type="number" name="one_time_fees[{{ $index }}][cost]" step="0.01" value="{{ old("one_time_fees.{$index}.cost") }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
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
                    @endif
                </div>
                <button type="button" onclick="addFee()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    + Add Fee
                </button>
            </div>
            
            <!-- Dynamic Total Calculator -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-4">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Estimated Total Cost</h2>
                <div class="text-2xl font-bold text-indigo-600" id="total-cost">$0.00</div>
                <p class="text-sm text-gray-500 mt-2">This is a real-time estimate based on current selections (device, plan, add-ons, fees, financing, credits). Final total may vary.</p>
            </div>
            
            <div class="flex justify-end">
                <a href="{{ route('customers.show', $subscriber->mobilityAccount->ivueAccount->customer_id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Contract
                </button>
            </div>
        </form>
    </div>
    
<script>
    let addOnCount = {{ count(old('add_ons', [])) }};
    let feeCount = {{ count(old('one_time_fees', [])) }};
    let currentPricingData = null;
    
    // Add-on autocomplete data
    const addonData = @json($planAddOns->map(function($addon) {
        return [
            'name' => $addon->add_on_name,
            'code' => $addon->soc_code,
            'cost' => $addon->monthly_rate
        ];
    }));
    
    function addAddOn() {
        const div = document.createElement('div');
        div.className = 'add-on grid grid-cols-1 sm:grid-cols-4 gap-4 items-end';
        div.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" 
                       name="add_ons[${addOnCount}][name]" 
                       class="addon-name-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       list="addon-suggestions"
                       autocomplete="off">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" 
                       name="add_ons[${addOnCount}][code]" 
                       class="addon-code-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
        
        // Attach autocomplete to new input
        const nameInput = div.querySelector('.addon-name-input');
        attachAddonAutocomplete(nameInput, div);
        
        addOnCount++;
        if (typeof calculateTotal === 'function') calculateTotal();
    }
    
    function removeAddOn(button) {
        button.parentElement.parentElement.remove();
        if (typeof calculateTotal === 'function') calculateTotal();
    }
    
    // Attach autocomplete listener
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
    
    // Helper function to add fee with defaults
    function addFeeWithDefaults(name, cost) {
        const div = document.createElement('div');
        div.className = 'one-time-fee grid grid-cols-1 sm:grid-cols-3 gap-4 items-end';
        div.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="one_time_fees[${feeCount}][name]" value="${name}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
                <input type="number" name="one_time_fees[${feeCount}][cost]" step="0.01" value="${cost}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input">
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
    
    // Automatically set end_date to 2 years from start_date
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
    
    // Bell Device Pricing JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const deviceSelect = document.getElementById('bell_device_id');
        const pricingTypeSelect = document.getElementById('pricing_type');
        const loadPricingBtn = document.getElementById('load-pricing-btn');
        const applyPricingBtn = document.getElementById('apply-pricing-btn');
        const pricingDetails = document.getElementById('pricing-details');
        const deviceSection = document.querySelector('.bg-gray-50.border.border-gray-200.rounded-lg.p-6.mt-4.mb-6');
        
        // Function to check if Load Pricing button should be enabled
        function checkLoadPricingButton() {
            const deviceId = deviceSelect.value;
            const tier = document.getElementById('selected_tier').value;
            
            console.log('Checking Load Pricing button:', { deviceId, tier }); // Debug
            
            // Enable button only if both device and tier are selected
            if (deviceId && tier) {
                loadPricingBtn.disabled = false;
                loadPricingBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                loadPricingBtn.disabled = true;
                loadPricingBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
        
        // Auto-add connection fee for New Postpaid Activation
        const activityTypeSelect = document.getElementById('activity_type_id');
        if (activityTypeSelect) {
            activityTypeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const activityName = selectedOption.textContent.trim();
                
                console.log('Activity type changed to:', activityName); // Debug log
                
                if (activityName === 'New Postpaid Activation') {
                    // Check if connection fee already exists
                    const existingFees = Array.from(document.querySelectorAll('[name^="one_time_fees"][name$="[name]"]'))
                        .map(input => input.value.trim());
                    
                    console.log('Existing fees:', existingFees); // Debug log
                    
                    if (!existingFees.includes('Connection Fee')) {
                        console.log('Adding connection fee...'); // Debug log
                        // Add connection fee
                        addFeeWithDefaults('Connection Fee', 75);
                    } else {
                        console.log('Connection fee already exists'); // Debug log
                    }
                }
            });
            
            // Check on page load if "New Postpaid Activation" is already selected
            const currentSelection = activityTypeSelect.options[activityTypeSelect.selectedIndex];
            if (currentSelection && currentSelection.textContent.trim() === 'New Postpaid Activation') {
                // Check if connection fee already exists
                const existingFees = Array.from(document.querySelectorAll('[name^="one_time_fees"][name$="[name]"]'))
                    .map(input => input.value.trim());
                
                if (!existingFees.includes('Connection Fee')) {
                    // Add connection fee on page load
                    addFeeWithDefaults('Connection Fee', 75);
                }
            }
        }
        
        // Monitor device selection
        deviceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const hasSmartpay = selectedOption.getAttribute('data-has-smartpay') === 'true';
            const hasDro = selectedOption.getAttribute('data-has-dro') === 'true';
            
            // Enable/disable pricing types based on availability
            Array.from(pricingTypeSelect.options).forEach(option => {
                if (option.value === 'smartpay') {
                    option.disabled = !hasSmartpay;
                } else if (option.value === 'dro') {
                    option.disabled = !hasDro;
                }
            });
            
            // Select first available option
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
        
        // Monitor tier changes from cellular pricing component
        const selectedTierField = document.getElementById('selected_tier');
        if (selectedTierField) {
            // Use MutationObserver to watch for value changes
            const tierObserver = new MutationObserver(function(mutations) {
                checkLoadPricingButton();
            });
            
            tierObserver.observe(selectedTierField, { 
                attributes: true,
                attributeFilter: ['value']
            });
            
            // Also listen for input events
            selectedTierField.addEventListener('change', checkLoadPricingButton);
            
            // Listen for when the value is set programmatically
            const originalSetAttribute = selectedTierField.setAttribute.bind(selectedTierField);
            selectedTierField.setAttribute = function(name, value) {
                originalSetAttribute(name, value);
                if (name === 'value') {
                    checkLoadPricingButton();
                }
            };
        }
        
        // Initial check
        checkLoadPricingButton();
        
        // Load pricing when button is clicked
        loadPricingBtn.addEventListener('click', function() {
            const deviceId = deviceSelect.value;
            const pricingType = pricingTypeSelect.value;
            const tier = document.getElementById('selected_tier').value;
            
            console.log('Load Pricing clicked:', { deviceId, pricingType, tier }); // Debug log
            
            if (!deviceId) {
                alert('Please select a device first');
                return;
            }
            if (!tier) {
                alert('Please select a rate plan first to determine the tier.');
                return;
            }
            
            // Show loading state
            loadPricingBtn.disabled = true;
            loadPricingBtn.textContent = 'Loading...';
            
            // Fetch pricing
            fetch(`/api/bell-pricing/device?device_id=${deviceId}&tier=${tier}&pricing_type=${pricingType}`)
                .then(response => {
                    console.log('Response status:', response.status); // Debug log
                    return response.json();
                })
                .then(data => {
                    console.log('Pricing data:', data); // Debug log
                    
                    if (data.error) {
                        alert('Pricing not found for this device and tier: ' + data.error);
                        return;
                    }
                    
                    // Store pricing data
                    currentPricingData = {
                        device_id: deviceId,
                        device_name: deviceSelect.options[deviceSelect.selectedIndex].getAttribute('data-name'),
                        pricing_type: pricingType,
                        tier: tier,
                        pricing: data.pricing
                    };
                    
                    // Update display
                    document.getElementById('display-retail-price').textContent = '$' + parseFloat(data.pricing.retail_price).toFixed(2);
                    document.getElementById('display-monthly-device').textContent = '$' + parseFloat(data.pricing.monthly_device_cost_pre_tax).toFixed(2);
                    document.getElementById('display-plan-device').textContent = '$' + parseFloat(data.pricing.plan_plus_device_pre_tax).toFixed(2);
                    
                    // Show/hide DRO amount
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
        
        // Apply pricing to contract form
        applyPricingBtn.addEventListener('click', function() {
            if (!currentPricingData) {
                alert('Please load pricing first');
                return;
            }
            
            const pricing = currentPricingData.pricing;
            
            // Populate hidden fields
            document.getElementById('hidden_bell_device_id').value = currentPricingData.device_id;
            document.getElementById('hidden_bell_pricing_type').value = currentPricingData.pricing_type;
            document.getElementById('hidden_bell_tier').value = currentPricingData.tier;
            document.getElementById('hidden_bell_retail_price').value = pricing.retail_price;
            document.getElementById('hidden_bell_monthly_device_cost').value = pricing.monthly_device_cost_pre_tax;
            document.getElementById('hidden_bell_plan_cost').value = pricing.plan_cost;
            document.getElementById('hidden_bell_dro_amount').value = pricing.dro_amount || 0;
            document.getElementById('hidden_bell_plan_plus_device').value = pricing.plan_plus_device_pre_tax;
            
            // Auto-populate agreement credit if available
            if (pricing.agreement_credit) {
                document.getElementById('agreement_credit_amount').value = pricing.agreement_credit;
            }
            
            // Show success message
            alert(`Bell pricing applied!\n\nDevice: ${currentPricingData.device_name}\nTier: ${currentPricingData.tier}\nRetail Price: $${parseFloat(pricing.retail_price).toFixed(2)}`);
            
            // Change button appearance
            applyPricingBtn.textContent = '✓ Applied';
            applyPricingBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            applyPricingBtn.classList.add('bg-gray-400');
            applyPricingBtn.disabled = true;
            
            // Re-enable after 2 seconds
            setTimeout(() => {
                applyPricingBtn.textContent = 'Apply to Contract';
                applyPricingBtn.classList.remove('bg-gray-400');
                applyPricingBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                applyPricingBtn.disabled = false;
            }, 2000);
            
            calculateTotal();
        });
        
        // Attach to existing addon inputs on page load
        document.querySelectorAll('.addon-name-input').forEach(input => {
            const container = input.closest('.add-on');
            if (container) {
                attachAddonAutocomplete(input, container);
            }
        });
        
        // Dynamic total calculation
        function calculateTotal() {
            let devicePrice = parseFloat(document.getElementById('hidden_bell_retail_price').value) || 0;
            let agreementCredit = parseFloat(document.getElementById('agreement_credit_amount').value) || 0;
            let requiredUpfront = parseFloat(document.getElementById('required_upfront_payment').value) || 0;
            let optionalDown = parseFloat(document.getElementById('optional_down_payment').value) || 0;
            let deferred = parseFloat(document.getElementById('deferred_payment_amount').value) || 0;
            
            // Get cellular plan costs
            let ratePlanPrice = parseFloat(document.getElementById('rate_plan_price').value) || 0;
            let mobileInternetPrice = parseFloat(document.getElementById('mobile_internet_price').value) || 0;
            let planPrice = ratePlanPrice + mobileInternetPrice;
            
            let addOnTotal = 0;
            document.querySelectorAll('[name^="add_ons["][name$="[cost]"]').forEach(input => {
                addOnTotal += parseFloat(input.value) || 0;
            });
            
            let feeTotal = 0;
            document.querySelectorAll('[name^="one_time_fees["][name$="[cost]"]').forEach(input => {
                feeTotal += parseFloat(input.value) || 0;
            });
            
            let financingTotal = requiredUpfront + optionalDown + deferred;
            
            let total = devicePrice + planPrice + addOnTotal + feeTotal + financingTotal - agreementCredit;
            
            document.getElementById('total-cost').textContent = '$' + total.toFixed(2);
        }
        
        // Attach listeners to all inputs that affect total
        document.querySelectorAll('.total-input').forEach(input => {
            input.addEventListener('input', calculateTotal);
        });
        
        // Also listen to add-on and fee containers for dynamic changes
        const addOnsContainer = document.getElementById('add-ons');
        const feesContainer = document.getElementById('one-time-fees');
        if (addOnsContainer) addOnsContainer.addEventListener('input', calculateTotal);
        if (feesContainer) feesContainer.addEventListener('input', calculateTotal);
        
        // Monitor changes to cellular pricing fields
        const cellularPriceFields = ['rate_plan_price', 'mobile_internet_price'];
        cellularPriceFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const observer = new MutationObserver(calculateTotal);
                observer.observe(field, { attributes: true, attributeFilter: ['value'] });
            }
        });
        
        // Initial calculation
        calculateTotal();
        
        // Make calculateTotal available globally for dynamic add/remove
        window.calculateTotal = calculateTotal;
    });
</script>
@endsection