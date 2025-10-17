@extends('layouts.app')
<!-- contracts/sign.blade.php -->
@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 px-2">
        <h1 class="text-2xl font-bold mb-4">Sign Contract #{{ $contract->id }}</h1>
        @if ($errors->any())
            <div class="bg-red-50 p-3 rounded-lg shadow-sm mb-6">
                <ul class="text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('contracts.storeSignature', $contract->id) }}" id="signature-form">
            @csrf
            <div class="mb-4">
                <label for="signature-pad" class="block text-sm font-medium text-gray-700">Signature</label>
                <canvas id="signature-pad" class="border border-gray-300 rounded-md" width="400" height="200"></canvas>
                <input type="hidden" name="signature" id="signature" value="">
            </div>
            <div class="flex items-center space-x-4">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
                        style="background-color: #2563eb !important; color: #ffffff !important;">
                    Sign Contract
                </button>
                <button type="button" id="clear-signature" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" 
                        style="background-color: #dc2626 !important; color: #ffffff !important;">
                    Clear
                </button>
                <a href="{{ route('contracts.view', $contract->id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                   style="background-color: #ffffff !important; color: #374151 !important;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const canvas = document.getElementById('signature-pad');
                if (!window.SignaturePad) {
                    throw new Error('SignaturePad is not defined');
                }
                const signaturePad = new window.SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
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
                    document.getElementById('signature').value = '';
                    console.log('Signature cleared');
                });

                document.getElementById('signature-form').addEventListener('submit', function(e) {
                    if (signaturePad.isEmpty()) {
                        e.preventDefault();
                        alert('Please provide a signature.');
                    } else {
                        const signatureData = signaturePad.toDataURL('image/png');
                        document.getElementById('signature').value = signatureData;
                        console.log('Signature data to be sent:', signatureData.substring(0, 50) + '...'); // Log first 50 chars
                    }
                });
            } catch (error) {
                console.error('Error initializing SignaturePad:', error);
            }
        });
    </script>
@endsection