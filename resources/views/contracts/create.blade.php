@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2"> <!-- Added px-2 -->
        <h1 class="text-2xl font-semibold text-gray-900">Create Contract for {{ $subscriber->mobile_number }}</h1>
        <form method="POST" action="{{ route('contracts.store', $subscriber->id) }}" class="mt-6 space-y-8">
            @csrf
            <div>
                <h2 class="text-lg font-medium text-gray-900">Contract Details</h2>
                <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('start_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
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

            <div>
                <h2 class="text-lg font-medium text-gray-900">Device Details</h2>
                <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                    <div> 
                        <label for="device_id" class="block text-sm font-medium text-gray-700">Device</label>
                        <select name="device_id" id="device_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">None</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device['id'] }}" {{ old('device_id') == $device['id'] ? 'selected' : '' }}>{{ $device['parsed']['manufacturer'] }} {{ $device['parsed']['model'] }} {{ $device['parsed']['version'] }} {{ $device['parsed']['deviceStorage'] }} {{ $device['parsed']['extraInfo'] }} Price: {{ $device['data']}} </option>
                            @endforeach
                        </select>
                        @error('device_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sim_number" class="block text-sm font-medium text-gray-700">SIM #</label>
                        <input type="text" name="sim_number" id="sim_number" value="{{ old('sim_number') }}" maxlength="50" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('sim_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="imei_number" class="block text-sm font-medium text-gray-700">IMEI #</label>
                        <input type="text" name="imei_number" id="imei_number" value="{{ old('imei_number') }}" maxlength="50" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('imei_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="amount_paid_for_device" class="block text-sm font-medium text-gray-700">Amount Paid for Device ($)</label>
                        <input type="number" name="amount_paid_for_device" id="amount_paid_for_device" step="0.01" value="{{ old('amount_paid_for_device', 0) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('amount_paid_for_device')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="agreement_credit_amount" class="block text-sm font-medium text-gray-700">Agreement Credit Amount ($)</label>
                        <input type="number" name="agreement_credit_amount" id="agreement_credit_amount" step="0.01" value="{{ old('agreement_credit_amount', 0) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('agreement_credit_amount')
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
                                <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }} (${{ number_format($plan->price, 2) }})</option>
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
            </div>

            <div>
                <h2 class="text-lg font-medium text-gray-900">Plan Add-ons</h2>
                <div id="add-ons" class="mt-4 space-y-4">
                    @if (old('add_ons'))
                        @foreach (old('add_ons') as $index => $addOn)
                            <div class="add-on grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="add_ons[{{ $index }}][name]" value="{{ old("add_ons.{$index}.name") }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error("add_ons.{$index}.name")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Code</label>
                                    <input type="text" name="add_ons[{{ $index }}][code]" value="{{ old("add_ons.{$index}.code") }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error("add_ons.{$index}.code")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
                                    <input type="number" name="add_ons[{{ $index }}][cost]" step="0.01" value="{{ old("add_ons.{$index}.cost") }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
                                    <input type="number" name="one_time_fees[{{ $index }}][cost]" step="0.01" value="{{ old("one_time_fees.{$index}.cost") }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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

        function addAddOn() {
            const div = document.createElement('div');
            div.className = 'add-on grid grid-cols-1 sm:grid-cols-4 gap-4 items-end';
            div.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="add_ons[${addOnCount}][name]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Code</label>
                    <input type="text" name="add_ons[${addOnCount}][code]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
                    <input type="number" name="add_ons[${addOnCount}][cost]" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <button type="button" onclick="removeAddOn(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
                <br>
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
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="one_time_fees[${feeCount}][name]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
                    <input type="number" name="one_time_fees[${feeCount}][cost]" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <button type="button" onclick="removeFee(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
                <br>
            `;
            document.getElementById('one-time-fees').appendChild(div);
            feeCount++;
        }

        function removeFee(button) {
            button.parentElement.parentElement.remove();
        }
    </script>
@endsection