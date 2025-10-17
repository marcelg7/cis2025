@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold mb-6">Changelog</h1>

        @if (empty($commits))
            <p class="text-gray-600">No changes logged yet.</p>
        @else
            <div class="space-y-6">
                @foreach ($commits as $commit)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $commit['hash'] }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $commit['author'] }}
                                </span>
                            </div>
								<span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($commit['date'])->diffForHumans() }}</span>		
                        </div>
                        <p class="text-gray-700">{{ $commit['message'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection