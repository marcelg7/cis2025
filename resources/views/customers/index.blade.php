@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2" x-data="{
        searchModalOpen: false,
        searchQuery: { lastName: '', firstName: '', businessName: '', address: '' },
        searchResults: [],
        searching: false,
        searchError: '',
        customerInput: '{{ old('customer_number') }}',
        handleFormSubmit(event) {
            const input = this.customerInput.trim();

            // Check if input starts with 502 (customer number)
            if (input.startsWith('502')) {
                // Submit the form normally
                event.target.submit();
            } else {
                // It's a search term - open search modal and search
                this.searchModalOpen = true;

                // Clear previous search
                this.searchQuery = { lastName: '', firstName: '', businessName: '', address: '' };

                // Try to guess if it's first name, last name, or business name
                // If it contains spaces, assume it's a full name (first last)
                if (input.includes(' ')) {
                    const parts = input.split(' ');
                    this.searchQuery.firstName = parts[0];
                    this.searchQuery.lastName = parts.slice(1).join(' ');
                } else {
                    // Single word - search lastName only (user can manually add to other fields if needed)
                    this.searchQuery.lastName = input;
                }

                // Automatically trigger search
                this.performSearch();
            }
        },
        async performSearch() {
            this.searching = true;
            this.searchError = '';
            this.searchResults = [];

            const params = new URLSearchParams();
            if (this.searchQuery.lastName) params.append('lastName', this.searchQuery.lastName);
            if (this.searchQuery.firstName) params.append('firstName', this.searchQuery.firstName);
            if (this.searchQuery.businessName) params.append('businessName', this.searchQuery.businessName);
            if (this.searchQuery.address) params.append('address', this.searchQuery.address);

            // If it's a single-field search from auto-fill, search multiple fields
            const isSingleWordSearch = this.searchQuery.lastName && !this.searchQuery.firstName && !this.searchQuery.businessName && !this.searchQuery.address;

            if (isSingleWordSearch) {
                // Search lastName, firstName, and businessName separately and combine results
                try {
                    const searchTerm = this.searchQuery.lastName;
                    const [lastNameResults, firstNameResults, businessNameResults] = await Promise.all([
                        fetch('{{ route('customers.search') }}?' + new URLSearchParams({ lastName: searchTerm })).then(r => r.json()),
                        fetch('{{ route('customers.search') }}?' + new URLSearchParams({ firstName: searchTerm })).then(r => r.json()),
                        fetch('{{ route('customers.search') }}?' + new URLSearchParams({ businessName: searchTerm })).then(r => r.json())
                    ]);

                    // Combine results and remove duplicates based on customerNumber
                    const allResults = [];
                    const seen = new Set();

                    [lastNameResults, firstNameResults, businessNameResults].forEach(result => {
                        if (result.success && result.customers) {
                            result.customers.forEach(customer => {
                                if (!seen.has(customer.customerNumber)) {
                                    seen.add(customer.customerNumber);
                                    allResults.push(customer);
                                }
                            });
                        }
                    });

                    this.searchResults = allResults;
                } catch (error) {
                    this.searchError = 'Failed to search customers. Please try again.';
                    console.error('Search error:', error);
                } finally {
                    this.searching = false;
                }
            } else {
                // Normal search with provided parameters
                fetch('{{ route('customers.search') }}?' + params.toString())
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.searchResults = data.customers;
                        } else {
                            this.searchError = data.message || 'An error occurred while searching.';
                        }
                    })
                    .catch(error => {
                        this.searchError = 'Failed to search customers. Please try again.';
                        console.error('Search error:', error);
                    })
                    .finally(() => {
                        this.searching = false;
                    });
            }
        }
    }"> <!-- Added px-2 -->		
		<!-- Active Users Bar -->
	
		@if($activeUsers->count() > 0)
			<div class="bg-green-50 p-3 rounded-lg shadow-sm mt-4 mb-6">
				<h3 class="text-sm font-medium text-green-800 inline-flex items-center">
					<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
					</svg>
					Also Online Now:
				</h3>
				<div class="flex flex-wrap mt-2 gap-2">
					@foreach($activeUsers as $user)
						<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
							{{ $user->name }}
						</span>
					@endforeach
				</div>
			</div>
		@endif
		

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Fetch Customer</h1>
            <button @click="searchModalOpen = true" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Search Customers
            </button>
        </div>

		@if ($errors->any())
			<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-4 rounded-md">
				<p class="font-medium">Error:</p>
				<ul class="list-disc list-inside">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif


        <form method="POST" action="{{ route('customers.fetch') }}" class="mt-6" @submit.prevent="handleFormSubmit($event)">
            @csrf
            <div class="mb-4">
                <label for="customer_number" class="block text-sm font-medium text-gray-700">Customer Number or Name</label>
                <input type="text" name="customer_number" id="customer_number" x-model="customerInput" value="{{ old('customer_number') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required placeholder="502xxxxx or customer name">
                <p class="mt-1 text-xs text-gray-500">Enter a customer number (502xxxxx) or a name to search</p>
                @error('customer_number')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Fetch or Update Customer
            </button>
        </form>
		

		<!-- Recent Customers Section -->
		@if($recentCustomers->count() > 0)
			<div class="mt-6">
				<h2 class="text-xl font-semibold text-gray-800 mb-4">Recently Imported Customers</h2>
				
				<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
					@foreach($recentCustomers as $customer)
						<x-customer-card :customer="$customer" />
					@endforeach
				</div>
			</div>
		@endif		
		
        <!-- Recent Contracts Section -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Contracts</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($latestContracts as $contract)
                    <x-contract-card :contract="$contract" />
                @empty
                    <div class="col-span-full">
                        <p class="text-gray-500">No contracts found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Customer Search Modal -->
        <div x-show="searchModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="searchModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="searchModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="searchModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Search Customers</h3>
                            <button @click="searchModalOpen = false" type="button" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-3">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                    <input x-model="searchQuery.lastName" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Smith">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                                    <input x-model="searchQuery.firstName" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="John">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Business Name</label>
                                    <input x-model="searchQuery.businessName" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Acme Corp">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <input x-model="searchQuery.address" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="123 Main St">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button @click="performSearch()" :disabled="searching" type="button" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                    <svg x-show="searching" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="searching ? 'Searching...' : 'Search'"></span>
                                </button>
                            </div>

                            <!-- Search Results -->
                            <div x-show="searchResults.length > 0" class="mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Search Results (<span x-text="searchResults.length"></span>)</h4>
                                <div class="border border-gray-200 rounded-md divide-y divide-gray-200 max-h-96 overflow-y-auto">
                                    <template x-for="customer in searchResults" :key="customer.customerNumber">
                                        <div class="p-3 hover:bg-gray-50">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900" x-text="customer.displayName"></p>
                                                    <p class="text-xs text-gray-500" x-text="customer.address"></p>
                                                </div>
                                                <form method="POST" action="{{ route('customers.fetch') }}" class="ml-4">
                                                    @csrf
                                                    <input type="hidden" name="customer_number" :value="customer.customerNumber">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <span x-text="customer.customerNumber"></span>
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div x-show="searchError" class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                                <p class="text-sm text-red-700" x-text="searchError"></p>
                            </div>

                            <!-- No Results -->
                            <div x-show="!searching && searchResults.length === 0 && searchError === '' && (searchQuery.lastName || searchQuery.firstName || searchQuery.businessName || searchQuery.address)" class="mt-4 text-center py-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No customers found matching your search criteria.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection