@if($contract->ratePlan || $contract->mobileInternetPlan)
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cellular Pricing</h3>
        
        <div class="space-y-4">
            @if($contract->ratePlan)
                <div class="border-b border-gray-200 pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Rate Plan</h4>
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $contract->ratePlan->plan_type === 'byod' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ strtoupper($contract->ratePlan->plan_type) }}
                                </span>
                                @if($contract->ratePlan->tier)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $contract->ratePlan->tier }}
                                    </span>
                                @endif
								@if($contract->ratePlan->credit_eligible)
									<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" title="This plan is eligible for ${{ number_format($contract->ratePlan->credit_amount, 2) }} {{ $contract->ratePlan->credit_type }}">
										💰 {{ $contract->ratePlan->credit_type }} Eligible
									</span>
								@endif
                            </div>
                            <p class="text-sm text-gray-700">
                                <strong>{{ $contract->ratePlan->plan_name }}</strong>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">SOC: {{ $contract->ratePlan->soc_code }}</p>
                            
                            @if($contract->ratePlan->data_amount)
                                <div class="flex items-center text-xs text-gray-600 mt-2">
                                    <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    {{ $contract->ratePlan->data_amount }}
                                </div>
                            @endif
                            
                            @if($contract->ratePlan->is_international || $contract->ratePlan->is_us_mexico)
                                <div class="flex items-center space-x-2 mt-2">
                                    @if($contract->ratePlan->is_international)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            International
                                        </span>
                                    @endif
                                    @if($contract->ratePlan->is_us_mexico)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            US/Mexico
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            <a href="{{ route('cellular-pricing.rate-plan-show', $contract->ratePlan->id) }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-500 mt-2 inline-block">
                                View plan details →
                            </a>
                        </div>
                        <div class="text-right ml-4">
                            <div class="text-2xl font-bold text-gray-900">
                                ${{ number_format($contract->rate_plan_price, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">per month</div>
                            
                            @if($contract->ratePlan->effective_price != $contract->rate_plan_price)
                                <div class="mt-2 text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded">
                                    Current: ${{ number_format($contract->ratePlan->effective_price, 2) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            
            @if($contract->mobileInternetPlan)
                <div class="border-b border-gray-200 pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Mobile Internet Plan</h4>
                            <p class="text-sm text-gray-700">
                                <strong>{{ $contract->mobileInternetPlan->plan_name }}</strong>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">SOC: {{ $contract->mobileInternetPlan->soc_code }}</p>
                            
                            @if($contract->mobileInternetPlan->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2">
                                    {{ $contract->mobileInternetPlan->category }}
                                </span>
                            @endif
                        </div>
                        <div class="text-right ml-4">
                            <div class="text-2xl font-bold text-gray-900">
                                ${{ number_format($contract->mobile_internet_price, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">per month</div>
                        </div>
                    </div>
                </div>
            @endif
            
            @if($contract->selected_tier)
                <div class="pb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-1">Selected Device Tier</h4>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        {{ $contract->selected_tier }}
                    </span>
                </div>
            @endif
        </div>
    </div>
@endif