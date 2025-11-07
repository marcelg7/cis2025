@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Mobility Account for {{ $customer->display_name }}</h1>

        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-md p-4">
            <p class="text-sm text-blue-800">
                <strong>IVUE Account:</strong> {{ $mobilityAccount->ivueAccount->ivue_account }}
            </p>
        </div>

        <form method="POST" action="{{ route('customers.update-mobility', [$customer->id, $mobilityAccount->id]) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="mobility_account" class="block text-sm font-medium text-gray-700">Mobility Account Number</label>
                <input
                    type="text"
                    name="mobility_account"
                    id="mobility_account"
                    value="{{ old('mobility_account', $mobilityAccount->mobility_account) }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                >
                @error('mobility_account')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <a href="{{ route('customers.show', $customer->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Mobility Account
                </button>
            </div>
        </form>
    </div>
@endsection
