<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cellular Pricing</h3>
   
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Universal Plan Selection -->
        <div class="md:col-span-2">
            <label for="cellular_plan_selector" class="block text-sm font-medium text-gray-700">Select Cellular Plan</label>
            <select id="cellular_plan_selector"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">-- Select a Plan --</option>
               
                @php
                    $byodPlans = $ratePlans->where('plan_type', 'byod');
                    $smartPayPlans = $ratePlans->where('plan_type', 'smartpay');
                @endphp
               
                @if($byodPlans->count() > 0)
                    <optgroup label="BYOD Rate Plans">
                        @foreach($byodPlans as $plan)
                            <option value="rate_plan_{{ $plan->id }}"
                                    data-type="rate_plan"
                                    data-id="{{ $plan->id }}"
                                    data-price="{{ $plan->effective_price }}"
                                    data-has-promo="{{ $plan->has_promo ? 'true' : 'false' }}"
                                    data-base-price="{{ $plan->base_price }}"
                                    data-promo-price="{{ $plan->promo_price }}"
                                    data-tier="{{ $plan->tier }}"
                                    data-plan-type="byod">
                                {{ $plan->plan_name }} - ${{ number_format($plan->effective_price, 2) }}/mo
                                @if($plan->has_promo) ⚡ @endif
                            </option>
                        @endforeach
                    </optgroup>
                @endif
               
                @if($smartPayPlans->count() > 0)
                    <optgroup label="SmartPay Rate Plans">
                        @foreach($smartPayPlans as $plan)
                            <option value="rate_plan_{{ $plan->id }}"
                                    data-type="rate_plan"
                                    data-id="{{ $plan->id }}"
                                    data-price="{{ $plan->effective_price }}"
                                    data-has-promo="{{ $plan->has_promo ? 'true' : 'false' }}"
                                    data-base-price="{{ $plan->base_price }}"
                                    data-promo-price="{{ $plan->promo_price }}"
                                    data-tier="{{ $plan->tier }}"
                                    data-plan-type="smartpay">
                                {{ $plan->plan_name }} - ${{ number_format($plan->effective_price, 2) }}/mo
                                @if($plan->has_promo) ⚡ @endif
                            </option>
                        @endforeach
                    </optgroup>
                @endif
               
                @if($mobileInternetPlans->count() > 0)
                    <optgroup label="Mobile Internet Plans">
                        @foreach($mobileInternetPlans as $plan)
                            <option value="mobile_internet_{{ $plan->id }}"
                                    data-type="mobile_internet"
                                    data-id="{{ $plan->id }}"
                                    data-price="{{ $plan->monthly_rate }}">
                                {{ $plan->plan_name }} - ${{ number_format($plan->monthly_rate, 2) }}/mo
                            </option>
                        @endforeach
                    </optgroup>
                @endif
            </select>
           
            <p class="mt-1 text-xs text-gray-500">
                Select a cellular plan and click "Apply to Contract" below
            </p>
        </div>
        <!-- Plan Details Display -->
        <div id="plan_details_display" class="md:col-span-2 p-4 bg-blue-50 border border-blue-200 rounded-lg" style="display: none;">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2" id="plan_display_name"></h4>
                    <div id="plan_display_features" class="text-xs text-blue-800"></div>
                </div>
                <div class="text-right ml-4">
                    <div class="text-2xl font-bold text-blue-900" id="plan_display_price"></div>
                    <div class="text-xs text-blue-600">per month</div>
                </div>
            </div>
            <button type="button"
                    id="apply_plan_btn"
                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                Apply to Contract
            </button>
        </div>
        <!-- Applied Plans Summary -->
        <div id="applied_plans_summary" class="md:col-span-2" style="display: none;">
            <div class="bg-white border border-gray-300 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Applied Plans</h4>
               
                <div id="applied_rate_plan" class="mb-3 p-3 bg-green-50 border border-green-200 rounded" style="display: none;" data-plan-type="{{ $contract->ratePlan->plan_type ?? '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-green-900" id="applied_rate_plan_name"></div>
                            <div class="text-xs text-green-700 mt-1" id="applied_rate_plan_tier"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-900" id="applied_rate_plan_price"></div>
                            <button type="button" onclick="removePlan('rate')" class="text-xs text-red-600 hover:text-red-800 mt-1">Remove</button>
                        </div>
                    </div>
                </div>
               
                <div id="applied_mobile_internet" class="p-3 bg-blue-50 border border-blue-200 rounded" style="display: none;">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-blue-900" id="applied_mobile_internet_name"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-blue-900" id="applied_mobile_internet_price"></div>
                            <button type="button" onclick="removePlan('mobile')" class="text-xs text-red-600 hover:text-red-800 mt-1">Remove</button>
                        </div>
                    </div>
                </div>
               
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900">Total Monthly Cost:</span>
                        <span class="text-2xl font-bold text-indigo-600" id="total_cellular_cost">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hidden fields -->
    <input type="hidden" name="rate_plan_id" id="rate_plan_id" value="{{ old('rate_plan_id', isset($contract) ? $contract->rate_plan_id : '') }}">
    <input type="hidden" name="rate_plan_price" id="rate_plan_price" value="{{ old('rate_plan_price', isset($contract) ? $contract->rate_plan_price : '') }}">
    <input type="hidden" name="selected_tier" id="selected_tier" value="{{ old('selected_tier', isset($contract) ? $contract->selected_tier : '') }}">
    <input type="hidden" name="mobile_internet_plan_id" id="mobile_internet_plan_id" value="{{ old('mobile_internet_plan_id', isset($contract) ? $contract->mobile_internet_plan_id : '') }}">
    <input type="hidden" name="mobile_internet_price" id="mobile_internet_price" value="{{ old('mobile_internet_price', isset($contract) ? $contract->mobile_internet_price : '') }}">
</div>
<script>
let currentSelectedPlan = null;
document.getElementById('cellular_plan_selector').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
  
    if (!selectedOption.value) {
        document.getElementById('plan_details_display').style.display = 'none';
        currentSelectedPlan = null;
        return;
    }
  
    const planType = selectedOption.dataset.type;
    const planId = selectedOption.dataset.id;
    const price = parseFloat(selectedOption.dataset.price);
  
    currentSelectedPlan = {
        type: planType,
        id: planId,
        price: price,
        name: selectedOption.textContent.trim(),
        tier: selectedOption.dataset.tier || null,
        hasPromo: selectedOption.dataset.hasPromo === 'true',
        basePrice: selectedOption.dataset.basePrice ? parseFloat(selectedOption.dataset.basePrice) : null,
        promoPrice: selectedOption.dataset.promoPrice ? parseFloat(selectedOption.dataset.promoPrice) : null,
        plan_type: selectedOption.dataset.planType, // Added for BYOD check
    };
  
    // Show plan details
    document.getElementById('plan_display_name').textContent = currentSelectedPlan.name;
    document.getElementById('plan_display_price').textContent = '$' + price.toFixed(2);
  
    let features = '';
    if (currentSelectedPlan.tier) {
        features += `<span class="inline-block px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs mr-2">Tier: ${currentSelectedPlan.tier}</span>`;
    }
    if (currentSelectedPlan.hasPromo) {
        features += `<span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">⚡ Promo Price</span>`;
    }
    document.getElementById('plan_display_features').innerHTML = features;
  
    document.getElementById('plan_details_display').style.display = 'block';
});
document.getElementById('apply_plan_btn').addEventListener('click', function() {
    if (!currentSelectedPlan) return;
  
    if (currentSelectedPlan.type === 'rate_plan') {
        // Apply rate plan
        document.getElementById('rate_plan_id').value = currentSelectedPlan.id;
        document.getElementById('rate_plan_price').value = currentSelectedPlan.price;
        document.getElementById('selected_tier').value = currentSelectedPlan.tier || '';
      
        // Update display
        document.getElementById('applied_rate_plan_name').textContent = currentSelectedPlan.name;
        document.getElementById('applied_rate_plan_tier').textContent = currentSelectedPlan.tier ? `Tier: ${currentSelectedPlan.tier}` : '';
        document.getElementById('applied_rate_plan_price').textContent = '$' + currentSelectedPlan.price.toFixed(2);
        document.getElementById('applied_rate_plan').style.display = 'block';
      
        // Update bell_tier hidden field if it exists
        const bellTierField = document.getElementById('hidden_bell_tier');
        if (bellTierField && currentSelectedPlan.tier) {
            bellTierField.value = currentSelectedPlan.tier;
        }
      
        // Update bell_plan_cost hidden field if it exists
        const bellPlanCostField = document.getElementById('hidden_bell_plan_cost');
        if (bellPlanCostField) {
            bellPlanCostField.value = currentSelectedPlan.price;
        }
      
    } else if (currentSelectedPlan.type === 'mobile_internet') {
        // Apply mobile internet plan
        document.getElementById('mobile_internet_plan_id').value = currentSelectedPlan.id;
        document.getElementById('mobile_internet_price').value = currentSelectedPlan.price;
      
        // Update display
        document.getElementById('applied_mobile_internet_name').textContent = currentSelectedPlan.name;
        document.getElementById('applied_mobile_internet_price').textContent = '$' + currentSelectedPlan.price.toFixed(2);
        document.getElementById('applied_mobile_internet').style.display = 'block';
    }
  
    // Show summary
    document.getElementById('applied_plans_summary').style.display = 'block';
  
    // Update total
    updateTotalCellularCost();
  
    // Clear selection
    document.getElementById('cellular_plan_selector').value = '';
    document.getElementById('plan_details_display').style.display = 'none';
    currentSelectedPlan = null;
  
    // Show success message
    this.textContent = '✓ Applied';
    this.classList.remove('bg-green-600', 'hover:bg-green-700');
    this.classList.add('bg-gray-400');
    setTimeout(() => {
        this.textContent = 'Apply to Contract';
        this.classList.remove('bg-gray-400');
        this.classList.add('bg-green-600', 'hover:bg-green-700');
    }, 1500);
  
    // Recalculate contract total if function exists
    if (typeof calculateTotal === 'function') {
        calculateTotal();
    }
});
function removePlan(type) {
    if (type === 'rate') {
        document.getElementById('rate_plan_id').value = '';
        document.getElementById('rate_plan_price').value = '';
        document.getElementById('selected_tier').value = '';
        document.getElementById('applied_rate_plan').style.display = 'none';
      
        // Clear bell fields if they exist
        const bellTierField = document.getElementById('hidden_bell_tier');
        if (bellTierField) bellTierField.value = '';
        const bellPlanCostField = document.getElementById('hidden_bell_plan_cost');
        if (bellPlanCostField) bellPlanCostField.value = '';
      
    } else if (type === 'mobile') {
        document.getElementById('mobile_internet_plan_id').value = '';
        document.getElementById('mobile_internet_price').value = '';
        document.getElementById('applied_mobile_internet').style.display = 'none';
    }
  
    updateTotalCellularCost();
  
    // Hide summary if no plans applied
    const ratePlanVisible = document.getElementById('applied_rate_plan').style.display !== 'none';
    const mobileInternetVisible = document.getElementById('applied_mobile_internet').style.display !== 'none';
  
    if (!ratePlanVisible && !mobileInternetVisible) {
        document.getElementById('applied_plans_summary').style.display = 'none';
    }
  
    // Recalculate contract total if function exists
    if (typeof calculateTotal === 'function') {
        calculateTotal();
    }
}
function updateTotalCellularCost() {
    const ratePlanPrice = parseFloat(document.getElementById('rate_plan_price').value) || 0;
    const mobileInternetPrice = parseFloat(document.getElementById('mobile_internet_price').value) || 0;
    const total = ratePlanPrice + mobileInternetPrice;
  
    document.getElementById('total_cellular_cost').textContent = '$' + total.toFixed(2);
}
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($contract) && ($contract->rate_plan_id || $contract->mobile_internet_plan_id))
        @if($contract->rate_plan_id)
            document.getElementById('applied_rate_plan_name').textContent = '{{ $contract->ratePlan->plan_name ?? '' }}';
            document.getElementById('applied_rate_plan_tier').textContent = '{{ $contract->ratePlan->tier ? "Tier: " . $contract->ratePlan->tier : "" }}';
            document.getElementById('applied_rate_plan_price').textContent = '${{ number_format($contract->rate_plan_price ?? 0, 2) }}';
            document.getElementById('applied_rate_plan').style.display = 'block';
        @endif
       
        @if($contract->mobile_internet_plan_id)
            document.getElementById('applied_mobile_internet_name').textContent = '{{ $contract->mobileInternetPlan->plan_name ?? '' }}';
            document.getElementById('applied_mobile_internet_price').textContent = '${{ number_format($contract->mobile_internet_price ?? 0, 2) }}';
            document.getElementById('applied_mobile_internet').style.display = 'block';
        @endif
       
        document.getElementById('applied_plans_summary').style.display = 'block';
        updateTotalCellularCost();
    @endif
});
</script>