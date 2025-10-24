@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">CSR Cheat Sheet</h1>
            <p class="mt-2 text-sm text-gray-600">Quick reference guide for creating and managing contracts</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-6 py-6 cheat-sheet-content">
                {!! \App\Helpers\MarkdownHelper::sanitize($content) !!}
            </div>
        </div>
    </div>

    <style>
        /* Custom styles for cheat sheet markdown content */
        .cheat-sheet-content {
            color: #374151;
            font-size: 0.9375rem;
            line-height: 1.6;
        }

        .cheat-sheet-content p {
            margin-bottom: 1rem;
        }

        .cheat-sheet-content ul,
        .cheat-sheet-content ol {
            margin-bottom: 1rem;
            margin-left: 1.5rem;
            padding-left: 0.5rem;
        }

        .cheat-sheet-content li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .cheat-sheet-content ul li {
            list-style-type: disc;
        }

        .cheat-sheet-content ol li {
            list-style-type: decimal;
        }

        .cheat-sheet-content code {
            padding: 0.125rem 0.375rem;
            background-color: #1f2937;
            color: #f3f4f6;
            border-radius: 0.25rem;
            font-size: 0.8125rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .cheat-sheet-content pre {
            padding: 1rem;
            background-color: #1f2937;
            color: #f3f4f6;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-bottom: 1rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .cheat-sheet-content pre code {
            padding: 0;
            background-color: transparent;
            font-size: 0.8125rem;
        }

        .cheat-sheet-content strong {
            font-weight: 600;
            color: #111827;
        }

        .cheat-sheet-content em {
            font-style: italic;
        }

        .cheat-sheet-content a {
            color: #4f46e5;
            text-decoration: underline;
        }

        .cheat-sheet-content a:hover {
            color: #3730a3;
        }

        .cheat-sheet-content h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1rem;
            margin-top: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .cheat-sheet-content h1:first-child {
            margin-top: 0;
        }

        .cheat-sheet-content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.75rem;
            margin-top: 1.75rem;
            padding-bottom: 0.375rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .cheat-sheet-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.75rem;
            margin-top: 1.5rem;
        }

        .cheat-sheet-content h4 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            margin-top: 1.25rem;
        }

        .cheat-sheet-content blockquote {
            border-left: 4px solid #d1d5db;
            padding-left: 1rem;
            font-style: italic;
            color: #6b7280;
            margin: 1rem 0;
        }

        .cheat-sheet-content hr {
            border-color: #d1d5db;
            margin: 1.5rem 0;
        }

        .cheat-sheet-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .cheat-sheet-content table th {
            background-color: #f3f4f6;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            border: 1px solid #d1d5db;
        }

        .cheat-sheet-content table td {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
        }

        .cheat-sheet-content table tr:nth-child(even) {
            background-color: #f9fafb;
        }
    </style>
@endsection
