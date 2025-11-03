@extends('layouts.app')

@section('content')
<div class="py-12">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="calculatorApp()">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Contract Calculator & Comparison Tool</h1>
                    <p class="mt-2 text-sm text-gray-600">Compare up to 4 rate plans side-by-side with device and add-on costs</p>
                </div>
                <div class="flex space-x-3">
                    <!-- Saved Comparisons Dropdown -->
                    <div x-show="savedComparisons.length > 0" class="relative">
                <button @click="showSaved = !showSaved" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    Load Saved
                </button>
                <div x-show="showSaved" @click.away="showSaved = false" class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10 max-h-96 overflow-y-auto">
                    <template x-for="comp in savedComparisons" :key="comp.id">
                        <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100">
                            <div @click="loadComparison(comp.id)" class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900" x-text="comp.name"></p>
                                    <p class="text-xs text-gray-500" x-text="comp.plan_count + ' plans'"></p>
                                </div>
                                <button @click.stop="deleteComparison(comp.id)" class="text-red-600 hover:text-red-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Panel -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuration</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Device Selection (Optional) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Device (Optional - BYOD if not selected)</label>
                <select x-model="deviceId" @change="updateCalculations()" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">BYOD (Bring Your Own Device)</option>
                    @foreach($bellDevices as $device)
                        <option value="{{ $device->id }}">{{ $device->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Financing Type -->
            <div x-show="deviceId">
                <label class="block text-sm font-medium text-gray-700 mb-2">Financing Type</label>
                <select x-model="financingType" @change="updateCalculations()" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="smartpay">SmartPay (24 months)</option>
                    <option value="dro">DRO (Upfront Payment)</option>
                </select>
            </div>

            <!-- Agreement Credit -->
            <div x-show="deviceId && financingType === 'smartpay'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Agreement Credit ($)</label>
                <input type="number" x-model.number="agreementCredit" @input="updateCalculations()" min="0" step="0.01" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
            </div>

            <!-- Upfront Payment -->
            <div x-show="deviceId && financingType === 'smartpay'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Upfront Payment ($)</label>
                <input type="number" x-model.number="upfrontPayment" @input="updateCalculations()" min="0" step="0.01" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
            </div>

            <!-- Down Payment -->
            <div x-show="deviceId && financingType === 'smartpay'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Down Payment ($)</label>
                <input type="number" x-model.number="downPayment" @input="updateCalculations()" min="0" step="0.01" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
            </div>

            <!-- Mobile Internet Plan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Internet Plan (Optional)</label>
                <select x-model="mobileInternetId" @change="updateCalculations()" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">None</option>
                    @foreach($mobileInternetPlans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->plan_name }} - {{ $plan->soc_code }} - ${{ number_format($plan->monthly_rate, 2) }}/mo</option>
                    @endforeach
                </select>
            </div>

            <!-- Add-Ons -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Add-Ons (Optional)</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($addOns as $addOn)
                        <label class="flex items-center">
                            <input type="checkbox" value="{{ $addOn->id }}" x-model="addOns" @change="updateCalculations()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $addOn->add_on_name }} - {{ $addOn->soc_code }} (${{ number_format($addOn->monthly_rate, 2) }})</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Selection -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Rate Plans to Compare (1-4 plans)</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <template x-for="(slot, index) in [0, 1, 2, 3]" :key="slot">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Plan <span x-text="index + 1"></span></label>
                    <select x-model="selectedPlans[index]" @change="updateCalculations()" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">-- Select Plan --</option>
                        @foreach($ratePlans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->plan_name }} - {{ $plan->soc_code }} - ${{ number_format($plan->effective_price, 2) }}</option>
                        @endforeach
                    </select>
                </div>
            </template>
        </div>

        <div class="mt-4 flex justify-end">
            <button @click="calculate()" :disabled="selectedPlans.filter(p => p).length === 0" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Calculate Comparison
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div x-show="loading" class="text-center py-12">
        <svg class="animate-spin h-12 w-12 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-4 text-gray-600">Calculating comparison...</p>
    </div>

    <!-- Comparison Results -->
    <div x-show="calculations.length > 0 && !loading" class="space-y-6">
        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-green-900 mb-1">Best Overall Value</h4>
                <p class="text-2xl font-bold text-green-700" x-text="bestValue?.plan_name || 'N/A'"></p>
                <p class="text-sm text-green-600" x-text="'$' + (bestValue?.costs?.total_24_months || 0).toFixed(2) + ' over 24 months'"></p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-900 mb-1">Lowest Monthly Cost</h4>
                <p class="text-2xl font-bold text-blue-700" x-text="lowestMonthly?.plan_name || 'N/A'"></p>
                <p class="text-sm text-blue-600" x-text="'$' + (lowestMonthly?.costs?.avg_monthly || 0).toFixed(2) + '/month avg'"></p>
            </div>

            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-purple-900 mb-1">Comparing</h4>
                <p class="text-2xl font-bold text-purple-700" x-text="calculations.length + ' Plans'"></p>
                <p class="text-sm text-purple-600">Side-by-side comparison</p>
            </div>
        </div>

        <!-- Comparison Table -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feature</th>
                            <template x-for="(calc, index) in calculations" :key="index">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <span x-text="calc.plan_name"></span>
                                        <span x-show="calc.plan_id === bestValue?.plan_id" class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Best Value</span>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data Allowance -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Data</td>
                            <template x-for="calc in calculations" :key="calc.plan_id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="calc.features.data"></td>
                            </template>
                        </tr>

                        <!-- Base Monthly Cost -->
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Monthly Cost</td>
                            <template x-for="calc in calculations" :key="calc.plan_id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900" x-text="'$' + calc.costs.avg_monthly.toFixed(2)"></td>
                            </template>
                        </tr>

                        <!-- Upfront Costs -->
                        <tr x-show="calculations.some(c => c.costs.upfront > 0)">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Upfront Costs</td>
                            <template x-for="calc in calculations" :key="calc.plan_id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="'$' + calc.costs.upfront.toFixed(2)"></td>
                            </template>
                        </tr>

                        <!-- 24-Month Total -->
                        <tr class="bg-blue-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">24-Month Total</td>
                            <template x-for="calc in calculations" :key="calc.plan_id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-900" x-text="'$' + calc.costs.total_24_months.toFixed(2)"></td>
                            </template>
                        </tr>

                        <!-- Hay Credit Savings -->
                        <tr x-show="calculations.some(c => c.costs.hay_credit_total > 0)">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-700">Hay Credit Savings</td>
                            <template x-for="calc in calculations" :key="calc.plan_id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-700">
                                    <span x-show="calc.costs.hay_credit_total > 0" x-text="'-$' + calc.costs.hay_credit_total.toFixed(2) + ' (' + calc.costs.hay_credit_months + ' months)'"></span>
                                    <span x-show="calc.costs.hay_credit_total === 0">-</span>
                                </td>
                            </template>
                        </tr>

                        <!-- Savings Compared to Most Expensive -->
                        <tr class="bg-green-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Savings vs Most Expensive</td>
                            <template x-for="calc in calculations" :key="calc.plan_id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    <span :class="getSavingsClass(calc)" x-text="getSavingsText(calc)"></span>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
            <button @click="saveComparison()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Save Comparison
            </button>
            <button @click="exportPdf()" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Export PDF
            </button>
            <button @click="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>
    </div>
