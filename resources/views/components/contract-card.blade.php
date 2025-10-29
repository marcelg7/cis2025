@props(['contract'])

<div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 card-hover">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                @if ($contract->subscriber && $contract->subscriber->mobilityAccount && $contract->subscriber->mobilityAccount->ivueAccount && $contract->subscriber->mobilityAccount->ivueAccount->customer)
                    <a href="{{ route('customers.show', $contract->subscriber->mobilityAccount->ivueAccount->customer->id) }}" 
                       class="hover:underline inline-flex items-center space-x-1 px-0"
                       style="color: var(--color-primary);"
                       onmouseover="this.style.color='var(--color-primary-hover)'"
                       onmouseout="this.style.color='var(--color-primary)'">
                        <span>{{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</span>
                        <x-icon-open class="ml-0" />
                    </a>
                @else
                    <span>{{ $contract->subscriber ? ($contract->subscriber->first_name . ' ' . $contract->subscriber->last_name) : 'N/A' }}</span>
                @endif
            </h3>
            <span class="px-2 py-1 text-xs rounded-full {{ $contract->status === 'draft' ? 'bg-gray-100 text-gray-800' : ($contract->status === 'signed' ? 'bg-yellow-100 text-yellow-800' : ($contract->status === 'finalized' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                {{ ucfirst($contract->status) }}
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
            <p><span class="font-medium">Location:</span> {{ $contract->locationModel?->name ?? 'N/A' }}</p>
            <p><span class="font-medium">Plan:</span> {{ $contract->bell_tier ?? 'N/A' }} Tier</p>
            @if ($contract->bell_device_id && $contract->bellDevice)
                <p class="mt-1">
                    <span class="font-medium">Device:</span> {{ $contract->bellDevice->name ?? 'N/A' }}
                </p>
            @endif
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-4 pt-3 border-t border-gray-200 flex flex-wrap items-center gap-2">
            <!-- View Button - Always Available -->
            <div class="flex-shrink-0">
                <a href="{{ route('contracts.view', $contract->id) }}" 
                   class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                   style="background-color: var(--color-info); color: white;"
                   onmouseover="this.style.backgroundColor='#2563eb'"
                   onmouseout="this.style.backgroundColor='var(--color-info)'"
                   title="View Contract">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
            </div>
            
            <!-- Download Button - Only for Finalized and NOT uploaded to vault -->
            @if(!$contract->ftp_to_vault)
                <div class="flex-shrink-0">
                    <a href="{{ route('contracts.download', $contract->id) }}"
                       class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $contract->status !== 'finalized' ? 'cursor-not-allowed' : '' }}"
                       style="background-color: {{ $contract->status === 'finalized' ? 'var(--color-success)' : '#e5e7eb' }}; color: {{ $contract->status === 'finalized' ? 'white' : '#9ca3af' }};"
                       @if($contract->status === 'finalized')
                       onmouseover="this.style.backgroundColor='#059669'"
                       onmouseout="this.style.backgroundColor='var(--color-success)'"
                       @endif
                       title="{{ $contract->status === 'finalized' ? 'Download PDF' : 'Must be finalized to download' }}"
                       {{ $contract->status !== 'finalized' ? 'onclick="return false;"' : '' }}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </a>
                </div>

                <!-- Email Button - Only for Finalized and NOT uploaded to vault -->
                <div class="flex-shrink-0">
                    <form action="{{ route('contracts.email', $contract->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                {{ $contract->status !== 'finalized' ? 'disabled' : '' }}
                                class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $contract->status !== 'finalized' ? 'cursor-not-allowed' : '' }}"
                                style="background-color: {{ $contract->status === 'finalized' ? 'var(--color-info)' : '#e5e7eb' }}; color: {{ $contract->status === 'finalized' ? 'white' : '#9ca3af' }};"
                                @if($contract->status === 'finalized')
                                onmouseover="this.style.backgroundColor='#2563eb'"
                                onmouseout="this.style.backgroundColor='var(--color-info)'"
                                @endif
                                title="{{ $contract->status === 'finalized' ? 'Email Contract' : 'Must be finalized to email' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </form>
                </div>
            @endif
            
            <!-- Edit Button - Only for Draft -->
            @if ($contract->status === 'draft')
                <div class="flex-shrink-0">
                    <a href="{{ route('contracts.edit', $contract->id) }}" 
                       class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                       style="background-color: var(--color-warning); color: white;"
                       onmouseover="this.style.backgroundColor='#d97706'"
                       onmouseout="this.style.backgroundColor='var(--color-warning)'"
                       title="Edit Contract">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                </div>
                
                <div class="flex-shrink-0">
                    <a href="{{ route('contracts.sign', $contract->id) }}" 
                       class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                       style="background-color: var(--color-primary); color: white;"
                       onmouseover="this.style.backgroundColor='var(--color-primary-hover)'"
                       onmouseout="this.style.backgroundColor='var(--color-primary)'"
                       title="Sign Contract">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </a>
                </div>
            @endif
            
            <!-- Finalize Button - Only for Signed -->
            @if ($contract->status === 'signed')
                <div class="flex-shrink-0">
                    <form action="{{ route('contracts.finalize', $contract->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                                style="background-color: var(--color-success); color: white;"
                                onmouseover="this.style.backgroundColor='#059669'"
                                onmouseout="this.style.backgroundColor='var(--color-success)'"
                                title="Finalize Contract">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </form>
                </div>
            @endif
            
            <!-- Create Revision Button - Only for Finalized -->
            @if ($contract->status === 'finalized')
                <div class="flex-shrink-0">
                    <form action="{{ route('contracts.revision', $contract->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                                style="background-color: var(--color-primary); color: white;"
                                onmouseover="this.style.backgroundColor='var(--color-primary-hover)'"
                                onmouseout="this.style.backgroundColor='var(--color-primary)'"
                                title="Create Revision">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                            </svg>
                        </button>
                    </form>
                </div>
                
               <!-- FTP to Vault Button or Status -->
                @if($contract->ftp_to_vault)
                    <div class="flex-shrink-0">
                        <div class="inline-flex justify-center items-center w-10 h-10 rounded-full" 
                             style="background-color: var(--color-success); color: white;"
                             title="Uploaded to Vault {{ $contract->ftp_at->diffForHumans() }}">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                @else
                    <div class="flex-shrink-0">
                        <form action="{{ route('contracts.ftp', $contract->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex justify-center items-center w-10 h-10 rounded-full bg-purple-100 hover:bg-purple-200 text-purple-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-purple-500" 
                                    title="Upload to Vault">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endif
            @endif
            
            <!-- Financing Form Button - Only for contracts that require financing and NOT uploaded to vault -->
            @if($contract->requiresFinancing() && $contract->status !== 'draft' && !$contract->ftp_to_vault)
                @if($contract->financing_status === 'pending')
                    <div class="flex-shrink-0">
                        <a href="{{ route('contracts.financing.index', $contract->id) }}"
                           class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                           style="background-color: var(--color-warning); color: white;"
                           onmouseover="this.style.backgroundColor='#d97706'"
                           onmouseout="this.style.backgroundColor='var(--color-warning)'"
                           title="Financing Form Pending">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </a>
                    </div>
                @elseif($contract->financing_status === 'signed' || $contract->financing_status === 'customer_signed')
                    <div class="flex-shrink-0">
                        <a href="{{ route('contracts.financing.index', $contract->id) }}"
                           class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                           style="background-color: var(--color-info); color: white;"
                           onmouseover="this.style.backgroundColor='#2563eb'"
                           onmouseout="this.style.backgroundColor='var(--color-info)'"
                           title="Financing Form Signed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </a>
                    </div>
                @elseif($contract->financing_status === 'finalized')
                    <div class="flex-shrink-0">
                        <a href="{{ route('contracts.financing.index', $contract->id) }}"
                           class="inline-flex justify-center items-center w-10 h-10 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                           style="background-color: var(--color-success); color: white;"
                           onmouseover="this.style.backgroundColor='#059669'"
                           onmouseout="this.style.backgroundColor='var(--color-success)'"
                           title="Financing Form Finalized">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>