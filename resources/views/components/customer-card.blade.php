<!-- resources/views/components/customer-card.blade.php -->
@props(['customer'])

<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200" x-data="{ refreshing: false }">
    <div class="px-4 py-4">
	    <div class="mt-3">
            <x-primary-link href="{{ route('customers.show', $customer->id) }}" class="text-xs px-2 py-1 w-full justify-center">
                View Customer
            </x-primary-link>
        </div>
        <div class="flex justify-between items-center">
            <h3 class="font-medium text-gray-900">
                {{ $customer->display_name }}
            </h3>
            <span class="text-xs text-gray-500">
                {{ $customer->last_fetched_at->diffForHumans() }}
            </span>
        </div>
        <div class="text-sm text-gray-600 mt-1 flex items-center">
            <span>{{ $customer->ivue_customer_number }}</span>
            <form method="POST" action="{{ route('customers.fetch') }}" class="inline-flex ml-1" x-ref="refreshForm">
                @csrf
                <input type="hidden" name="customer_number" value="{{ $customer->ivue_customer_number }}">
                <button
                    type="submit"
                    @click.prevent="refreshing = true; $refs.refreshForm.submit();"
                    :disabled="refreshing"
                    class="text-gray-400 hover:text-indigo-600 focus:outline-none disabled:opacity-50"
                    title="Refresh customer data from NISC"
                >
                    <svg
                        :class="refreshing ? 'animate-spin' : ''"
                        class="w-3 h-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </form>
        </div>
        @if($customer->email)
            <p class="text-xs text-gray-500 truncate">{{ $customer->email }}</p>
        @endif
    </div>
</div>