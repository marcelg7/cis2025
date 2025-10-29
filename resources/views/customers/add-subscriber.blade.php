@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container"> <!-- Added px-2 -->
        <h1 class="text-2xl font-semibold text-gray-900">Add Subscriber for {{ $customer->display_name }}</h1>
        <form method="POST" action="{{ route('customers.store-subscriber', $customer->id) }}" class="mt-6 space-y-6">
            @csrf
            <div>
                <label for="mobility_account_id" class="block text-sm font-medium text-gray-700">Select Mobility Account</label>
                <select name="mobility_account_id" id="mobility_account_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @if ($customer->ivueAccounts->pluck('mobilityAccount')->filter()->isEmpty())
                        <option value="">No mobility accounts available</option>
                    @else
                        @foreach ($customer->ivueAccounts as $ivue)
                            @if ($ivue->mobilityAccount)
                                <option value="{{ $ivue->mobilityAccount->id }}" {{ old('mobility_account_id') == $ivue->mobilityAccount->id ? 'selected' : '' }}>{{ $ivue->mobilityAccount->mobility_account }}</option>
                            @endif
                        @endforeach
                    @endif
                </select>
                @error('mobility_account_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                <input type="text" name="mobile_number" id="mobile_number" value="{{ old('mobile_number') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('mobile_number')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name (optional)</label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('first_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name (optional)</label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('last_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end">
                <a href="{{ route('customers.show', $customer->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Subscriber
                </button>
            </div>
        </form>
    </div>
@endsection