<!-- resources/views/components/contract-card.blade.php -->
@props(['contract'])

<div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}
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
            <p><span class="font-medium">Date:</span> {{ $contract->contract_date->format('M d, Y') }}</p>
            <p><span class="font-medium">Plan:</span> {{ $contract->plan->name }}</p>
            
            @if($contract->device)
                <p class="mt-1"><span class="font-medium">Device:</span> {{ $contract->device->manufacturer }} {{ $contract->device->model }}</p>
            @endif
        </div>
        
        <div class="mt-4 pt-3 border-t border-gray-200 grid grid-cols-2 gap-3">
            <x-info-link href="{{ route('contracts.view', $contract->id) }}" class="justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                View
            </x-info-link>
            
            <x-primary-link href="{{ route('contracts.download', $contract->id) }}" class="justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download
            </x-primary-link>
            
            <form action="{{ route('contracts.email', $contract->id) }}" method="POST" class="w-full">
                @csrf
                <x-primary-button type="submit" class="w-full justify-center bg-indigo-100 text-indigo-700 hover:bg-indigo-600 hover:text-white">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email
                </x-primary-button>
            </form>
            
            <form action="{{ route('contracts.ftp', $contract->id) }}" method="POST" class="w-full">
                @csrf
                <x-primary-button type="submit" class="w-full justify-center bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    Vault
                </x-primary-button>
            </form>
        </div>
    </div>
</div>