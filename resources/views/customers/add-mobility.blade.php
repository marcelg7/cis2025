@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2"> <!-- Added px-2 -->
        <h1 class="text-2xl font-semibold text-gray-900">Add Mobility Account for {{ $customer->display_name }}</h1>
        <form method="POST" action="{{ route('customers.store-mobility', $customer->id) }}" class="mt-6 space-y-6">
            @csrf
            <div>
                <label for="ivue_account_id" class="block text-sm font-medium text-gray-700">Select IVUE Account</label>
				<select name="ivue_account_id" id="ivue_account_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
					@foreach ($customer->ivueAccounts as $ivue)
						@if (!$ivue->mobilityAccount)
							<option value="{{ $ivue->id }}">{{ $ivue->ivue_account }}</option>
						@endif
					@endforeach
				</select>
                @error('ivue_account_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="mobility_account" class="block text-sm font-medium text-gray-700">Mobility Account Number</label>
                <input type="text" name="mobility_account" id="mobility_account" value="{{ old('mobility_account') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                @error('mobility_account')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end">
                <a href="{{ route('customers.show', $customer->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Mobility Account
                </button>
            </div>
        </form>
    </div>
@endsection