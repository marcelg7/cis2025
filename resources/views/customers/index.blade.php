@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2"> <!-- Added px-2 -->		
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
		

        <h1 class="text-2xl font-semibold text-gray-900">Fetch Customer</h1>
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

		
        <form method="POST" action="{{ route('customers.fetch') }}" class="mt-6">
            @csrf
            <div class="mb-4">
                <label for="customer_number" class="block text-sm font-medium text-gray-700">Customer Number</label>
                <input type="text" name="customer_number" id="customer_number" value="{{ old('customer_number') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('customer_number')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Fetch
            </button>
        </form>
		

		<!-- Recent Customers Section -->
		@if($recentCustomers->count() > 0)
			<div class="mt-6">
				<h2 class="text-xl font-semibold text-gray-800 mb-4">Recently Viewed Customers</h2>
				
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
    </div>
@endsection