@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2 page-container">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Customer Information for {{ $customer->display_name }}</h1>

        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-md p-4">
            <p class="text-sm text-blue-800">
                <strong>Customer Number:</strong> {{ $customer->ivue_customer_number }}
            </p>
            <p class="text-sm text-blue-800 mt-1">
                <strong>Primary Email (IVUE):</strong> {{ $customer->email ?? 'N/A' }}
            </p>
        </div>

        <form method="POST" action="{{ route('customers.update', $customer->id) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="contract_email" class="block text-sm font-medium text-gray-700">
                    Contract Signing Email
                    <span class="text-gray-500 font-normal">(Optional)</span>
                </label>
                <p class="mt-1 text-sm text-gray-500">
                    This email will be used for sending contract signing links and contract PDFs. If not specified, the primary IVUE email will be used.
                </p>
                <input
                    type="email"
                    name="contract_email"
                    id="contract_email"
                    value="{{ old('contract_email', $customer->contract_email) }}"
                    class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="example@domain.com"
                >
                @error('contract_email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <a href="{{ route('customers.show', $customer->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Contract Email
                </button>
            </div>
        </form>
    </div>
@endsection