</div>
</div>

<script>
function calculatorApp() {
    return {
        // Configuration
        deviceId: '',
        financingType: 'smartpay',
        agreementCredit: 0,
        upfrontPayment: 0,
        downPayment: 0,
        mobileInternetId: '',
        addOns: [],

        // Plan Selection
        selectedPlans: ['', '', '', ''],

        // Results
        calculations: [],
        bestValue: null,
        lowestMonthly: null,
        lowestTotal: null,
        loading: false,

        // Saved Comparisons
        savedComparisons: @json($savedComparisons),
        showSaved: false,

        init() {
            console.log('Calculator initialized');
        },

        async calculate() {
            const plans = this.selectedPlans.filter(p => p);
            if (plans.length === 0) {
                alert('Please select at least one plan');
                return;
            }

            this.loading = true;

            try {
                const response = await fetch('{{ route("calculator.calculate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        plans: plans,
                        device_id: this.deviceId || null,
                        financing_type: this.deviceId ? this.financingType : 'byod',
                        agreement_credit: this.agreementCredit,
                        upfront_payment: this.upfrontPayment,
                        down_payment: this.downPayment,
                        mobile_internet_id: this.mobileInternetId || null,
                        add_ons: this.addOns
                    })
                });

                const data = await response.json();
                this.calculations = data.calculations;
                this.bestValue = data.best_value;
                this.lowestMonthly = data.lowest_monthly;
                this.lowestTotal = data.lowest_total;
            } catch (error) {
                console.error('Calculation error:', error);
                alert('Error calculating comparison. Please try again.');
            } finally {
                this.loading = false;
            }
        },

        updateCalculations() {
            if (this.calculations.length > 0) {
                this.calculate();
            }
        },

        getSavingsClass(calc) {
            const maxCost = Math.max(...this.calculations.map(c => c.costs.total_24_months));
            const savings = maxCost - calc.costs.total_24_months;
            return savings > 0 ? 'text-green-700' : 'text-gray-500';
        },

        getSavingsText(calc) {
            const maxCost = Math.max(...this.calculations.map(c => c.costs.total_24_months));
            const savings = maxCost - calc.costs.total_24_months;
            return savings > 0 ? '-$' + savings.toFixed(2) : '$0.00';
        },

        async saveComparison() {
            const name = prompt('Enter a name for this comparison:');
            if (!name) return;

            const notes = prompt('Optional notes about this comparison:');

            try {
                const response = await fetch('{{ route("calculator.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: name,
                        notes: notes,
                        comparison_data: {
                            device_id: this.deviceId,
                            financing_type: this.financingType,
                            agreement_credit: this.agreementCredit,
                            upfront_payment: this.upfrontPayment,
                            down_payment: this.downPayment,
                            mobile_internet_id: this.mobileInternetId,
                            add_ons: this.addOns,
                            selected_plans: this.selectedPlans,
                            calculations: this.calculations
                        },
                        lowest_monthly_cost: this.lowestMonthly.costs.avg_monthly,
                        lowest_total_cost: this.lowestTotal.costs.total_24_months,
                        plan_count: this.calculations.length
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Comparison saved successfully!');
                    this.savedComparisons.unshift(data.comparison);
                }
            } catch (error) {
                console.error('Save error:', error);
                alert('Error saving comparison. Please try again.');
            }
        },

        async loadComparison(id) {
            try {
                const response = await fetch(`/calculator/load/${id}`);
                const comparison = await response.json();

                const data = comparison.comparison_data;
                this.deviceId = data.device_id || '';
                this.financingType = data.financing_type || 'smartpay';
                this.agreementCredit = data.agreement_credit || 0;
                this.upfrontPayment = data.upfront_payment || 0;
                this.downPayment = data.down_payment || 0;
                this.mobileInternetId = data.mobile_internet_id || '';
                this.addOns = data.add_ons || [];
                this.selectedPlans = data.selected_plans || ['', '', '', ''];

                this.showSaved = false;
                await this.calculate();
            } catch (error) {
                console.error('Load error:', error);
                alert('Error loading comparison. Please try again.');
            }
        },

        async deleteComparison(id) {
            if (!confirm('Are you sure you want to delete this comparison?')) return;

            try {
                const response = await fetch(`/calculator/delete/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.savedComparisons = this.savedComparisons.filter(c => c.id !== id);
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('Error deleting comparison. Please try again.');
            }
        },

        async exportPdf() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("calculator.export-pdf") }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const calculationsInput = document.createElement('input');
            calculationsInput.type = 'hidden';
            calculationsInput.name = 'calculations';
            calculationsInput.value = JSON.stringify(this.calculations);
            form.appendChild(calculationsInput);

            const bestValueInput = document.createElement('input');
            bestValueInput.type = 'hidden';
            bestValueInput.name = 'best_value';
            bestValueInput.value = JSON.stringify(this.bestValue);
            form.appendChild(bestValueInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    }
}
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
@endsection
