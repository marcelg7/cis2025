@extends('layouts.app')

@section('content')
<style>
    /* Mobile/Tablet Optimizations */
    @media (max-width: 1024px) {
        .signature-page-container {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .signature-canvas canvas {
            height: 400px !important;
            min-height: 400px !important;
        }

        .signature-button {
            padding: 1rem 1.5rem !important;
            font-size: 1rem !important;
            min-height: 48px;
        }

        .clear-signature-btn {
            padding: 0.75rem 1rem;
            font-size: 0.95rem !important;
            min-height: 44px;
        }

        .signature-card {
            margin-bottom: 2rem;
        }
    }

    /* Tablet-specific (768px - 1024px) */
    @media (min-width: 768px) and (max-width: 1024px) {
        .signature-canvas canvas {
            height: 450px !important;
            min-height: 450px !important;
        }

        .px-6 {
            padding-left: 2rem !important;
            padding-right: 2rem !important;
        }
    }

    /* Touch feedback */
    @media (hover: none) and (pointer: coarse) {
        .signature-button:active,
        .clear-signature-btn:active {
            transform: scale(0.98);
            transition: transform 0.1s;
        }
    }
</style>

<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6 signature-page-container">
    <div class="mb-6 stagger-item">
        <a href="{{ route('contracts.view', $contract->id) }}" class="inline-flex items-center text-base md:text-sm text-indigo-600 hover:text-indigo-500 transition-all duration-200 hover:translate-x-1 py-2">
            <svg class="w-5 h-5 md:w-4 md:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Contract
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden signature-card">
        <div class="px-4 md:px-6 py-5 md:py-4 border-b border-gray-200">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900">Sign Contract</h2>
            <p class="mt-1 text-sm md:text-sm text-gray-600">Contract #{{ $contract->id }} - {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
        </div>

        <div class="px-4 md:px-6 py-6">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative">
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="text-sm text-gray-700 mb-6 stagger-item">
                Please review the contract and provide your signature below to confirm your agreement to the terms and conditions.
            </p>

            <form method="POST" action="{{ route('contracts.storeSignature', $contract->id) }}" id="signature-form">
                @csrf

                <div class="mb-6 signature-pad-wrapper">
                    <label class="block text-base md:text-sm font-medium text-gray-700 mb-3">Customer Signature</label>
                    <div class="border-2 md:border border-gray-300 rounded-md bg-gray-50 signature-canvas">
                        <canvas id="signature-pad" class="w-full" style="height: 300px; min-height: 300px; touch-action: none; cursor: crosshair;"></canvas>
                    </div>
                    <div class="mt-3 md:mt-2 flex flex-col md:flex-row justify-between items-start md:items-center space-y-2 md:space-y-0">
                        <button type="button" id="clear-signature" class="inline-flex items-center text-base md:text-sm text-gray-600 hover:text-gray-900 clear-signature-btn bg-gray-100 hover:bg-gray-200 rounded-md px-4 py-2 md:px-3 md:py-1 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear Signature
                        </button>
                        <p class="text-sm md:text-xs text-gray-500">Sign above using your finger or stylus</p>
                    </div>
                </div>

                <input type="hidden" name="signature" id="signature-data">

                <div class="flex flex-col md:flex-row justify-end space-y-3 md:space-y-0 md:space-x-4 stagger-item">
                    <a href="{{ route('contracts.view', $contract->id) }}"
                       class="inline-flex items-center justify-center px-6 md:px-4 py-3 md:py-2 border border-gray-300 text-base md:text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 signature-button">
                        Cancel
                    </a>
                    <button type="submit" id="submit-signature"
                            class="inline-flex items-center justify-center px-6 md:px-4 py-3 md:py-2 border border-transparent text-base md:text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 signature-button">
                        <svg class="w-5 h-5 md:w-4 md:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Sign Contract
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        
        if (!window.SignaturePad) {
            console.error('SignaturePad library not loaded');
            return;
        }
        
        const signaturePad = new window.SignaturePad(canvas, {
            backgroundColor: 'rgb(249, 250, 251)',
            penColor: 'rgb(0, 0, 0)'
        });

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            // Save signature data before resizing
            const data = signaturePad.toData();

            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();

            // Restore signature data after resizing
            if (data && data.length > 0) {
                signaturePad.fromData(data);
            }
        }

        // Debounce resize events to prevent excessive redraws
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(resizeCanvas, 100);
        });
        resizeCanvas();

        document.getElementById('clear-signature').addEventListener('click', function() {
            signaturePad.clear();
        });

        document.getElementById('signature-form').addEventListener('submit', function(e) {
            e.preventDefault();

            if (signaturePad.isEmpty()) {
                alert('Please provide a signature before submitting.');
                return;
            }

            const dataURL = signaturePad.toDataURL('image/png');
            document.getElementById('signature-data').value = dataURL;
            
            this.submit();
        });
    });
</script>
@endsection