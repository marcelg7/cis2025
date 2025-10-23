<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cellular Pricing</h3>
   
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
									data-plan-type="byod"
									data-soc="{{ $plan->soc ?? 'N/A' }}"
									data-credit-eligible="{{ $plan->credit_eligible ? 'true' : 'false' }}"
									data-credit-amount="{{ $plan->credit_amount ?? 0 }}"
									data-credit-type="{{ $plan->credit_type ?? 'Credit' }}">
								{{ $plan->plan_name }} - ${{ number_format($plan->effective_price, 2) }}/mo
								@if($plan->has_promo) ⚡ @endif
								@if($plan->credit_eligible) 💰 @endif
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
									data-plan-type="smartpay"
									data-soc="{{ $plan->soc ?? 'N/A' }}"
									data-credit-eligible="{{ $plan->credit_eligible ? 'true' : 'false' }}"
									data-credit-amount="{{ $plan->credit_amount ?? 0 }}"
									data-credit-type="{{ $plan->credit_type ?? 'Credit' }}">
								{{ $plan->plan_name }} - ${{ number_format($plan->effective_price, 2) }}/mo
								@if($plan->has_promo) ⚡ @endif
								@if($plan->credit_eligible) 💰 @endif
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
        </div>

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
            <div class="mt-4 flex items-center space-x-2">
                <button type="button"
                        id="apply_plan_btn"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                    Apply to Contract
                </button>
                <button type="button"
                        id="add_hay_credit_btn"
                        class="inline-flex items-center px-4 py-2 border border-green-600 text-sm font-medium rounded-md shadow-sm text-green-700 bg-white hover:bg-green-50"
                        style="display: none;">
                    💰 Add Hay Credit
                </button>
            </div>
        </div>

        <div id="applied_plans_summary" class="md:col-span-2" style="display: none;">
            <div class="bg-white border border-gray-300 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Applied Plans</h4>
               
                <div id="applied_rate_plan" class="mb-3 p-3 bg-green-50 border border-green-200 rounded" style="display: none;">
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
    const creditEligible = selectedOption.dataset.creditEligible === 'true';
    const creditAmount = parseFloat(selectedOption.dataset.creditAmount) || 0;
    const creditType = selectedOption.dataset.creditType || 'Credit';
  
    currentSelectedPlan = {
        type: planType,
        id: planId,
        price: price,
        name: selectedOption.textContent.trim(),
        tier: selectedOption.dataset.tier || null,
        hasPromo: selectedOption.dataset.hasPromo === 'true',
        basePrice: selectedOption.dataset.basePrice ? parseFloat(selectedOption.dataset.basePrice) : null,
        promoPrice: selectedOption.dataset.promoPrice ? parseFloat(selectedOption.dataset.promoPrice) : null,
        plan_type: selectedOption.dataset.planType,
        creditEligible: creditEligible,
        creditAmount: creditAmount,
        creditType: creditType,
    };
  
    document.getElementById('plan_display_name').textContent = currentSelectedPlan.name;
    document.getElementById('plan_display_price').textContent = '$' + price.toFixed(2);
  
    let features = '';
    if (currentSelectedPlan.tier) {
        features += `<span class="inline-block px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs mr-2">Tier: ${currentSelectedPlan.tier}</span>`;
    }
    if (currentSelectedPlan.hasPromo) {
        features += `<span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs mr-2">⚡ Promo Price</span>`;
    }
    if (currentSelectedPlan.creditEligible) {
        features += `<span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-xs mr-2">💰 ${creditType} Eligible ($${creditAmount.toFixed(2)})</span>`;
    }
    document.getElementById('plan_display_features').innerHTML = features;
  
    const addCreditBtn = document.getElementById('add_hay_credit_btn');
    if (currentSelectedPlan && currentSelectedPlan.creditEligible) {
        addCreditBtn.style.display = 'inline-flex';
        addCreditBtn.textContent = `💰 Add ${creditType}`;
    } else {
        addCreditBtn.style.display = 'none';
    }
  
    document.getElementById('plan_details_display').style.display = 'block';
});

document.getElementById('add_hay_credit_btn').addEventListener('click', function() {
    if (!currentSelectedPlan || !currentSelectedPlan.creditEligible) return;
    
    addCreditAsAddon(currentSelectedPlan.creditAmount, currentSelectedPlan.creditType);
});

function addCreditAsAddon(amount, creditType) {
    // Check if credit already exists
    const existingAddons = document.querySelectorAll('[name^="add_ons["][name$="[name]"]');
    for (let addon of existingAddons) {
        if (addon.value.toLowerCase().includes('credit')) {
            alert(`A credit (${addon.value}) has already been added. Please remove it first if you want to add a different credit.`);
            return;
        }
    }
    
    const div = document.createElement('div');
    div.className = 'add-on grid grid-cols-1 sm:grid-cols-3 gap-4 items-end';
    div.innerHTML = `
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text"
                   name="add_ons[${addOnCount}][name]"
                   value="${creditType}"
                   class="addon-name-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-green-50"
                   readonly>
            <input type="hidden"
                   name="add_ons[${addOnCount}][code]"
                   value="CREDIT"
                   class="addon-code-input">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Cost ($)</label>
            <input type="number"
                   name="add_ons[${addOnCount}][cost]"
                   step="0.01"
                   value="-${amount.toFixed(2)}"
                   class="addon-cost-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm total-input bg-green-50">
        </div>
        <div>
            <button type="button" onclick="removeAddOn(this)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700">
                Remove
            </button>
        </div>
    `;
    document.getElementById('add-ons').appendChild(div);
    addOnCount++;
    
    if (typeof calculateTotal === 'function') calculateTotal();
    
    const btn = document.getElementById('add_hay_credit_btn');
    btn.textContent = `✓ ${creditType} Added`;
    btn.classList.add('bg-gray-100');
    btn.disabled = true;
    setTimeout(() => {
        btn.textContent = `💰 Add ${creditType}`;
        btn.classList.remove('bg-gray-100');
        btn.disabled = false;
    }, 2000);
}

document.getElementById('apply_plan_btn').addEventListener('click', function() {
    if (!currentSelectedPlan) return;
  
    if (currentSelectedPlan.type === 'rate_plan') {
        document.getElementById('rate_plan_id').value = currentSelectedPlan.id;
        document.getElementById('rate_plan_price').value = currentSelectedPlan.price;
        document.getElementById('selected_tier').value = currentSelectedPlan.tier || '';
      
        document.getElementById('applied_rate_plan_name').textContent = currentSelectedPlan.name;
        document.getElementById('applied_rate_plan_tier').textContent = currentSelectedPlan.tier ? `Tier: ${currentSelectedPlan.tier}` : '';
        document.getElementById('applied_rate_plan_price').textContent = '$' + currentSelectedPlan.price.toFixed(2);
        document.getElementById('applied_rate_plan').style.display = 'block';
      
        const bellTierField = document.getElementById('hidden_bell_tier');
        if (bellTierField && currentSelectedPlan.tier) {
            bellTierField.value = currentSelectedPlan.tier;
        }
      
        const bellPlanCostField = document.getElementById('hidden_bell_plan_cost');
        if (bellPlanCostField) {
            bellPlanCostField.value = currentSelectedPlan.price;
        }

        // Debug logging for rate plan selection
        console.log('✓ Rate Plan Applied:');
        console.log('  Plan Name:', currentSelectedPlan.name);
        console.log('  Plan ID:', currentSelectedPlan.id);
        console.log('  Price:', '$' + currentSelectedPlan.price.toFixed(2));
        console.log('  Tier:', currentSelectedPlan.tier || 'N/A');
        console.log('  SOC (from select):', document.querySelector('#cellular_plan_selector option:checked')?.dataset.soc || 'Not found');

    } else if (currentSelectedPlan.type === 'mobile_internet') {
        document.getElementById('mobile_internet_plan_id').value = currentSelectedPlan.id;
        document.getElementById('mobile_internet_price').value = currentSelectedPlan.price;
      
        document.getElementById('applied_mobile_internet_name').textContent = currentSelectedPlan.name;
        document.getElementById('applied_mobile_internet_price').textContent = '$' + currentSelectedPlan.price.toFixed(2);
        document.getElementById('applied_mobile_internet').style.display = 'block';
    }
  
    document.getElementById('applied_plans_summary').style.display = 'block';
    updateTotalCellularCost();

    document.getElementById('cellular_plan_selector').value = '';
    document.getElementById('plan_details_display').style.display = 'none';
    currentSelectedPlan = null;

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
  
    const ratePlanVisible = document.getElementById('applied_rate_plan').style.display !== 'none';
    const mobileInternetVisible = document.getElementById('applied_mobile_internet').style.display !== 'none';
  
    if (!ratePlanVisible && !mobileInternetVisible) {
        document.getElementById('applied_plans_summary').style.display = 'none';
    }
  
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
	
    // Check if credit already exists and disable button
    function checkExistingCredit() {
        const addCreditBtn = document.getElementById('add_hay_credit_btn');
        if (!addCreditBtn) return;
        
        const existingAddons = document.querySelectorAll('[name^="add_ons["][name$="[name]"]');
        let creditExists = false;
        
        for (let addon of existingAddons) {
            if (addon.value.toLowerCase().includes('credit')) {
                creditExists = true;
                break;
            }
        }
        
        if (creditExists) {
            addCreditBtn.disabled = true;
            addCreditBtn.classList.add('opacity-50', 'cursor-not-allowed');
            addCreditBtn.title = 'A credit has already been added';
        } else {
            addCreditBtn.disabled = false;
            addCreditBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            addCreditBtn.title = '';
        }
    }	
	
	    // Check on page load
    setTimeout(checkExistingCredit, 100);
    
    // Check whenever addons change
    const addOnsContainer = document.getElementById('add-ons');
    if (addOnsContainer) {
        const observer = new MutationObserver(checkExistingCredit);
        observer.observe(addOnsContainer, { childList: true, subtree: true });
    }
	

	
});
</script>