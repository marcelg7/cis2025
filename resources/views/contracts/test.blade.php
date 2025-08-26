@extends('layouts.pdf')
@section('content')
    <h1>Test PDF for Contract #{{ $contract->id }}</h1>
    <p>Contract ID: {{ $contract->id }}</p>
    <h2>Your Information</h2>
    <p>Account Name: {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
    <h2>Device Details</h2>
    <p>Model: {{ collect([
        $contract->manufacturer ? ucfirst($contract->manufacturer) : null,
        $contract->model ? ($contract->model === 'iphone' ? 'iPhone' : ucfirst($contract->model)) : null,
        $contract->version,
        $contract->device_storage ? str_replace('gb', 'GB', $contract->device_storage) : null,
        $contract->extra_info ? ucfirst($contract->extra_info) : null,
    ])->filter()->implode(' ') }}</p>
    <h2>Rate Plan Details</h2>
    <p>Plan: {{ $contract->plan->name }}</p>
    <h2>Add-ons</h2>
    @if($contract->addOns->count())
        <ul>
            @foreach($contract->addOns as $addOn)
                <li>{{ $addOn->name }} ({{ $addOn->code }}): ${{ number_format($addOn->cost, 2) }}</li>
            @endforeach
        </ul>
    @endif
    <h2>One-Time Fees</h2>
    @if($contract->oneTimeFees->count())
        <ul>
            @foreach($contract->oneTimeFees as $fee)
                <li>{{ $fee->name }}: ${{ number_format($fee->cost, 2) }}</li>
            @endforeach
        </ul>
    @endif
    <h2>Total Cost</h2>
    <p>Total: ${{ number_format($totalCost, 2) }}</p>
    <h2>Signature</h2>
    @if ($contract->signature_path)
        @php
            $signaturePath = trim($contract->signature_path);
            $checkPath = str_replace('storage/', '', $signaturePath);
            $signatureExists = Storage::disk('public')->exists($checkPath);
        @endphp
        @if ($signatureExists)
            <img src="{{ public_path($signaturePath) }}" alt="Signature" style="max-height: 50px;">
        @else
            <p>Signature file not found at {{ $checkPath }}.</p>
        @endif
    @endif
@endsection