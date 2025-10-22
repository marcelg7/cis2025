@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <a href="{{ route('contracts.view', $contract->id) }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Contract
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Sign Contract</h2>
            <p class="mt-1 text-sm text-gray-600">Contract #{{ $contract->id }} - {{ $contract->subscriber->first_name }} {{ $contract->subscriber->last_name }}</p>
        </div>

        <div class="px-6 py-6">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative">
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="text-sm text-gray-700 mb-6">
                Please review the contract and provide your signature below to confirm your agreement to the terms and conditions.
            </p>

            <form method="POST" action="{{ route('contracts.storeSignature', $contract->id) }}" id="signature-form">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer Signature</label>
                    <div class="border border-gray-300 rounded-md bg-gray-50">
                        <canvas id="signature-pad" class="w-full" style="height: 300px; min-height: 300px; touch-action: none;"></canvas>
                    </div>
                    <div class="mt-2 flex justify-between items-center">
                        <button type="button" id="clear-signature" class="text-sm text-gray-600 hover:text-gray-900">
                            Clear Signature
                        </button>
                        <p class="text-xs text-gray-500">Sign above using your mouse or touch screen</p>
                    </div>
                </div>

                <input type="hidden" name="signature" id="signature-data">

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('contracts.view', $contract->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" id="submit-signature"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }
        
        window.addEventListener('resize', resizeCanvas);
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