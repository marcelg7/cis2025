@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
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
    </div>
@endsection