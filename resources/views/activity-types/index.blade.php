@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 page-container">
        <h1 class="text-2xl font-semibold text-gray-900">Activity Types</h1>
        <div class="mt-4">
            <a href="{{ route('activity-types.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add New Activity Type
            </a>
        </div>
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($activityTypes as $activityType)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activityType->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activityType->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
								<x-warning-link href="{{ route('activity-types.edit', $activityType->id) }}">
									<x-icon-edit></x-icon-edit>														
								</x-warning-link>
                                <form action="{{ route('activity-types.destroy', $activityType->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
									<x-danger-submit-button><x-icon-delete></x-icon-delete></x-danger-submit-button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex space-x-4">
            <a href="{{ route('customers.index') }}" class="text-indigo-600 hover:text-indigo-900">Back to Customers</a>

        </div>
    </div>
@endsection