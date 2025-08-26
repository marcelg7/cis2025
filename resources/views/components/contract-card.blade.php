@props(['contract'])
<div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                @if ($contract->subscriber && $contract->subscriber->mobilityAccount && $contract->subscriber->mobilityAccount->ivueAccount && $contract->subscriber->mobilityAccount->ivueAccount->customer)
                    <a href="{{ route('customers.show', $contract->subscriber->mobilityAccount->ivueAccount->customer->id) }}" class="hover:text-indigo-600 hover:underline inline-flex items-center space-x-1 px-0">
                        <span>{{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</span>
                        <x-icon-open class="ml-0" />
                    </a>
                @else
                    <span>{{ $contract->subscriber ? ($contract->subscriber->first_name . ' ' . $contract->subscriber->last_name) : 'N/A' }}</span>
                @endif
            </h3>
            <span class="px-2 py-1 text-xs rounded-full {{ $contract->end_date->isPast() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                {{ $contract->end_date->isPast() ? 'Expired' : 'Active' }}
            </span>
        </div>
        <div class="flex justify-between items-center mt-1">
            <p class="text-xs text-gray-500">Contract #{{ $contract->id }}</p>
            <p class="text-xs text-gray-500">Last updated: {{ $contract->updated_at->diffForHumans() }}</p>
        </div>
        <div class="mt-3 text-sm text-gray-500">
            <p><span class="font-medium">Phone:</span> {{ $contract->subscriber->mobile_number }}</p>
            <p><span class="font-medium">Mobility:</span> {{ $contract->subscriber->mobilityAccount->mobility_account }}</p>
            <p><span class="font-medium">IVUE:</span> {{ $contract->subscriber->mobilityAccount->ivueAccount->ivue_account }}</p>
            <p><span class="font-medium">Start Date:</span> {{ $contract->start_date->format('M d, Y') }}</p>
            <p><span class="font-medium">First Bill Date:</span> {{ $contract->first_bill_date->format('M d, Y') }}</p>
            <p><span class="font-medium">Location:</span> {{ ucfirst($contract->location) }}</p>
            <p><span class="font-medium">Plan:</span> {{ $contract->plan->name }}</p>
            @if ($contract->manufacturer || $contract->model || $contract->version || $contract->device_storage || $contract->extra_info)
                <p class="mt-1">
                    <span class="font-medium">Device:</span>
                    {{ collect([
                        $contract->manufacturer ? ucfirst($contract->manufacturer) : null,
                        $contract->model ? ($contract->model === 'iphone' ? 'iPhone' : ucfirst($contract->model)) : null,
                        $contract->version,
                        $contract->device_storage ? str_replace('gb', 'GB', $contract->device_storage) : null,
                        $contract->extra_info ? ucfirst($contract->extra_info) : null,
                    ])->filter()->implode(' ') }}
                </p>
            @endif
        </div>
		<div class="mt-4 pt-3 border-t border-gray-200 flex items-center space-x-2">
			<div class="flex-shrink-0">
				<a href="{{ route('contracts.view', $contract->id) }}" class="inline-flex justify-center items-center w-10 h-10 rounded-full bg-blue-100 hover:bg-blue-200 text-blue-800 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500" title="View">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
					</svg>
				</a>
			</div>
            @if ($contract->status === 'draft')
                <div class="flex-shrink-0">
                    <a href="{{ route('contracts.edit', $contract->id) }}" class="inline-flex justify-center items-center w-10 h-10 rounded-full bg-yellow-100 hover:bg-yellow-200 text-yellow-800 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-yellow-500" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="w-10 h-10"></div>
            @endif
            <div class="flex-shrink-0">
                <a href="{{ route('contracts.download', $contract->id) }}" class="inline-flex justify-center items-center w-10 h-10 rounded-full bg-green-100 hover:bg-green-200 text-green-800 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-green-500 {{ $contract->status !== 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}" title="Download">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </a>
            </div>
<div class="flex-shrink-0">
    <form action="{{ route('contracts.email', $contract->id) }}" method="POST">
        @csrf
        <button type="submit" class="contract-card-button inline-flex justify-center items-center w-10 h-10 rounded-full bg-blue-100 hover:bg-blue-200 text-blue-800 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 {{ $contract->status !== 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}" title="Email">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </button>
    </form>
</div>
<div class="flex-shrink-0">
    <form action="{{ route('contracts.ftp', $contract->id) }}" method="POST">
        @csrf
        <button type="submit" class="contract-card-button inline-flex justify-center items-center w-10 h-10 rounded-full bg-purple-100 hover:bg-purple-200 text-purple-800 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-purple-500" title="Vault">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
        </button>
    </form>
</div>
        </div>
    </div>
</div>