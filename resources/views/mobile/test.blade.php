@extends('layouts.app')

@section('content')
    <h1>WordPress Database Test</h1>

    <p>Debug: {{ $shortcodes->count() }} shortcodes loaded.</p>
    <h2>Shortcodes</h2>
    @if ($shortcodes->isEmpty())
        <p>No shortcodes found.</p>
    @else
        <ul>
            @foreach ($shortcodes as $shortcode)
                <li>
                    <h3>{{ $shortcode->slug }}</h3>
                    <p>{{ $shortcode->data }}</p>
                    <p>Disabled: {{ $shortcode->disabled ? 'Yes' : 'No' }}</p>
                    <p>Previous Slug: {{ $shortcode->previous_slug ?: 'N/A' }}</p>
                    <p>Multisite: {{ $shortcode->multisite ? 'Yes' : 'No' }}</p>
                </li>
            @endforeach
        </ul>
    @endif

@endsection