@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Changelog</h1>
            <p class="mt-2 text-sm text-gray-600">Recent changes and updates to the application</p>
        </div>

        @if (empty($commits))
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-gray-600">No changes logged yet.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($commits as $commit)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $commit['title'] }}</h3>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $commit['hash'] }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $commit['author'] }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium text-gray-600">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ \Carbon\Carbon::parse($commit['date'])->setTimezone(config('app.timezone'))->format('M j, Y \a\t g:i A') }}
                                            <span class="ml-1 text-gray-500">({{ \Carbon\Carbon::parse($commit['date'])->diffForHumans() }})</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Body (if exists) -->
                        @if (!empty($commit['body']))
                            <div class="px-6 py-4 bg-gray-50">
                                <div class="changelog-content">
                                    {!! \App\Helpers\MarkdownHelper::sanitize(Str::markdown($commit['body'])) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <style>
        /* Custom styles for changelog markdown content */
        .changelog-content {
            color: #374151;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .changelog-content p {
            margin-bottom: 0.75rem;
        }

        .changelog-content ul,
        .changelog-content ol {
            margin-bottom: 0.75rem;
            margin-left: 1.5rem;
            padding-left: 0.5rem;
        }

        .changelog-content li {
            margin-bottom: 0.375rem;
            line-height: 1.6;
        }

        .changelog-content ul li {
            list-style-type: disc;
        }

        .changelog-content ol li {
            list-style-type: decimal;
        }

        .changelog-content code {
            padding: 0.125rem 0.375rem;
            background-color: #1f2937;
            color: #f3f4f6;
            border-radius: 0.25rem;
            font-size: 0.8125rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .changelog-content pre {
            padding: 1rem;
            background-color: #1f2937;
            color: #f3f4f6;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-bottom: 0.75rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .changelog-content pre code {
            padding: 0;
            background-color: transparent;
            font-size: 0.8125rem;
        }

        .changelog-content strong {
            font-weight: 600;
            color: #111827;
        }

        .changelog-content em {
            font-style: italic;
        }

        .changelog-content a {
            color: #4f46e5;
            text-decoration: underline;
        }

        .changelog-content a:hover {
            color: #3730a3;
        }

        .changelog-content h1,
        .changelog-content h2,
        .changelog-content h3 {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
            margin-top: 1rem;
        }

        .changelog-content h1 {
            font-size: 1.25rem;
        }

        .changelog-content h2 {
            font-size: 1.125rem;
        }

        .changelog-content h3 {
            font-size: 1rem;
        }

        .changelog-content blockquote {
            border-left: 4px solid #d1d5db;
            padding-left: 1rem;
            font-style: italic;
            color: #6b7280;
            margin: 0.75rem 0;
        }

        .changelog-content hr {
            border-color: #d1d5db;
            margin: 1rem 0;
        }
    </style>
@endsection
