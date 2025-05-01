<!-- resources/views/components/customer-card.blade.php -->
@props(['customer'])

<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <div class="px-4 py-4">
        <div class="flex justify-between items-center">
            <h3 class="font-medium text-gray-900">
                {{ $customer->display_name }}
            </h3>
            <span class="text-xs text-gray-500">
                {{ $customer->last_fetched_at->diffForHumans() }}
            </span>
        </div>
        <p class="text-sm text-gray-600 mt-1">{{ $customer->ivue_customer_number }}</p>
        @if($customer->email)
            <p class="text-xs text-gray-500 truncate">{{ $customer->email }}</p>
        @endif
        <div class="mt-3">
            <x-primary-link href="{{ route('customers.show', $customer->id) }}" class="text-xs px-2 py-1 w-full justify-center">
                View Customer
            </x-primary-link>
        </div>
    </div>
</div>