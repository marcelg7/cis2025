@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Contract #{{ $contract->id }}</h1>
        @if (session('error'))
            <div class="bg-red-50 p-3 rounded-lg shadow-sm mb-6">
                {{ session('error') }}
            </div>
        @endif
        <form method="POST" action="{{ route('contracts.update', $contract->id) }}" class="mt-6 space-y-8">
            @csrf
            @method('PUT')
            <div>
                <h2 class="text-lg font-medium text-gray-900">Contract Details</h2>
                <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $contract->start_date ? $contract->start_date->format('Y-m-d') : '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('start_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $contract->end_date ? $contract->end_date->format('Y-m-d') : '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
                        <input type="date" name="contract_date" id="contract_date" value="{{ old('contract_date', $contract->contract_date ? $contract->contract_date->format('Y-m-d') : '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('contract_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <select name="location" id="location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            <option value="zurich" {{ old('location', $contract->location) === 'zurich' ? 'selected' : '' }}>Zurich</option>
                            <option value="exeter" {{ old('location', $contract->location) === 'exeter' ? 'selected' : '' }}>Exeter</option>
                            <option value="grand_bend" {{ old('location', $contract->location) === 'grand_bend' ? 'selected' : '' }}>Grand Bend</option>
                        </select>
                        @error('location')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-medium text-gray-900">Device Details</h2>
                <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                    <div>
                        <label for="shortcode_id" class="block text-sm font-medium text-gray-700">Device</label>
                        <select name="shortcode_id" id="shortcode_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">None</option>
                            @foreach ($shortcodes as $shortcode)
                                <option value="{{ $shortcode['id'] }}" {{ old('shortcode_id', $contract->shortcode_id ?? '') == $shortcode['id'] ? 'selected' : '' }}>
                                    {{ $shortcode['display'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('shortcode_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sim_number" class="block text-sm font-medium text-gray-700">SIM #</label>
                        <input type="text" name="sim_number" id="sim_number" value="{{ old('sim_number', $contract->sim_number) }}" maxlength="50" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('sim_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="imei_number" class="block text-sm font-medium text-gray-700">IMEI #</label>
                        <input type="text" name="imei_number" id="imei_number" value="{{ old('imei_number', $contract->imei_number) }}" maxlength="50" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('imei_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="amount_paid_for_device" class="block text-sm font-medium text-gray-700">Amount Paid for Device ($)</label>
                        <input type="number" name="amount_paid_for_device" id="amount_paid_for_device" step="0.01" value="{{ old('amount_paid_for_device', $contract->amount_paid_for_device) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('amount_paid_for_device')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="agreement_credit_amount" class="block text-sm font-medium text-gray-700">Agreement Credit Amount ($)</label>
                        <input type="number" name="agreement_credit_amount" id="agreement_credit_amount" step="0.01" value="{{ old('agreement_credit_amount', $contract->agreement_credit_amount) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
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
                        <input type="number" name="required_upfront_payment" id="required_upfront_payment" step="0.01" value="{{ old('required_upfront_payment', $contract->required_upfront_payment) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('required_upfront_payment')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="optional_down_payment" class="block text-sm font-medium text-gray-700">Optional Down Payment ($)</label>
                        <input type="number" name="optional_down_payment" id="optional_down_payment" step="0.01" value="{{ old('optional_down_payment', $contract->optional_down_payment) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('optional_down_payment')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="deferred_payment_amount" class="block text-sm font-medium text-gray-700">Deferred Payment Amount ($)</label>
                        <input type="number" name="deferred_payment_amount" id="deferred_payment_amount" step="0.01" value="{{ old('deferred_payment_amount', $contract->deferred_payment_amount) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('deferred_payment_amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="dro_amount" class="block text-sm font-medium text-gray-700">DRO Amount ($)</label>
                        <input type="number" name="dro_amount" id="dro_amount" step="0.01" value="{{ old('dro_amount', $contract->dro_amount) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('dro_amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-medium text-gray-900">Plan Section</h2>
                <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                    <div>
                        <label for="plan_id" class="block text-sm font-medium text-gray-700">Plan</label>
                        <select name="plan_id" id="plan_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id', $contract->plan_id) == $plan->id ? 'selected' : '' }}>{{ $plan->name }} (${{ number_format($plan->price, 2) }})</option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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
                        <input type="date" name="first_bill_date" id="first_bill_date" value="{{ old('first_bill_date', $contract->first_bill_date ? $contract->first_bill_date->format('Y-m-d') : $defaultFirstBillDate->toDateString()) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('first_bill_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-medium text-gray-900">Plan Add-ons</h2>
                <div id="add-ons" class="mt-4 space-y-4">
                    @foreach ($contract->addOns as $index => $addOn)
                        <div class="add-on grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                            <div>
                                <label for="add_ons[{{ $index }}][name]" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="add_ons[{{ $index }}][name]" id="add_ons[{{ $index }}][name]" value="{{ old("add_ons.{$index}.name", $addOn->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error("add_ons.{$index}.name")
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="add_ons[{{ $index }}][code]" class="block text-sm font-medium text-gray-700">Code</label>
                                <input type="text" name="add_ons[{{ $index }}][code]" id="add_ons[{{ $index }}][code]" value="{{ old("add_ons.{$index}.code", $addOn->code) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error("add_ons.{$index}.code")
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="add_ons[{{ $index }}][cost]" class="block text-sm font-medium text-gray-700">Cost ($)</label>
                                <input type="number" name="add_ons[{{ $index }}][cost]" id="add_ons[{{ $index }}][cost]" step="0.01" value="{{ old("add_ons.{$index}.cost", $addOn->cost) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
            </div>
            <div>
                <h2 class="text-lg font-medium text-gray-900">One Time Fees</h2>
                <div id="one-time-fees" class="mt-4 space-y-4">
                    @foreach ($contract->oneTimeFees as $index => $fee)
                        <div class="one-time-fee grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                            <div>
                                <label for="one_time_fees[{{ $index }}][name]" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="one_time_fees[{{ $index }}][name]" id="one_time_fees[{{ $index }}][name]" value="{{ old("one_time_fees.{$index}.name", $fee->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error("one_time_fees.{$index}.name")
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="one_time_fees[{{ $index }}][cost]" class="block text-sm font-medium text-gray-700">Cost ($)</label>
                                <input type="number" name="one_time_fees[{{ $index }}][cost]" id="one_time_fees[{{ $index }}][cost]" step="0.01" value="{{ old("one_time_fees.{$index}.cost", $fee->cost) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
        let addOnCount = {{ $contract->addOns->count() }};
        let feeCount = {{ $contract->oneTimeFees->count() }};
        function addAddOn() {
            const div = document.createElement('div');
            div.className = 'add-on grid grid-cols-1 sm:grid-cols-4 gap-4 items-end';
            div.innerHTML = `
                <div>
                    <label for="add_ons[${addOnCount}][name]" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="add_ons[${addOnCount}][name]" id="add_ons[${addOnCount}][name]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="add_ons[${addOnCount}][code]" class="block text-sm font-medium text-gray-700">Code</label>
                    <input type="text" name="add_ons[${addOnCount}][code]" id="add_ons[${addOnCount}][code]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="add_ons[${addOnCount}][cost]" class="block text-sm font-medium text-gray-700">Cost ($)</label>
                    <input type="number" name="add_ons[${addOnCount}][cost]" id="add_ons[${addOnCount}][cost]" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <button type="button" onclick="removeAddOn(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
            `;
            document.getElementById('add-ons').appendChild(div);
            addOnCount++;
        }
        function removeAddOn(button) {
            button.parentElement.parentElement.remove();
        }
        function addFee() {
            const div = document.createElement('div');
            div.className = 'one-time-fee grid grid-cols-1 sm:grid-cols-3 gap-4 items-end';
            div.innerHTML = `
                <div>
                    <label for="one_time_fees[${feeCount}][name]" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="one_time_fees[${feeCount}][name]" id="one_time_fees[${feeCount}][name]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="one_time_fees[${feeCount}][cost]" class="block text-sm font-medium text-gray-700">Cost ($)</label>
                    <input type="number" name="one_time_fees[${feeCount}][cost]" id="one_time_fees[${feeCount}][cost]" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <button type="button" onclick="removeFee(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
            `;
            document.getElementById('one-time-fees').appendChild(div);
            feeCount++;
        }
        function removeFee(button) {
            button.parentElement.parentElement.remove();
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
    </script>
@endsection