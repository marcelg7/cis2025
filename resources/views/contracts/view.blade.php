@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2">
        <h1 class="text-2xl font-bold mb-4">Contract #{{ $contract->id }}</h1>
        @if (session('success'))
            <div class="bg-green-50 p-3 rounded-lg shadow-sm mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 p-3 rounded-lg shadow-sm mb-6">
                {{ session('error') }}
            </div>
        @endif
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Contract Details</h2>
            <p><strong>Customer:</strong> {{ $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name }}</p>
            <p><strong>Start Date:</strong> {{ $contract->start_date }}</p>
            <p><strong>End Date:</strong> {{ $contract->end_date ?? 'N/A' }}</p>
            <p><strong>Location:</strong> {{ ucfirst($contract->location) }}</p>
            <p><strong>Plan:</strong> {{ $contract->plan->name ?? 'N/A' }} (${{ number_format($contract->plan->price ?? 0, 2) }})</p>
            <p><strong>Commitment Period:</strong> {{ $contract->commitmentPeriod->name ?? 'N/A' }}</p>
            <p><strong>First Bill Date:</strong> {{ $contract->first_bill_date }}</p>
            <p><strong>Activity Type:</strong> {{ $contract->activityType->name ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($contract->status) }}</p>

            <h2 class="text-lg font-medium text-gray-900 mt-6 mb-4">Device Details</h2>
            <p><strong>Device:</strong> {{ implode(', ', array_filter([
                $contract->manufacturer ? "Manufacturer: " . $contract->manufacturer : null,
                $contract->model ? "Model: " . $contract->model : null,
                $contract->version ? "Version: " . $contract->version : null,
                $contract->device_storage ? "Storage: " . $contract->device_storage : null,
                $contract->extra_info ? "Extra: " . $contract->extra_info : null,
            ])) }}</p>
            <p><strong>Device Retail Price:</strong> ${{ number_format($contract->device_price ?? 0, 2) }}</p>
            <p><strong>SIM #:</strong> {{ $contract->sim_number ?? 'N/A' }}</p>
            <p><strong>IMEI #:</strong> {{ $contract->imei_number ?? 'N/A' }}</p>
            <p><strong>Amount Paid for Device:</strong> ${{ number_format($contract->amount_paid_for_device ?? 0, 2) }}</p>
            <p><strong>Agreement Credit Amount:</strong> ${{ number_format($contract->agreement_credit_amount ?? 0, 2) }}</p>

            <h2 class="text-lg font-medium text-gray-900 mt-6 mb-4">Hay Financing</h2>
            <p><strong>Required Up-front Payment:</strong> ${{ number_format($contract->required_upfront_payment ?? 0, 2) }}</p>
            <p><strong>Optional Down Payment:</strong> ${{ number_format($contract->optional_down_payment ?? 0, 2) }}</p>
            <p><strong>Deferred Payment Amount:</strong> ${{ number_format($contract->deferred_payment_amount ?? 0, 2) }}</p>
            <p><strong>DRO Amount:</strong> ${{ number_format($contract->dro_amount ?? 0, 2) }}</p>
            <p><strong>Total Financing Cost:</strong> ${{ number_format($totalFinancingCost, 2) }}</p>

            @if($contract->addOns->count())
                <h2 class="text-lg font-medium text-gray-900 mt-6 mb-4">Add-ons</h2>
                <ul class="list-disc pl-6">
                    @foreach($contract->addOns as $addOn)
                        <li>{{ $addOn->name }} ({{ $addOn->code }}): ${{ number_format($addOn->cost, 2) }}</li>
                    @endforeach
                </ul>
                <p><strong>Total Add-on Cost:</strong> ${{ number_format($totalAddOnCost, 2) }}</p>
            @endif

            @if($contract->oneTimeFees->count())
                <h2 class="text-lg font-medium text-gray-900 mt-6 mb-4">One-Time Fees</h2>
                <ul class="list-disc pl-6">
                    @foreach($contract->oneTimeFees as $fee)
                        <li>{{ $fee->name }}: ${{ number_format($fee->cost, 2) }}</li>
                    @endforeach
                </ul>
                <p><strong>Total One-Time Fee Cost:</strong> ${{ number_format($totalOneTimeFeeCost, 2) }}</p>
            @endif

            <h2 class="text-lg font-medium text-gray-900 mt-6 mb-4">Total Cost</h2>
            <p><strong>Total Contract Cost:</strong> ${{ number_format($totalCost, 2) }}</p>

            @if($contract->signature_path)
                <h2 class="text-lg font-medium text-gray-900 mt-6 mb-4">Signature</h2>
                <img src="{{ Storage::url($contract->signature_path) }}" alt="Signature" class="max-w-xs">
            @endif

            <div class="mt-6 flex items-center space-x-4">
                <a href="{{ route('contracts.download', $contract->id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 {{ $contract->status != 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}" 
                   style="background-color: #16a34a !important; color: #ffffff !important;" 
                   {{ $contract->status != 'finalized' ? 'disabled' : '' }}>
                    Download PDF
                </a>
                <a href="{{ route('contracts.email', $contract->id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $contract->status != 'finalized' ? 'opacity-50 cursor-not-allowed' : '' }}" 
                   style="background-color: #2563eb !important; color: #ffffff !important;" 
                   {{ $contract->status != 'finalized' ? 'disabled' : '' }}>
                    Email Contract
                </a>
                <a href="{{ route('contracts.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                   style="background-color: #ffffff !important; color: #374151 !important;">
                    Back to Contracts
                </a>
                @if($contract->status == 'draft')
                    <a href="{{ route('contracts.edit', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500" 
                       style="background-color: #ca8a04 !important; color: #ffffff !important;">
                        Edit
                    </a>
                    <a href="{{ route('contracts.sign', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
                       style="background-color: #2563eb !important; color: #ffffff !important;">
                        Sign
                    </a>
                @endif
                @if($contract->status == 'signed')
                    <form action="{{ route('contracts.finalize', $contract->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" 
                                style="background-color: #16a34a !important; color: #ffffff !important;">
                            Finalize
                        </button>
                    </form>
                @endif
                @if($contract->status == 'finalized')
                    <form action="{{ route('contracts.revision', $contract->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
                                style="background-color: #2563eb !important; color: #ffffff !important;">
                            Create Revision
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection