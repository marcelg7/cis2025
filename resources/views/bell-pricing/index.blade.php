@extends('layouts.app')

@section('content')
<div class="py-12">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
	<!-- Page Header -->
	<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
		<div class="p-6">
			<div class="flex justify-between items-center">
				<div>
					<h1 class="text-2xl font-bold text-gray-900">Bell Device Pricing</h1>
				</div>
				<div class="flex space-x-2">
					<a href="{{ route('bell-pricing.upload') }}"
					   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
						<svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
						</svg>
						Upload New Pricing
					</a>
					<a href="{{ route('bell-pricing.compare') }}"
					   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
						Compare Devices
					</a>
				</div>
			</div>
		</div>
	</div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('bell-pricing.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search Device</label>
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="{{ request('search') }}"
                       placeholder="Device name, model..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            
            <div>
                <label for="manufacturer" class="block text-sm font-medium text-gray-700">Manufacturer</label>
                <select name="manufacturer" 
                        id="manufacturer"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Manufacturers</option>
                    @foreach($manufacturers as $manufacturer)
                        <option value="{{ $manufacturer }}" {{ request('manufacturer') === $manufacturer ? 'selected' : '' }}>
                            {{ $manufacturer }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Devices Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Device
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Retail Price
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        SmartPay Tiers
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        DRO Available
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($devices as $device)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $device->name }}</div>
                            <div class="text-xs text-gray-500">{{ $device->manufacturer }} • {{ $device->storage }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $samplePricing = $device->currentPricing->first() ?? $device->currentDroPricing->first();
                            @endphp
                            <div class="text-sm text-gray-900">
                                ${{ number_format($samplePricing->retail_price ?? 0, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($device->currentPricing->pluck('tier')->unique() as $tier)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $tier }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($device->currentDroPricing->count() > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Yes
                                </span>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('bell-pricing.show', $device->id) }}" 
                               class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            <a href="{{ route('bell-pricing.history', $device->id) }}" 
                               class="text-gray-600 hover:text-gray-900">History</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            No devices found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $devices->links() }}
    </div>
</div>
</div>
@endsection