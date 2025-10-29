@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">

		<div class="mb-6">
			<h1 class="text-2xl font-semibold text-gray-900">Search Results for "{{ $query }}"</h1>
			@if($totalResults > 0)
				<p class="text-sm text-gray-600 mt-1">Found {{ $totalResults }} {{ Str::plural('result', $totalResults) }}</p>
			@else
				<p class="text-sm text-gray-600 mt-1">No results found</p>
			@endif

			<!-- Filters -->
			<div class="mt-4 bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
				<form action="{{ route('search') }}" method="GET" id="search-filters-form">
					<input type="hidden" name="query" value="{{ $query }}">
					<input type="hidden" name="submitted" value="1">

					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
						<!-- Category Filter -->
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
							<select name="category" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
								<option value="">All Categories</option>
								<option value="customers" {{ $category === 'customers' ? 'selected' : '' }}>Customers</option>
								<option value="subscribers" {{ $category === 'subscribers' ? 'selected' : '' }}>Subscribers</option>
								<option value="contracts" {{ $category === 'contracts' ? 'selected' : '' }}>Contracts</option>
								<option value="devices" {{ $category === 'devices' ? 'selected' : '' }}>Devices</option>
								<option value="plans" {{ $category === 'plans' ? 'selected' : '' }}>Plans</option>
								@hasrole('admin')
									<option value="admin" {{ $category === 'admin' ? 'selected' : '' }}>Admin Items</option>
								@endhasrole
							</select>
						</div>

						<!-- Contract Status Filter -->
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1">Contract Status</label>
							<select name="contract_status" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
								<option value="">All Statuses</option>
								<option value="draft" {{ $contractStatus === 'draft' ? 'selected' : '' }}>Draft</option>
								<option value="signed" {{ $contractStatus === 'signed' ? 'selected' : '' }}>Signed</option>
								<option value="finalized" {{ $contractStatus === 'finalized' ? 'selected' : '' }}>Finalized</option>
							</select>
						</div>

						<!-- Date From Filter -->
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
							<input type="date" name="date_from" value="{{ $dateFrom }}" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
						</div>

						<!-- Date To Filter -->
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
							<input type="date" name="date_to" value="{{ $dateTo }}" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
						</div>
					</div>

					<!-- Test Data Toggle and Reset Filters -->
					<div class="mt-4 flex items-center justify-between">
						<label class="flex items-center cursor-pointer">
							<input type="checkbox"
								   name="include_test"
								   value="1"
								   {{ $includeTest ? 'checked' : '' }}
								   onchange="this.form.submit()"
								   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
							<span class="ml-2 text-sm text-gray-700">Include test data</span>
						</label>

						@if($category || $contractStatus || $dateFrom || $dateTo || !$includeTest)
							<a href="{{ route('search', ['query' => $query, 'submitted' => 1]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
								Reset Filters
							</a>
						@endif
					</div>
				</form>
			</div>

			
			<!-- Search Tips -->
			<div class="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
				<p class="text-xs text-blue-800">
					<strong>Search Tips:</strong> 
					You can search by customer name, phone number (any format), contract ID, email, address, device name, or plan name.
				</p>
			</div>
		</div>
        @if(strlen($query ?? '') < 2)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800">Please enter at least 2 characters to search.</p>
            </div>
        @elseif($totalResults === 0)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
                <p class="mt-1 text-sm text-gray-500">Try searching with different keywords</p>
            </div>
        @else
            <div class="mt-6 space-y-6">
                
                <!-- Customers -->
                @if($results['customers']->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-3">Customers ({{ $results['customers']->count() }})</h2>
                        <ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
                            @foreach($results['customers'] as $customer)
                                <li class="px-6 py-4 hover:bg-gray-50">
                                    <a href="{{ route('customers.show', $customer->id) }}" class="block">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $customer->display_name }}</p>
                                                <p class="text-sm text-gray-600">Account: {{ $customer->ivue_customer_number }}</p>
                                                @if($customer->email)
                                                    <p class="text-xs text-gray-500">{{ $customer->email }}</p>
                                                @endif
                                                @if($customer->address)
                                                    <p class="text-xs text-gray-500">{{ $customer->address }}, {{ $customer->city }}</p>
                                                @endif
                                            </div>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Subscribers -->
                @if($results['subscribers']->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-3">Subscribers ({{ $results['subscribers']->count() }})</h2>
                        <ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
                            @foreach($results['subscribers'] as $subscriber)
                                <li class="px-6 py-4 hover:bg-gray-50">
                                    <a href="{{ route('customers.show', $subscriber->mobilityAccount->ivueAccount->customer->id) }}" class="block">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $subscriber->mobile_number }}</p>
                                                <p class="text-sm text-gray-600">{{ $subscriber->first_name }} {{ $subscriber->last_name }}</p>
                                                <p class="text-xs text-gray-500">Customer: {{ $subscriber->mobilityAccount->ivueAccount->customer->display_name }}</p>
                                            </div>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

				<!-- Contracts -->
				@if($results['contracts']->isNotEmpty())
					<div>
						<h2 class="text-lg font-medium text-gray-900 mb-3">Contracts ({{ $results['contracts']->count() }})</h2>
						<ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
							@foreach($results['contracts'] as $contract)
								<li class="px-6 py-4 hover:bg-gray-50">
									<a href="{{ route('contracts.view', $contract->id) }}" class="block">
										<div class="flex items-center justify-between">
											<div class="flex-1">
												<div class="flex items-center gap-2">
													<p class="text-sm font-medium text-indigo-600">Contract #{{ $contract->id }}</p>
													<span class="px-2 py-0.5 text-xs font-semibold rounded-full 
														{{ $contract->status === 'finalized' ? 'bg-green-100 text-green-800' : '' }}
														{{ $contract->status === 'signed' ? 'bg-blue-100 text-blue-800' : '' }}
														{{ $contract->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}">
														{{ ucfirst($contract->status) }}
													</span>
												</div>
												<p class="text-sm text-gray-600 mt-1">
													<span class="font-medium">{{ $contract->subscriber->mobile_number }}</span> - 
													{{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}
												</p>
												<p class="text-xs text-gray-500 mt-1">
													Customer: {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name }} • 
													Date: {{ $contract->contract_date->format('M d, Y') }}
												</p>
												@if($contract->bellDevice)
													<p class="text-xs text-gray-500">Device: {{ $contract->bellDevice->name }}</p>
												@endif
											</div>
											<svg class="h-5 w-5 text-gray-400 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
											</svg>
										</div>
									</a>
								</li>
							@endforeach
						</ul>
					</div>
				@endif

                <!-- Bell Devices -->
                @if($results['bell_devices']->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-3">Bell Devices ({{ $results['bell_devices']->count() }})</h2>
                        <ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
                            @foreach($results['bell_devices'] as $device)
                                <li class="px-6 py-4 hover:bg-gray-50">
                                    <a href="{{ route('bell-pricing.show', $device->id) }}" class="block">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $device->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $device->manufacturer }} {{ $device->model }}</p>
                                            </div>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Rate Plans -->
                @if($results['rate_plans']->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-3">Rate Plans ({{ $results['rate_plans']->count() }})</h2>
                        <ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
                            @foreach($results['rate_plans'] as $plan)
                                <li class="px-6 py-4 hover:bg-gray-50">
                                    <a href="{{ route('cellular-pricing.rate-plan-show', $plan->id) }}" class="block">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $plan->plan_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $plan->soc_code }} - ${{ number_format($plan->base_price, 2) }}/mo</p>
                                            </div>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Mobile Internet Plans -->
                @if($results['mobile_internet_plans']->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-3">Mobile Internet Plans ({{ $results['mobile_internet_plans']->count() }})</h2>
                        <ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
                            @foreach($results['mobile_internet_plans'] as $plan)
                                <li class="px-6 py-4 hover:bg-gray-50">
                                    <a href="{{ route('cellular-pricing.mobile-internet.show', $plan->id) }}" class="block">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $plan->plan_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $plan->soc_code }} - ${{ number_format($plan->monthly_rate, 2) }}/mo</p>
                                            </div>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Users (Admin Only) -->
                @if($results['users']->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-3">Users ({{ $results['users']->count() }})</h2>
                        <ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
                            @foreach($results['users'] as $user)
                                <li class="px-6 py-4 hover:bg-gray-50">
                                    <a href="{{ route('users.edit', $user->id) }}" class="block">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                            </div>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Activity Types (Admin Only) -->
                @if($results['activity_types']->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-3">Activity Types ({{ $results['activity_types']->count() }})</h2>
                        <ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
                            @foreach($results['activity_types'] as $activityType)
                                <li class="px-6 py-4 hover:bg-gray-50">
                                    <a href="{{ route('activity-types.edit', $activityType->id) }}" class="block">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $activityType->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $activityType->is_active ? 'Active' : 'Inactive' }}</p>
                                            </div>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Commitment Periods (Admin Only) -->
                @if($results['commitment_periods']->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-3">Commitment Periods ({{ $results['commitment_periods']->count() }})</h2>
                        <ul class="divide-y divide-gray-200 bg-white shadow rounded-lg">
                            @foreach($results['commitment_periods'] as $period)
                                <li class="px-6 py-4 hover:bg-gray-50">
                                    <a href="{{ route('commitment-periods.edit', $period->id) }}" class="block">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $period->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $period->is_active ? 'Active' : 'Inactive' }}</p>
                                            </div>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Customers
            </a>
        </div>
    </div>
@endsection